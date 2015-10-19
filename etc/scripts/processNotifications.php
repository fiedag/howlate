<?php

/*
 * Table howlate_main.notifqueue contains SMS notifications which are 
 * queued to go out.  This program gets those which have not yet been sent
 * and sends them.
 * 
 * TODO: What happens when an exception is thrown.  Test and ensure 
 * the results are detected.
 * 
 * 
 */

$real = filter_input(INPUT_GET,'real');


define('__SITE_PATH', realpath(dirname(__FILE__) . "/../../master"));

include __SITE_PATH . '/model/HowLate_SMS.class.php';
include 'autoload.php';

date_default_timezone_set('Australia/Melbourne');

if(isset($real)) {
    $sms = new HowLate_SMS(new Clickatell());
}
else {
    $sms = new HowLate_SMS(new MockClickatell());
}

function mylog($msg) {
    echo date("Y-m-d H:i:s e", time()) . ":" . $msg . "\r\n<br>";
}

mylog("**************** Processing Queued notifications ******************");

$new_notif = HowLate_Util::getQueuedNotifications();

foreach ($new_notif as $key => $val) {

    mylog ("New queued notification to send: $val->UID, Mobile=$val->MobilePhone , Message='$val->Message'");

    try {
        $sms->httpSend($val->OrgID, $val->MobilePhone, $val->Message, $val->ClinicID, $val->PractitionerID);
        HowLate_Util::dequeueNotification($val->UID);
    } catch (Exception $ex) {
        throw $ex;
    }
}

?>