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

if (isset($_SERVER[''])) {
    die;  // should not run from web ever
}
define('__SITE_PATH', realpath(dirname(__FILE__) . "/../../master"));
include 'autoload.php';

date_default_timezone_set('Australia/Melbourne');

$proc = new proc();
$proc->go();


class proc {

    public function go() {
        $this->mylog("**************** processing stale lateness records ******************");
        $this->deleteStaleLatenessRecords();

        $zones = $this->getLateTimezones();
        foreach ($zones as $key => $zone) {
            $day = HowLate_Util::dayName("now", $zone->Timezone);
            $time = HowLate_Util::secondsSinceMidnight("now", $zone->Timezone);
            $this->mylog(" Processing timezone " . $zone->Timezone . ", day = " . $day);
            $tolerance = 7200;  // two hours

            $toprocess = $this->getLatesAndSessions($zone->Timezone, $day);
            foreach ($toprocess as $k => $val) {
                $this->mylog("UKey=$val->UKey,Clinic=$val->ClinicName,TZ=$val->Timezone,Day=$val->Day,Session Start $val->StartTime,Session End $val->EndTime");

                // we are only going to delete entries where sessions exist at all for that day
                // if sessions do not exist, then StartTime and EndTime will return -1 and we will ignore
                // and rely on manual updates (huh? what does that mean?)
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
                        $this->mylog($msg);
                        $this->deleteLateByKey($val->UKey);
                        $this->mylog("    Deleted lateness.UKey = $val->UKey");
                    }
                }
            }
        }
    }

    private function mylog($msg) {
        echo date("Y-m-d H:i:s e", time()) . ": " . $msg . "\n";
    }

    /*
     * This deletes any stale lateness records
     * which are from the perspective of a particular
     * patient
     */
    private function deleteStaleLatenessRecords() {
        $tempArray = array();
        $q = "DELETE FROM lates WHERE Updated < DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND UDID <> ''";
        $stmt = db::getInstance()->prepare($q);
        $stmt->execute();
        $this->mylog("Deleted " . $stmt->rowCount() . " stale lateness row" . (($stmt->rowCount() != 1) ? "s" : ""));
    }

    private function getLateTimezones() {
        $tempArray = array();
        $q = "SELECT DISTINCT Timezone FROM vwLateTZ";
        $stmt = db::getInstance()->query($q);
        while ($o = $stmt->fetchObject()) {
            $tempArray[] = $o;
        }
        return $tempArray;
    }

    private function getLatesAndSessions($timezone, $day) {
        // $day is Monday, Tuesday etc.
        // $time is in seconds since midnight
        $q = " SELECT v.UKey, v.OrgID, v.ID, v.Updated, v.Minutes, v.ClinicName, v.Timezone, s.Day, " .
                " IFNULL(s.StartTime, -1) As StartTime, IFNULL(s.EndTime,-1) As EndTime " .
                " FROM vwLateTZ v " .
                " LEFT OUTER JOIN sessions s on s.OrgID = v.OrgID and s.ID = v.ID and Day = :day " .
                " WHERE v.Timezone = :timezone";
        $stmt = db::getInstance()->prepare($q);
        $stmt->bindParam(":timezone", $timezone);
        $stmt->bindParam(":day", $day);
        $tempArray = array();
        $stmt->execute();
        $this->mylog(" $timezone and $day has returned " . $stmt->rowCount() . "  lateness record eligible for deletion");
        while ($o = $stmt->fetchObject()) {
            $tempArray[] = $o;
        }
        return $tempArray;
    }

    private function deleteLateByKey($UKey) {
        $q = "DELETE FROM lates WHERE UKey = :ukey";
        $stmt = db::getInstance()->prepare($q);
        $stmt->bindParam(":ukey", $UKey);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            $this->mylog("Expected to delete one lates record (UKey=$UKey), deleted " . $stmt->rowCount());
        }
    }
}

?>
