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

function getNotifications($status) {
    $tempArray=array();
    $q = "SELECT * FROM notifqueue WHERE Status = '$status'";
    $stmt = db::getInstance()->query($q);
    while($o = $stmt->fetchObject()) {
        $tempArray[] = $o;
    }
    return $tempArray;
}

function isAlreadySentToday(HowLate_Phone $MobilePhone, Clinic $Clinic, $PractitionerID) {
    $q = "SELECT COUNT(0) As AlreadyDone FROM notifqueue" .
            " WHERE OrgID = '" . $Clinic->OrgID . "'" .
            " AND PractitionerID = '" . $PractitionerID . "'" .
            " AND ClinicID = " . $Clinic->ClinicID . 
            " AND MobilePhone = '$MobilePhone->CanonicalMobile'" .
            " AND Status = 'Sent'" .
            " AND Created >= CURDATE()";
    $stmt = db::getInstance()->query($q);
    $result = $stmt->fetchObject();
    
    if ($result->AlreadyDone > 0) {
        return true;
    }
    return false;
}

function updateNotification($UID, $to, $status) {
    $q = "UPDATE notifqueue SET Status = :status, SentTo = :to WHERE UID = :uid";
    
    $stmt = db::getInstance()->prepare($q);
    $stmt->bindParam(":status",$status);
    $stmt->bindParam(":to",$to);
    $stmt->bindParam(":uid",$UID);
    $stmt->execute();
    if ($stmt->rowCount() != 1) {
        throw new PDOException("Expected to update one notiqueue record");
    }    
}

mylog("**************** Processing Queued notifications ******************");

$new_notif = getNotifications('Queued');

foreach ($new_notif as $key => $val) {
    //mylog("Queued notification: $val->UID, Mobile=$val->MobilePhone , Message='$val->Message', Created=$val->Created");

    $clinic = Clinic::getInstance($val->OrgID, $val->ClinicID);
    $phone = new HowLate_Phone($val->MobilePhone, $clinic);

    if (isAlreadySentToday($phone, $clinic, $val->PractitionerID)) {
        mylog("An SMS has already been sent today to this destination regarding this Practitioner");
        continue;
    }

    $now = new DateTime();
    $msg_created = new DateTime($val->Created);
    $msg_expires = $msg_created->add(new DateInterval('PT01H'));

    if ($now > $msg_expires) {
        mylog("Message has expired, do not send.  Mark as Expired");
        updateNotification($val->UID, 'Expired');
        continue;
    }

    $to="";
    if ($clinic->NotifDestination != "SMS") {
        $user = OrgUser::getInstance($clinic->OrgID, $clinic->NotifDestination);
        $mailer = new Howlate_Mailer();
        $to = $user->EmailAddress;
        $subject = "Mock SMS to " . $val->MobilePhone;
        $body = <<<EOD
<html>
<h1>SMS Destination: $val->MobilePhone</h1>
<h3>$val->Message</h3>
</html>
EOD;
        $mailer->sendHtml($to, $user->FullName, $subject, $body, 'noreply@how-late.com', 'How-Late.Com');
        mylog("Sent email to $user->EmailAddress");
    } else {
        $to = $val->MobilePhone;
        $sms = new HowLate_SMS(new Clickatell());
        $sms->httpSend($val->OrgID, $to, $val->Message, $val->ClinicID, $val->PractitionerID);
        mylog("Sent SMS to $to");
    }
    updateNotification($val->UID, $to, 'Sent');
}
?>