<?php

# get config file
include("look-cfg.php");

include("look-cisco.php");
include("look-juniper.php");

class DeviceCommand {
    private static $last_id = -1;
    public $id;
    public $display;
    public $cmd;
    public $subnet_mask;
    public $args = array();

    function __construct($display) {
        self::$last_id++;
        $this->id = self::$last_id;

        $this->display = $display;
    }

    function set_command($cmd) {
        $this->cmd = $cmd;
    }

    function get_command($arg) {
        if($this->subnet_with_mask != NULL) {
            $arg = $this->subnet_with_mask;
        }
        return str_replace("%arg%", $arg, $this->cmd);
    }

    function allow_ip_argument() {
        $this->args[] = "ip";
    }

    function allow_subnet_argument($type = "bits") {
        $this->args[] = "subnet";

        // should be "bits" or "full".  Bits means /24 but full will be converted to 255.255.255.0
        $this->subnet_mask_type = $type;
    }

    function allow_device_argument() {
        $this->args[] = "device";
    }

    function make_subnet_mask($bits) {
        $network = 0xFFFFFFFF << (32 - $bits);
        $mask = array(
            ($network & 0xFF000000) >> 24,
            ($network & 0x00FF0000) >> 16,
            ($network & 0x0000FF00) >> 8,
            ($network & 0x000000FF),
        );

        return implode(".", $mask);
    }

    function valid_arg($arg_type, $arg) {
        if(in_array($arg_type, $this->args)) {
            switch($arg_type) {
                case "ip":
                    $ret = $this->valid_ip($arg);
                    break;

                case "subnet":
                    $ret = $this->valid_subnet($arg);
                    break;

                case "device":
                    $ret = true;
                    break;
            }
        } else {
            $ret = false;
        }
        return $ret;
    }

    function valid_ip($arg) {
        $octets = explode(".", $arg);

        // an IP argument should look like x.x.x.x
        if(count($octets) != 4) {
            return false;
        }

        // all octets should be integers between 0 and 255 (inclusive)
        foreach($octets as $octet) {
            if($octet < 0 or $octet > 255) {
                return false;
            }
        }

        return true;
    }

    function valid_subnet($arg) {
        $pieces = explode("/", $arg);

        // a subnet argument should look like x.x.x.x/x
        if(count($pieces) != 2) {
            return false;
        }

        $network = $pieces[0];
        $mask = $pieces[1];

        // the network address should be a valid IP
        if(!$this->valid_ip($network)) {
            return false;
        }

        // a subnet should be between /1 and /32 (inclusive)
        if($mask < 1 or $mask > 32) {
            return false;
        }

        if($this->subnet_mask_type == "full") {
            $this->subnet_with_mask = $network . " " . $this->make_subnet_mask($mask);
        }

        return true;
    }
}

$all_cmds = array();
$all_cmds[] = new DeviceCommand("ping");
$all_cmds[] = new DeviceCommand("traceroute");
$all_cmds[] = new DeviceCommand("show route");
$all_cmds[] = new DeviceCommand("show bgp route");
$all_cmds[] = new DeviceCommand("show eigrp topology");

class BaseDevice {
    public $hostname;
    public $decription;
    protected $connection;
    protected $username;
    protected $password;

    protected $connection_obj;

    function __construct($hostname, $description, $connection, $username, $password) {
        $this->hostname = $hostname;
        $this->description = $description;
        $this->username = $username;
        $this->password = $password;
        $this->connection = strtolower($connection);

        $this->query_function = "query_router_";
        switch($this->connection) {
            case "telnet":
                $this->query_function .= "telnet";
                break;
            case "sshv2":
                $this->query_function .= "ssh";
                break;
            case "snmpv3":
                $this->query_function .= "snmpv3";
                break;
        }

        $this->setup_cmds();
    }

    function setup_cmds() {
        // implement in sub classes
    }

    function query_router($cmd_id, $arg_type, $arg) {
        $ret = "";
        if(array_key_exists($cmd_id, $GLOBALS["all_cmds"])) {
            $cmd_obj = $GLOBALS["all_cmds"][$cmd_id];
            // validate argument
            if($cmd_obj->valid_arg($arg_type, $arg)) {
                if($arg_type == "device") {
                    $arg = $GLOBALS["devices"][$arg]->hostname;
                }

                $func = $this->query_function;
                $ret = $this->$func($cmd_obj->get_command($arg));
            } else {
                // invalid argument
                $ret = "invalid argument: $arg";
            }
        } else {
            // command doesn't apply for this router
            $ret = "command does not apply for this router";
        }
        return $ret;
    }

    function query_router_ssh($cmd) {
        $data = "";
        if($ssh = ssh2_connect($this->hostname, 22)) {
            if(ssh2_auth_password($ssh, $this->username, $this->password)) {
                $stream = ssh2_exec($ssh, $cmd);
                stream_set_blocking($stream, true);
                while($buffer = fread($stream, 4096)) {
                    $data .= $buffer;
                }
                fclose($stream);
            } else {
                // password is invalid
                $data = "ERROR: cannot authenticate to the router";
            }
        } else {
            // cannot connect
            $data = "ERROR: cannot connect to the router";
        }
        return $data;
    }

    function query_router_telnet($cmd) {
        $data = "";
        if($fp = fsockopen($this->hostname, 23)) {
            fputs($fp, $this->username . "\r\n");
            fputs($fp, $this->password . "\r\n");
            fputs($fp, "terminal length 0\r\n");
            fputs($fp, "\r\n");
            fputs($fp, $cmd . "\r\n");
            fputs($fp, "exit\r\n");

            $tmp_data = "";
            while (!feof($fp)) {
                $tmp_data .= fgets($fp, 1024);
            }
            fclose($fp);

            // get rid of excess login stuff before the command
            $pieces1 = explode($cmd . "\r\n", $tmp_data);
            $pieces2 = explode("\r\n", $pieces1[1]);
            $data = implode("\n", array_slice($pieces2, 0, -2));
        } else {
            // cannot connect
            $data = "ERROR: cannot connect to the router";
        }

        return $data;
    }

    function query_router_snmpv3($cmd) {
        // not yet implemented
    }
}

function sort_by_description($a, $b) {
    if ($a["description"] == $b["description"]) {
        return 0;
    }
    return ($a["description"] < $b["description"]) ? -1 : 1;
}

uasort($device_cfg, "sort_by_description");

$devices = array();
foreach($device_cfg as $device_index => $device) {
    $hostname = $device["hostname"];

    if(array_key_exists("vendor", $device)) {
        $vendor = $device["vendor"];
    } else {
        $vendor = $DEFAULT_VENDOR;
    }

    if(array_key_exists("username", $device)) {
        $username = $device["username"];
    } else {
        $username = $DEFAULT_USERNAME;
    }

    if(array_key_exists("password", $device)) {
        $password = $device["password"];
    } else {
        $password = $DEFAULT_PASSWORD;
    }

    if(array_key_exists("connection", $device)) {
        $connection = $device["connection"];
    } else {
        $connection = $DEFAULT_CONNECTION;
    }

    switch(strtolower($vendor)) {
        case "cisco":
            $devices[$device_index] = new CiscoDevice($hostname, $device["description"], $connection, $username, $password);
            break;

        case "juniper":
            $devices[$device_index] = new JuniperDevice($hostname, $device["description"], $connection, $username, $password);
            break;
    }
}

// the page was called from the HTML form
if($_SERVER['REQUEST_METHOD'] == 'POST' and $_POST["form"] == "ajax") {
    // check bounds!!
    $src_index = $_POST["source"];
    if($src_index < 0 or $src_index > count($devices) - 1) {
        echo "ERROR: invalid source";
    }

    $cmd_index = $_POST["cmd"];

    $target_type = $_POST["target_type"];
    $target = $_POST["target"];

    echo $devices[$src_index]->query_router($cmd_index, $target_type, $target);
}

?>
