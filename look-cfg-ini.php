<?php

[defaults]
; Options are: "Cisco" or "Juniper"
vendor = "Cisco"

; Options are "telnet" or "sshv2"
connection = "sshv2"

; The username you have configured on your router
username = "ssh-user";

; The password you have configured on your router
password = "password";

; These are all of the commands that will show up on the webpage.
; If you don't want to allow users to do some of these commands,
; then comment out the offending line by putting a ';' at the
; beginning of the line.  If you want to add more commands, start
; by adding it here and then go into each device file and add
; the appropriate information (like look-cisco.php or
; look-juniper.php).  Don't worry if these aren't the exact
; commands for your router, they are for displaying to the user.
; For example, on a cisco router you don't run "show route"
; you run "show ip route".  This is handled in the look-cisco.php
; file.
commands[] = "ping"
commands[] = "traceroute"
commands[] = "show route"
commands[] = "show bgp route"
commands[] = "show eigrp topology"

["10.211.55.44"]
hostname = "10.211.55.44"
description = "r1"

["192.168.255.2"]
hostname = "192.168.255.2"
description = "r2"

["10.211.55.2"]
hostname = "10.211.55.2"
description = "Mac OS X"
vendor = "Juniper"

["10.211.55.15"]
hostname = "10.211.55.15"
description = "Atlanta, GA"
vendor = "blah"

?>
