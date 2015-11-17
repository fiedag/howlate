<!DOCTYPE html>
<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <meta http-equiv="Cache-control" content="no-cache">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    </head>

    <body>

        
        <script>
          $("#localtime").text("Hello there");
        
        
        </script>        
   
        
        
        
        <form id="update" action="/api?ver=post&clin=29&met=upd" method="POST">
            <input type="text" id="Practitioner" name="Practitioner" value="Dr Natasha Litjens">
            <input type="text" id="AppointmentTime" name="AppointmentTime" value="70000">
            <input type="text" id="ArrivalTime" name="ArrivalTime" value="71000">
            <input type="text" id="ConsultationTime" name="ConsultationTime" value="72000">
            <input type="text" id="NewLate" name="NewLate" value="2000">
            <input type="text" id="credentials" name="credentials" value="alexf.9cbf8a4dcb8e30682b927f352d6559a0">
            <input type="submit" value="Update Late" name="submit">
        </form>
                
        
        <!-- form id="sessions" action="/api?ver=post&clin=31&met=sess" method="POST">
            <input type="text" id="Practitioner" name="Practitioner">
            <input type="text" id="Day" name="Day">
            <input type="text" id="StartTime" name="StartTime">
            <input type="text" id="EndTime" name="EndTime">
            <input type="text" id="credentials" name="credentials" value="alexf.9cbf8a4dcb8e30682b927f352d6559a0">
            <input type="submit" value="Update Session" name="submit">
        </form -->
        
        
        <!-- form id="upload" action="/test/upload" method="post" enctype="multipart/form-data">
            Select image to upload:
            <input type="file" accept="image/*" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Upload Image" name="submit">
        </form -->
        
        
<!--
        <form id="email" name="email" method="post" action="/test/email">
            <div class="control-group">
                <label class="control-label" for="email">Email:</label>
                <input type="email" class="controls" id="email" name="email" size="50" value="alex.fiedler@internode.on.net"></input>
            </div>

            <div class="control-group">
                <label class="control-label" for="to">To:</label>
                <input type="text" class="controls" id="to" name="to" size="50" value="Alex Fiedler"></input>
            </div>

            <div class="control-group">
                <label class="control-label" for="subject">Subject:</label>
                <input type="text" class="controls" id="subject" name="subject" size="50" value="Subject Line"></input>
            </div>

            <div class="control-group">
                <label class="control-label" for="body">Body:</label>
                <input type="text" class="controls" id="body" name="body" size="50" value="BBody of the Email"></input>
            </div>

            <div class="control-group">
                <label class="control-label" for="from">From:</label>
                <input type="text" class="controls" id="from" name="from" size="50" value="noreply@How-Late.com"></input>
            </div>
            <div class="control-group">
                <label class="control-label" for="fromName">From Name:</label>
                <input type="text" class="controls" id="fromName" name="fromName" size="50" value="HOW-LATE.COM"></input>
            </div>


            <div class="xcrud-nav">
                <button class="xcrud-button xcrud-cyan " type="submit" name="Submit" value="Submit">Send</button>
            </div>

        </form>
-->

    </body>


</html>
