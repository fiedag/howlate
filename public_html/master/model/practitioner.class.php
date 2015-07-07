<?php

class Practitioner {

    protected static $instance;
    public $OrgID;
    public $ClinicName;
    public $ClinicID;
    public $Subdomain;
    public $Pin;
    public $PractitionerID;
    public $PractitionerName;
    public $FullName;
    public $OrgName;
    public $FQDN;
    public $NotificationThreshold;
    public $LateToNearest;
    public $LatenessOffset;
    public $LatenessCeiling;

    public $AppointmentBook;  
    
    
    public static function getInstance($OrgID, $FieldValue, $FieldName = 'PractitionerID') {
        $q = "SELECT OrgID, PractitionerID, Pin, PractitionerName, FullName, " .
                "ClinicName, ClinicID, OrgName, FQDN, NotificationThreshold, LateToNearest, LatenessOffset, LatenessCeiling " .
                "FROM vwPractitioners WHERE OrgID = '$OrgID' AND $FieldName = ";
        $q .= ($FieldName == "SurrogKey")?$FieldValue:"'$FieldValue'";
        $sql = MainDb::getInstance();
        
        if ($result = $sql->query($q)->fetch_object()) {
            if (!self::$instance) {
                self::$instance = new self();
            }
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            
            return self::$instance;
        }
        else {
            return null;
        }
    }
    
    public static function getInstance2($OrgID, $FieldValue, $FieldName = 'PractitionerID') {
        $q = "SELECT OrgID, PractitionerID, Pin, PractitionerName, FullName, " .
                "ClinicName, ClinicID, OrgName, FQDN, NotificationThreshold, LateToNearest, LatenessOffset, LatenessCeiling " .
                "FROM vwPractitioners WHERE OrgID = '$OrgID' AND $FieldName = ";
        $q .= ($FieldName == "SurrogKey")?$FieldValue:"'$FieldValue'";
        $sql = MainDb::getInstance();
        echo $q;
        
        if ($result = $sql->query($q)->fetch_object()) {
            if (!self::$instance) {
                self::$instance = new self();
            }
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            
            return self::$instance;
        }
        else {
            return null;
        }
    }
    

    
    
    public function logoURL() {
        return HowLate_Util::logoURL($this->Subdomain);
    }


    public function updateLateness($NewLate, $Sticky = 0, $Manual = 1) {

        $q = "CALL sp_LateUpd(?, ?, ?, ?, ?)"; // last parameter denotes manual update
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ssiii', $this->OrgID, $this->PractitionerID, $NewLate, $Sticky, $Manual);
        if (!$stmt->execute()) {
            throw new Exception("# Query Error $this->OrgID, $this->PractitionerID, $NewLate, $Sticky ( $sql->errno ) "  . $sql->error);
        }
        Logging::trlog(TranType::LATE_UPD, "Lateness now $NewLate , Sticky = $Sticky.", $this->OrgID, $this->ClinicID, $this->PractitionerID, null, $NewLate);
        return "lateness updated ok";
    }

    public static function updatesessions($org, $clinicid, $id, $day, $start, $end) {
        $q = "REPLACE INTO sessions (OrgID, ID, Day, StartTime, EndTime, ClinicID) VALUES (?,?,?,?,?,?)";
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('sssiii', $org, $id, $day, $start, $end, $clinicid);
        $stmt->execute() or trigger_error('# Query Error (' . $sql->errno . ') ' . $sql->error, E_USER_ERROR);
        Logging::trlog(TranType::SESS_UPD, "$day Session updated to [$start,$end]", $org, $clinicid, $id, null);
    }

    
    /*
     * Returns true if the session is ended today
     * as at @AsAt seconds since midnight
     */
    public function isSessionEnded($AsAt) {
        $q = "SELECT EndTime FROM sessions WHERE OrgID = ? and ID = ? AND ClinicID = ? AND Day = ?";
        $row = MainDb::getInstance()->query($q)->fetch_object();
        return (!$row->EndTime || $row->EndTime < $AsAt);
    }

    public function place2($org, $surrogkey, $clinic) {
        $q = "REPLACE INTO placements (OrgID, ID, ClinicID) SELECT OrgID, ID, '$clinic' FROM practitioners WHERE OrgID = ? AND SurrogKey = ?";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('ss', $org, $surrogkey);
        $stmt->execute();
    }
    public function place($org, $id, $clinic) {
        $q = "REPLACE INTO placements (OrgID, ID, ClinicID) SELECT OrgID, ID, '$clinic' FROM practitioners WHERE OrgID = ? AND ID = ?";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('ss', $org, $id);
        $stmt->execute();
    }
    
    public function displace($org, $id, $clinic) {
        $q = "DELETE FROM placements WHERE OrgID = ? AND ID = ? AND ClinicID = ?";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $org, $id, $clinic);
        $stmt->execute();
    }

    public static function createDefaultPractitioner($orgid, $clinic, $name) {
        $q = "CALL sp_CreatePractitioner (?, ?, ?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sis', $orgid, $clinic, $name);
        if (!$stmt->execute()) {
            throw new Exception("sp_CreatePractitioner($orgid, $clinic, $name)" . $stmt->error);
        }
        if ($stmt->affected_rows != 1) {
            throw new Exception("The default practitioner was not created, error= " . $stmt->error, E_USER_ERROR);
        }
        Logging::trlog(TranType::MISC_MISC,"Default Practitioner $name created",$orgid);
        return self::getInstance($orgid, $name, 'FullName');
    }
    

    /* enqueue a message with the lateness 
     * adjusted for any gaps
     */
    public function enqueueAdjustedMessage($FromClinicID, $MobilePhone, $PublishedLateness, $Domain = 'how-late.com') {
        if ($FromClinicID != $this->ClinicID) {
            // 
            return "Doctor not enrolled or wrong clinic.  Message not queued";
        }
        $MobilePhone = trim($MobilePhone);
        $MobilePhone = preg_replace("/[^0-9]/", "", $MobilePhone);
        Device::register($this->OrgID, $this->PractitionerID, $MobilePhone);
        
        $q = "SELECT COUNT(0) As AlreadyDone FROM notifqueue WHERE OrgID = '$this->OrgID' AND PractitionerID = '$this->PractitionerID' AND ClinicID = $this->ClinicID AND MobilePhone = '$MobilePhone' AND Created >= CURDATE()";
        $row = MainDb::getInstance()->query($q)->fetch_object();
        if ($row->AlreadyDone != "0") {
            return "Patient $MobilePhone already notified today.  Not queued.";
        }
        
        $url = "http://m." . $Domain . "/late/view&udid=$MobilePhone";
        
        $msg = Notification::getMessage($this, $PublishedLateness, $MobilePhone, $Domain);
        
        // this takes care of duplicates and suppression based on clinic.SuppressNotifications etc
        $q = "CALL sp_EnqueueNotification(?,?,?,?,?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sisss', $this->OrgID, $this->ClinicID, $this->PractitionerID, $MobilePhone, $msg);
        $stmt->execute() or trigger_error('# Query Error (' . $stmt->errno . ') ' . $stmt->error, E_USER_ERROR);
        
        return "SMS message to $MobilePhone queued.";
    }
    
    public function enqueueNotification($MobilePhone, $domain = 'how-late.com') {
        
        $MobilePhone = trim($MobilePhone);
        $MobilePhone = preg_replace("/[^0-9]/", "", $MobilePhone);
        Device::register($this->OrgID, $this->PractitionerID, $MobilePhone);
        
        $lateness = $this->getCurrentLateness();
        if(!$lateness) {
            return "Practitioner is not assigned to clinic, not enqueued.";
        }
        
        if (strtolower($lateness) == "on time" or strtolower($lateness) == "off duty") 
            return "Practitioner $this->PractitionerName ($this->PractitionerID) is $lateness, not enqueued";

        
        $q = "SELECT COUNT(0) As AlreadyDone FROM notifqueue WHERE OrgID = '$this->OrgID' AND PractitionerID = '$this->PractitionerID' AND ClinicID = $this->ClinicID AND MobilePhone = '$MobilePhone' AND Created >= CURDATE()";
        $row = MainDb::getInstance()->query($q)->fetch_object();
        if ($row->AlreadyDone != "0") {
            return "Patient $MobilePhone already notified today.  Not enqueued.";
        }
        
        $url = "http://m." . $domain . "/late/view&udid=$MobilePhone";
        
        //$msg = $this->PractitionerName . " is running " . $lateness . ". For updates,click " . $url;

        $msg = Notification::getMessage($this, $lateness, $MobilePhone, $domain);
        
        // this takes care of duplicates and suppression based on clinic.SuppressNotifications etc
        $q = "CALL sp_EnqueueNotification(?,?,?,?,?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sisss', $this->OrgID, $this->ClinicID, $this->PractitionerID, $MobilePhone, $msg);
        $stmt->execute() or trigger_error('# Query Error (' . $stmt->errno . ') ' . $stmt->error, E_USER_ERROR);
        
        return "SMS message to $MobilePhone queued.";
    }
    
    
    
    // Used by lateness screen
    public function getCurrentLateness() {
        $q = "SELECT MinutesLateMsg FROM vwLateness WHERE OrgID = '$this->OrgID' AND ID = '$this->PractitionerID'";
        // always guaranteed to get one row
        if ($result = MainDb::getInstance()->query($q)) {
            $row = $result->fetch_object();
            if (!$row) {
                return null;
            }
            return $row->MinutesLateMsg;
        }
        $result->close(); 
        return null;
    }
    
    /* NEW PREDICTIVE ALGORITHM IMPROVES CAPABILITY
     * Parameter @AsAt is a time of day expressed in seconds since midnight
     * Returns seconds late expressed in seconds
     * Adjusted to account for offset, threshold and offset
     */
    public function getActualLateness() {
        return $this->AppointmentBook->CurrentLate;
    }

    /*
     * Parameter @ActualLateness is in minutes
     * Returns Published lateness as a string
     */
    public function getLatenessMsg($ActualLateness) {
        $result = "on time";
        if ($ActualLateness <= 0 || $ActualLateness < $this->NotificationThreshold) {
            $result = "on time";
        }
        elseif($this->LatenessCeiling > 0 && $ActualLateness >= $this->LatenessCeiling) {
            $result = $this->durationStr($this->LatenessCeiling);
        }
        else {
            $tonearest = $this->LateToNearest;
            if ($tonearest == 0) {
                $tonearest = 1;
            }
            $rounded = $tonearest * round($ActualLateness / $tonearest,0,PHP_ROUND_HALF_UP);
        
            $adjusted = $rounded - $this->LatenessOffset;
            $result = $this->durationStr($adjusted);
        }
        return trim(strtolower($result));
        
    }
    
    private function durationStr($minutes) {
        $result = "";
        $hours = floor($minutes / 60);
        $minutes = floor($minutes % 60);
        if ($hours == 0) {
            $result = "";
        }
        elseif($hours == 1) {
            $result = "an hour";
        }
        else {
            $result = "$hours hours";
        }
        if ($minutes != 0) {
            $result .= " $minutes minutes"; 
        }
        return $result . " late";
    }
    
    
    public function setAppointmentBook($appt_array, $time_now) {
        $this->AppointmentBook = ApptBook::getInstance($this->OrgID, $this->ClinicID, $time_now, $appt_array);
        return $this;
    }

    
    public function predictConsultTimes() {
        if($this->AppointmentBook) {
            $this->AppointmentBook->traverseAppointments();
        }
        return $this;
    }
   
    // the new function where $ActualLateSeconds argument is in seconds!
    public function LatenessUpdate($ActualLateSeconds) {
        $InMinutes = round($ActualLateSeconds / 60, 0, PHP_ROUND_HALF_UP);
        $q = "CALL sp_LateUpd(?, ?, ?, 0, 0)";
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ssi', $this->OrgID, $this->PractitionerID, $InMinutes);
        if (!$stmt->execute()) {
            throw new Exception("# Query Error $this->OrgID, $this->PractitionerID, $InMinutes ( $sql->errno ) "  . $sql->error);
        }
        Logging::trlog(TranType::LATE_UPD, "Agent update, now $InMinutes late", $this->OrgID, $this->ClinicID, $this->PractitionerID, null, $InMinutes);
        return "lateness updated ok";
    }
    
}
?>