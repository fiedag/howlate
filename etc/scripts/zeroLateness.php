<?php
/*
 * deletes lateness records which are no longer current
 * does this both by age and by checking session times
 * 
 * If a lateness record is over 8 hours old it gets deleted
 * If a doctor's session ended over 2 hours ago, the lateness gets deleted
 * 
 * 
 * 
 */
date_default_timezone_set('Australia/Melbourne');

echo "\r\n";
echo "\r\n";
echo "\r\n";

function mylog($msg) {
    echo date("Y-m-d H:i:s e", time()) . ":" . $msg . "\r\n";
}

mylog("**************** zeroing out old lateness records ******************");

include("/home/howlate/public_html/master/model/trantype.class.php");
include("/home/howlate/public_html/master/model/howlate_db.class.php");
include("/home/howlate/public_html/master/model/howlate_util.class.php");

$db = new howlate_db();
// simply delete those older than 8 hours, even sticky ones
$db->deleteOldLates();

$result = $db->getLateTimezones();
foreach ($result as $key => $TZval) {
    mylog("++++++zeroing lateness ++++++++++++++++++ Processing timezone = " . $TZval->Timezone . " +++++++++++++++++");

    $tolerance = 7200;  // two hours

    $day = howlate_util::dayName("now", $TZval->Timezone);
    $time = howlate_util::secondsSinceMidnight("now", $TZval->Timezone);

    $toprocess = $db->getLatesAndSessions($TZval->Timezone, $day, $time);
    foreach ($toprocess as $key => $val) {

        mylog("[UKey,Org,ID,Upd,Minutes,TZ,Day,Start,End] = [$val->UKey , $val->OrgID, $val->ID, $val->Updated, $val->Minutes, $val->Timezone, $val->Day, $val->StartTime, $val->EndTime ]");

        // we are only going to delete entries where sessions exist at all for that day
        // if sessions do not exist, then StartTime and EndTime will return -1 and we will ignore
        // and rely on manual updates
        if ($val->StartTime >= 0 and $val->EndTime >= 0) {
            $start = $val->StartTime - $tolerance;
            $end = $val->EndTime + $tolerance;

            if ($start < 0) {
                $start = $start + 86400;
            }
            if ($end > 86400) {
                $end = $end - 86400;
            }
            if ($time < $start or $time > $end) {
                $msg = "Time right now is $time, ";
                if ($time < $start) {
                    $msg .= "session starts at $start ";
                }
                if ($time > $end) {
                    $msg .= "session ends at $end ";
                }
                $msg .= " so lateness can be deleted.";
                mylog($msg);
                $db->deleteLateByKey($val->UKey);
                $db->trlog(TranType::LATE_RESET,"Lateness deleted because session ended", $val->OrgID, null, $val->ID, null);
                mylog("    Deleted lateness.UKey = $val->UKey");
            }
        }
    }
}
?>
