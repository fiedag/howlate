<?php

date_default_timezone_set('Australia/Melbourne');

echo "\r\n";
echo "\r\n";
echo "\r\n";

function mylog($msg) {
    echo date("Y-m-d H:i:s ", time()) . ":" . $msg . "\r\n";
}

mylog("**************** zeroing out old lateness records ******************");

include("/home/howlate/public_html/master/model/billing.class.php");
include("/home/howlate/public_html/master/model/howlate_db.class.php");
include("/home/howlate/public_html/master/model/howlate_util.class.php");

$billing = new billing();

$orgs = $billing->getDueOrgs();

foreach($orgs as $key=>$val) {
    mylog("Organisation $val->OrgID is due for billing.  Period is : $val->LastBillingDay to $val->NextBillingDay");
    $clin_count = $billing->getOrgClinicCount($val->OrgID);
    mylog(var_dump($clin_count));
    mylog("Free Clinics:" . $clin_count->FreeClinics);
    mylog("Small Clinics:" . $clin_count->SmallClinics);
    mylog("Large Clinics:" . $clin_count->LargeClinics);
    mylog("Superclinics:" . $clin_count->SuperClinics);
    
    $num_sms_sent = $billing->getOrgSMSCount($val->OrgID, $val->LastBillingDay, $val->NextBillingDay);
    mylog("Organisation $val->OrgID sent $num_sms_sent SMSs in that billing period");

}

?>