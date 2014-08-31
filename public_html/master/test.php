<?php


include("/home/howlate/public_html/master/model/howlate_db.class.php");
include("/home/howlate/public_html/master/model/howlate_util.class.php");

$db = new howlate_db();

$result = $db->getLateTimezones();
foreach($result as $key => $val) {
    echo "<h3>$key $val->Timezone</h3>"; 

    $tolerance = 7200;  // two hours
    
    $day = howlate_util::dayName("now", $val->Timezone);
    $time = howlate_util::secondsSinceMidnight("now", $val->Timezone);
    
    // 
    $toprocess = $db->getLatesAndSessions($val->Timezone, $day, $time);
    
    if (count($toprocess) > 0) {
        foreach ($toprocess as $key => $val) {

            echo " time = $time, [UKey,Org,ID,Upd,Minutes,TZ,Day,Start,End] = [$val->UKey , $val->OrgID, $val->ID, $val->Updated, $val->Minutes, $val->Timezone, $val->Day, $val->StartTime, $val->EndTime ]<br>";
            
            // we are only going to delete entries where sessions exist at all for that day
            // if sessions do not exist, then StartTime and EndTime will return -1
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
                $db->deleteLateByKey($val->UKey);
                echo "Delete $val->UKey<br>";
                }
                
            }
            
       }
    }
}

?>
