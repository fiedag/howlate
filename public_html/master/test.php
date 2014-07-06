<?php
   
echo  $_SERVER["SERVER_NAME"];


$site_path = realpath(dirname(__FILE__));

echo $site_path;

    
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Some page title</title>
</head>
 
<body>
 
<?php
    echo $xcrud->render();
?>
 
</body>
</html>