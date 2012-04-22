<?php

class DeviceCommand {
    private static $last_id = -1;
    public $id;
    public $display;
    public $cmd;
    public $subnet_with_mask;
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

?>
