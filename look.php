<?php

# Load the command class package.
include("look-command.php");

# Load the device class packages.
# Have a new device?  Add it here!
include("look-basedevice.php");
include("look-cisco.php");
include("look-juniper.php");

# Get config file
$CONFIG = parse_ini_file("look-cfg-ini.php", true);

$all_cmds = array();
foreach($CONFIG["defaults"]["commands"] as $cmd) {
    $all_cmds[] = new DeviceCommand($cmd);
}

$all_devices = array();
foreach($CONFIG as $section => $entry) {
    if($section != "defaults") {
        $all_devices[] = $entry;
    }
}

# This is a helper function that sorts the all_devices array
# by the 'description' field while maintaining the index
# association.  This lets the user see a sorted list in
# the device drop down box.
function sort_by_description($a, $b) {
    if ($a["description"] == $b["description"]) {
        return 0;
    }
    return ($a["description"] < $b["description"]) ? -1 : 1;
}

# Sort the $all_devices array using the above function.
uasort($all_devices, "sort_by_description");

# This code is only executed when the user clicks "execute command"
# on the webpage and the browser connects via AJAX.
if($_SERVER['REQUEST_METHOD'] == 'POST' and $_POST["form"] == "ajax") {
    # This is the array index of the device that the user wants
    # to connect to.
    $src_index = $_POST["source"];

    # A valid $src_index will be one of the devices in $all_devices
    if(0 <= $src_index and $src_index < count($all_devices)) {
        # Get the all_devices object that we're interested in.
        $device = $all_devices[$src_index];

        # Get the hostname
        $hostname = $device["hostname"];
        $description = $device["description"];

        # For each of these values, if there is no value set, then use the DEFAULT_ value.
        # This helps the administrator out by letting him use the same username/password for
        # a long list of routers without having to list the values under each device
        $vendor = array_key_exists("vendor", $device) ? $device["vendor"] : $CONFIG["defaults"]["vendor"];
        $username = array_key_exists("username", $device) ? $device["username"] : $CONFIG["defaults"]["username"];
        $password = array_key_exists("password", $device) ? $device["password"] : $CONFIG["defaults"]["password"];
        $connection = array_key_exists("connection", $device) ? $device["connection"] : $CONFIG["defaults"]["connection"];

        switch(strtolower($vendor)) {
            case "cisco":
                $device_obj = new CiscoDevice($hostname, $description, $connection, $username, $password);
                break;

            case "juniper":
                $device_obj = new JuniperDevice($hostname, $description, $connection, $username, $password);
                break;

            default:
                $device_obj = NULL;
                echo "ERROR: look is misconfigured - invalide device vendor. Please contact the network administrator";
                return true;
        }

        # Get the rest of the form elements.
        $cmd_index = $_POST["cmd"];
        $target_type = $_POST["target_type"];
        $target = $_POST["target"];

        # Query the device.  It will figure out which connection type to use.
        echo $device_obj->query_router($cmd_index, $target_type, $target);
    } else {
        echo "ERROR: invalid source";
    }
}

?>
