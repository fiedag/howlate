<?php

spl_autoload_register(function ($class_name) {
    $filename = strtolower($class_name) . '.class.php';
    $file = '/home/howlate/public_html/master/model/' . $filename;
    if (file_exists($file) == false) {
        return false;
    }
    include ($file);
});



function mylog($msg) {
    echo date("Y-m-d H:i:s e", time()) . ":" . $msg . "\r\n";
}

mylog("**************** Processing Usage for All organisations!!! ******************");



$billing = new billing();
$billing->recordAllUsage();

?>