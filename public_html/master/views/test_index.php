<!DOCTYPE html>
<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <meta http-equiv="Cache-control" content="no-cache">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    </head>

    <body>
       
        <form id="update" action="/test/check_package" method="POST">
            <input type="text" id="OrgID" name="OrgID" value="CCENV">
            <input type="submit" value="Package" name="submit">
        </form>

        <br>
        <br>
        <br>
        <br>
        
        <form id="update" action="/test/create_line" method="POST">
            <input type="text" id="package_id" name="package_id" value="559">
            <input type="text" id="line_item_id" name="line_item_id" value="598">
            <input type="text" id="descrip" name="descrip" value="Description">
            <input type="text" id="external_key" name="external_key" value="121">
            
            <input type="submit" value="Update Package Line" name="submit">
        </form>

        
        
    </body>


</html>
