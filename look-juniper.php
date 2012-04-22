<?php

class JuniperDevice extends BaseDevice {
    function setup_cmds() {
        foreach($GLOBALS["all_cmds"] as $key => $cmd) {
            switch($cmd->display) {
                case "ping":
                    $this->device_cmds[$key] = $cmd->display;
                    $cmd->set_command("ping %arg% count 5");
                    $cmd->allow_ip_argument();
                    $cmd->allow_device_argument();
                    break;

                case "traceroute":
                    $this->device_cmds[$key] = $cmd->display;
                    $cmd->set_command("traceroute %arg% wait 10");
                    $cmd->allow_ip_argument();
                    $cmd->allow_device_argument();
                    break;

                case "show route":
                    $this->device_cmds[$key] = $cmd->display;
                    $cmd->set_command("show route %arg%");
                    $cmd->allow_ip_argument();
                    $cmd->allow_device_argument();
                    break;

                case "show bgp route":
                    $this->device_cmds[$key] = $cmd->display;
                    $cmd->set_command("show route protocol bgp %arg%");
                    $cmd->allow_ip_argument();
                    $cmd->allow_subnet_argument("bits");
                    $cmd->allow_device_argument();
                    break;
            }
        }
    }
}

?>
