<?php

date_default_timezone_set('Australia/Melbourne');

echo "\r\n";
echo "\r\n";
echo "\r\n";

function mylog($msg) {
    echo date("Y-m-d H:i:s ", time()) . ":" . $msg . "<br>";
}

mylog("**************** BILL DUE ACCOUNTS ******************");

include("/home/howlate/public_html/master/model/billing.class.php");
include("/home/howlate/public_html/master/model/invoicer.class.php");
include("/home/howlate/public_html/master/model/howlate_util.class.php");

$billing = new billing();

$RightNow = new DateTime();

// all those orgs where NextBillingDate <= today
$orgs = $billing->getDueOrgs();

foreach($orgs as $key=>$val) {
    mylog("$val->OrgName ($val->OrgID) is due for billing.  Period is : $val->LastBillingDay to " . $RightNow->format('Y-m-d H:i:sP'));
    $clin_count = $billing->getOrgClinicCount($val->OrgID);
    mylog("Free Clinics:" . $clin_count->FreeClinics);
    mylog("Small Clinics:" . $clin_count->SmallClinics);
    mylog("Large Clinics:" . $clin_count->LargeClinics);
    mylog("Superclinics:" . $clin_count->SuperClinics);
    
    $num_sms_sent = $billing->getOrgSMSCount($val->OrgID, $val->LastBillingDay, $RightNow->format('Y-m-d H:i:sP'));
    mylog("Organisation $val->OrgID sent $num_sms_sent SMSs in that billing period");

    $invoicer = new invoicer();
    try {
        $invoicer->createNewInvoice($val->OrgID, $val,$clin_count,$num_sms_sent);
    } catch(Exception $ex) {
        mylog("Unable to create invoice, error = " . $ex->getMessage() . ",program=" . $ex->getFile() . " (" . $ex->getLine() . ") " . $ex->getTraceAsString());
    }
}

?>