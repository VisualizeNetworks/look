<?php

class CiscoDevice extends BaseDevice {
    function setup_cmds() {
        foreach($GLOBALS["all_cmds"] as $cmd) {
            switch($cmd->display) {
                case "ping":
                    $cmd->set_command("ping");
                    $cmd->set_ip_argument();
                    $cmd->set_device_argument();
                    break;

                case "traceroute":
                    $cmd->set_command("traceroute");
                    $cmd->set_ip_argument();
                    $cmd->set_device_argument();
                    break;

                case "show route":
                    $cmd->set_command("show ip route");
                    $cmd->set_ip_argument();
                    $cmd->set_device_argument();
                    break;

                case "show bgp route":
                    $cmd->set_command("show ip bgp route");
                    $cmd->set_ip_argument();
                    $cmd->set_subnet_argument();
                    $cmd->set_device_argument();
                    break;

                case "show eigrp topology":
                    $cmd->set_command("show ip eigrp topology");
                    $cmd->set_subnet_argument();
                    break;
            }

        }
    }
}

?>
