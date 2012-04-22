<?php

# get config file
include("look-cfg.php");

include("look-command.php");

include("look-basedevice.php");
include("look-cisco.php");
include("look-juniper.php");

$all_cmds = array();
$all_cmds[] = new DeviceCommand("ping");
$all_cmds[] = new DeviceCommand("traceroute");
$all_cmds[] = new DeviceCommand("show route");
$all_cmds[] = new DeviceCommand("show bgp route");
$all_cmds[] = new DeviceCommand("show eigrp topology");

function sort_by_description($a, $b) {
    if ($a["description"] == $b["description"]) {
        return 0;
    }
    return ($a["description"] < $b["description"]) ? -1 : 1;
}

uasort($device_cfg, "sort_by_description");

// the page was called from the HTML form
if($_SERVER['REQUEST_METHOD'] == 'POST' and $_POST["form"] == "ajax") {
    $src_index = $_POST["source"];
    if($src_index < 0 or $src_index > count($device_cfg) - 1) {
        echo "ERROR: invalid source";
    } else {
        $device = $device_cfg[$src_index];
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
                $device_obj = new CiscoDevice($hostname, $device["description"], $connection, $username, $password);
                break;

            case "juniper":
                $device_obj = new JuniperDevice($hostname, $device["description"], $connection, $username, $password);
                break;
        }

        $cmd_index = $_POST["cmd"];

        $target_type = $_POST["target_type"];
        $target = $_POST["target"];

        echo $device_obj->query_router($cmd_index, $target_type, $target);
    }
}

?>
