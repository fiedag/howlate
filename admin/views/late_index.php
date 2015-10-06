<!DOCTYPE html>
<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <meta http-equiv="Cache-control" content="no-cache">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

        <!-- add json2html; both the core library and the jquery wrapper -->
        <script type="text/javascript" src="includes/json2html/json2html.js"></script>
        <script type="text/javascript" src="includes/json2html/jquery.json2html.js"></script>


    </head>

    <body>

        <!-- Load JSON2HTML -->
        <script type="text/javascript">

            //Transforms

             var transforms = {
			'Clinic': [
					{"tag":"li","html":"${ClinicName} <b>${AbbrevName} </b> ${MinutesLateMsg}"}
                        ]
             };

            //Callback Function
            function formatContent(json) {
                
                if (json !== undefined)
                    $('#content').json2html(json, transforms.Clinic);
            }





        </script>

        <h1 id="header">What this device sees</h1>

        <div id="content"></div>

        <script>
                    formatContent('<?php echo $json_result; ?>');

        </script>
        
    </body>


</html>
