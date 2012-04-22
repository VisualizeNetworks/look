<?php

class CiscoDevice extends BaseDevice {
    function setup_cmds() {
        foreach($GLOBALS["all_cmds"] as $key => $cmd) {
            switch($cmd->display) {
                case "ping":
                    $this->device_cmds[$key] = $cmd->display;
                    $cmd->set_command("ping %arg%");
                    $cmd->allow_ip_argument();
                    $cmd->allow_device_argument();
                    break;

                case "traceroute":
                    $this->device_cmds[$key] = $cmd->display;
                    $cmd->set_command("traceroute %arg%");
                    $cmd->allow_ip_argument();
                    $cmd->allow_device_argument();
                    break;

                case "show route":
                    $this->device_cmds[$key] = $cmd->display;
                    $cmd->set_command("show ip route %arg%");
                    $cmd->allow_ip_argument();
                    $cmd->allow_device_argument();
                    break;

                case "show bgp route":
                    $this->device_cmds[$key] = $cmd->display;
                    $cmd->set_command("show ip bgp route %arg%");
                    $cmd->allow_ip_argument();
                    $cmd->allow_subnet_argument("full");
                    $cmd->allow_device_argument();
                    break;

                case "show eigrp topology":
                    $this->device_cmds[$key] = $cmd->display;
                    $cmd->set_command("show ip eigrp topology %arg%");
                    $cmd->allow_subnet_argument();
                    break;
            }

        }
    }
}

?>
