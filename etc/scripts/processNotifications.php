<?php


function __autoload($class_name) {
    $filename = strtolower($class_name) . '.class.php';
    $file = '/home/howlate/public_html/master/model/' . $filename;
    if (file_exists($file) == false) {
        return false;
    }
    include ($file);
}


date_default_timezone_set('Australia/Melbourne');

echo "\r\n";
echo "\r\n";

function mylog($msg) {
    echo date("Y-m-d H:i:s e", time()) . ":" . $msg . "\r\n";
}

/*
include("/home/howlate/public_html/master/model/howlate_db.class.php");
include("/home/howlate/public_html/master/model/howlate_util.class.php");
include("/home/howlate/public_html/master/model/howlate_sms.class.php");
include("/home/howlate/public_html/master/model/clickatell.class.php");
*/

mylog("**************** processing queued notifications ******************");

$db = new howlate_db();
$new_notif = $db->getQueuedNotifications();

foreach ($new_notif as $key => $val) {
    echo "New queued notification to send: $val->MobilePhone , $val->Message \r\n";
    try {
        if ($val->TestMobile == '') {
            howlate_sms::httpSend($val->OrgID, $val->MobilePhone, $val->Message);
        } else {
            howlate_sms::httpSend($val->OrgID, $val->TestMobile, $val->Message);
        }
        $db->dequeueNotification($val->UID);
    } catch (Exception $ex) {
        throw $ex;
    }
}
?>