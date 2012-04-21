<?php
include("look.php");
?>
<HTML>
<HEAD>
    <STYLE TYPE="text/css">
        #lg_table {
            width: 760px;
            margin: 0px;
            padding: 10px;
        }

        #submit_area {
            vertical-align: middle;
            text-align: center;
        }

        .step {
            width: 250px;
            margin: 0px;
            padding: 0px;
            display: inline-block;
            vertical-align: middle;
        }

        #submit_area {
            padding: 10px;
            display: block;
            vertical-align: middle;
        }

        #lg_results {
            vertical-align: top;
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
    <SCRIPT LANGUAGE="JavaScript" SRC="jquery-1.7.2.min.js"></SCRIPT>
    <SCRIPT LANGUAGE="JavaScript">
        $(document).ready(function() {
            $("#submit_button").click(function() {
                // clear the result area before starting a new test
                $("#result_area").html("");

                var source = $("select#source :selected").val();
                var cmd = $("input#cmd:checked").val();
                var target_type = $("input#target_type:checked").val();

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

                var dataString = 'source='+ source + '&cmd=' + cmd + '&target_type=' + target_type + "&target=" + target;

                $.ajax({
                    type: "POST",
                    url: "lg.php",
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
<DIV ID="lg_table">
<FORM>
    <DIV ID="step1" CLASS="step">
        Source:<BR>
        <SELECT NAME="source" ID="source">
        <?php
            foreach($device_cfg as $key => $value) {
                echo "            <OPTION VALUE=\"$key\">" . $value["description"] . "</OPTION>\n";
            }
        ?>
        </SELECT>
    </DIV>
    <DIV ID="step2" CLASS="step">
        Command:<BR>
        <?php
            foreach($all_cmds as $cmd) {
                echo "<INPUT TYPE=\"radio\" NAME=\"cmd\" ID=\"cmd\" VALUE=\"" . $cmd->id . "\"/>" . $cmd->display . "<BR>\n";
            }
        ?>
    </DIV>
    <DIV ID="step3" CLASS="step">
        Target:<BR>
        IP address: <INPUT TYPE="radio" NAME="target_type" ID="target_type" VALUE="ip"><INPUT TYPE="text" NAME="target" ID="target_ip"><BR>
        Subnet: <INPUT TYPE="radio" NAME="target_type" ID="target_type" VALUE="subnet"><INPUT TYPE="text" NAME="target" ID="target_subnet"><BR>
        Example: 192.168.1.0/24<BR>
        Device: <INPUT TYPE="radio" NAME="target_type" ID="target_type" VALUE="device">
        <SELECT NAME="target_device" ID="target_device">
        <?php
            foreach($device_cfg as $key => $value) {
                echo "            <OPTION VALUE=\"$key\">" . $value["description"] . "</OPTION>\n";
            }
        ?>
        </SELECT>
    </DIV>
    <DIV ID="submit_area">
        <INPUT TYPE="button" NAME="submit" ID="submit_button" VALUE="submit">
    </DIV>
</FORM>
<DIV ID="lg_results">
    Results:
    <BR>
    <DIV ID="ajax_busy"><IMG SRC="ipLoader.gif" BORDER="0"></DIV>
    <BR>
    <PRE ID="result_area"></PRE>
</DIV>
</DIV>
</DIV>
</BODY>
</HTML>

