<?php

if (isset($_SERVER[''])) {
    die;  // should not run from web ever
}
define('__SITE_PATH', realpath(dirname(__FILE__) . "/../../master"));
include 'autoload.php';

date_default_timezone_set('Australia/Melbourne');

function mylog($msg) {
    echo date("Y-m-d H:i:s e", time()) . ":" . $msg . "\r\n<br>";
}


mylog("**************** Processing Usage for All organisations!!! ******************");


$billing = new Billing();
$billing->recordAllUsage();

?>