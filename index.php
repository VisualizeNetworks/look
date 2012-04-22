<?php
include("look.php");

?>
<HTML>
<HEAD>
    <LINK REL="stylesheet" TYPE="text/css" HREF="css/shadows.css">
    <STYLE TYPE="text/css">
       BODY {
            background-image: url('img/background.png');
            padding:20px 0 30px;
            color:#333;
        }

        #look_table {
            width: 750px;
            text-align: center;
        }

        #look_title_block {
            text-align: center;
        }

        #look_form_table {
            width: 750px;
            margin: 0px;
            padding: 10px;
            border: 1px black;
            text-align: left;
            vertical-align: middle;
        }

        .look_form_header_cell {
        }

        .look_form_body_cell {
        }

        #submit_area {
            height: 50px;
            text-align: center;
        }

        #look_results {
            vertical-align: top;
            text-align: left;
        }

        #result_area {
            vertical-align: top;
            padding: 0px;
            color: #00AA00;
        }

        #ajax_busy {
            display: none;
            padding: 0px;
            margin: 0px;
        }
    </STYLE>
    <SCRIPT LANGUAGE="JavaScript" SRC="js/jquery-1.7.2.min.js"></SCRIPT>
    <SCRIPT LANGUAGE="JavaScript">
        $(document).ready(function() {
            $("#submit_button").click(function() {
                // clear the result area before starting a new test
                $("#result_area").html("");

                var source = $("select#source :selected").val();
                var cmd = $("input#cmd:checked").val();
                var target_type = $("input[name=target_type]:checked").val();

                var target = "";
                switch(target_type) {
                    case "ip":
                        target = $("input#target_ip").val();
                        break;
                    case "subnet":
                        target = $("input#target_subnet").val();
                        break;
                    case "device":
                        target = $("select#target_device :selected").val();
                        break;
                }

                var dataString = 'form=' + 'ajax' + '&source='+ source + '&cmd=' + cmd + '&target_type=' + target_type + "&target=" + target;

                $.ajax({
                    type: "POST",
                    url: "look.php",
                    data: dataString,
                    success: function(result) {
                        $('#result_area').html(result);
                    }
                });
                return false;
            });

            $(document).ajaxStart(function(){
                $('#ajax_busy').show();
            }).ajaxStop(function(){
                $('#ajax_busy').hide();
            });
        });
    </SCRIPT>
</HEAD>
<BODY>
<!--
<DIV ID="look_table" CLASS="drop-shadow curved curved-hz-2">
-->
<DIV ID="look_table" CLASS="box">
    <DIV ID="look_title_block">
        <A HREF="https://github.com/drewpc/look" TARGET="_new"><IMG SRC="img/logo.png" BORDER="0"></A>
    </DIV>
    <FORM NAME="look">
    <TABLE ID="look_form_table">
        <TR CLASS="look_form_row">
            <TH CLASS="look_form_header_cell">Source:</TH>
            <TH CLASS="look_form_header_cell">Command:</TH>
            <TH CLASS="look_form_header_cell">Target:</TH>
        </TR>
        <TR CLASS="look_form_row">
            <TD CLASS="look_form_body_cell">
                <SELECT NAME="source" ID="source">
                <?php
                    foreach($device_cfg as $key => $value) {
                        echo "            <OPTION VALUE=\"$key\">" . $value["description"] . "</OPTION>\n";
                    }
                ?>
                </SELECT>
            </TD>
            <TD CLASS="look_form_body_cell">
            <?php
                foreach($all_cmds as $cmd) {
                    echo "<INPUT TYPE=\"radio\" NAME=\"cmd\" ID=\"cmd\" VALUE=\"" . $cmd->id . "\"/>&nbsp;&nbsp;&nbsp;" . $cmd->display . "<BR>\n";
                }
            ?>
            </TD>
            <TD CLASS="look_form_body_cell">
                <INPUT TYPE="radio" NAME="target_type" ID="target_ip_radio" VALUE="ip">&nbsp;&nbsp;&nbsp;IP Address: <INPUT TYPE="text" NAME="target" ID="target_ip" onFocus="$('input#target_ip_radio').prop('checked', true);"><BR>
                <INPUT TYPE="radio" NAME="target_type" ID="target_subnet_radio" VALUE="subnet">&nbsp;&nbsp;&nbsp;Subnet: <INPUT TYPE="text" NAME="target" ID="target_subnet" onFocus="$('input#target_subnet_radio').prop('checked', true);"><BR>
                <I>Example: 192.168.1.0/24</I><BR>
                <INPUT TYPE="radio" NAME="target_type" ID="target_device_radio" VALUE="device">&nbsp;&nbsp;&nbsp;Device:
                <SELECT NAME="target_device" ID="target_device" onChange="$('input#target_device_radio').prop('checked', true);">
                <?php
                    foreach($device_cfg as $key => $value) {
                        echo "            <OPTION VALUE=\"$key\">" . $value["description"] . "</OPTION>\n";
                    }
                ?>
                </SELECT>
            </TD>
        </TR>
        <TR CLASS="look_form_row">
            <TD ID="submit_area" CLASS="look_form_body_cell" COLSPAN="3">
                <INPUT TYPE="button" NAME="submit" ID="submit_button" VALUE="Execute Command">
            </TD>
        </TR>
    </TABLE>
    </FORM>
    <DIV ID="look_results">
        Results:
        <DIV ID="ajax_busy"><IMG SRC="img/loading.gif" BORDER="0"></DIV>
        <PRE ID="result_area"></PRE>
    </DIV>
</DIV>
</BODY>
</HTML>

