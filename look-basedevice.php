<?php

class BaseDevice {
    public $hostname;
    public $decription;
    protected $connection;
    protected $username;
    protected $password;

    protected $device_cmds;
    protected $connection_obj;

    function __construct($hostname, $description, $connection, $username, $password) {
        $this->hostname = $hostname;
        $this->description = $description;
        $this->username = $username;
        $this->password = $password;
        $this->device_cmds = array();
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
        if(array_key_exists($cmd_id, $this->device_cmds)) {
            $cmd_obj = $GLOBALS["all_cmds"][$cmd_id];
            // validate argument
            if($cmd_obj->valid_arg($arg_type, $arg)) {
                if($arg_type == "device") {
                    $arg = $GLOBALS["device_cfg"][$arg]["hostname"];
                }

                $func = $this->query_function;
                $ret = $this->$func($cmd_obj->get_command($arg));
            } else {
                // invalid argument
                $ret = "ERROR: invalid argument '$arg'";
            }
        } else {
            // command doesn't apply for this router
            $ret = "ERROR: command does not apply for this router";
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

?>
