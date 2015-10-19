<?php

function __autoload($class_name) {
    $filename = strtolower($class_name) . '.class.php';
    $file = '/home/howlate/public_html/master/model/' . $filename;
    if (file_exists($file) == false) {
        return false;
    }
    include ($file);
}

function mylog($msg) {
    echo date("Y-m-d H:i:s e", time()) . ":" . $msg . "\r\n";
}

mylog("**************** Processing deletions of old transactionlog records !!! ******************");

$logging = new Logging();
$logging->deleteOld(365);

?>