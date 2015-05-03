<?php

class practitioner {

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

    public static function getInstance($OrgID, $FieldValue, $FieldName = 'PractitionerID') {

        $q = "SELECT OrgID, PractitionerID, Pin, PractitionerName, FullName, " .
                "ClinicName, ClinicID, OrgName, FQDN, NotificationThreshold, LateToNearest, LatenessOffset, LatenessCeiling " .
                "FROM vwPractitioners WHERE OrgID = '$OrgID' AND $FieldName = ";
        $q .= ($FieldName == "SurrogKey")?$FieldValue:"'$FieldValue'";

        $sql = maindb::getInstance();
        
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

        $sql = maindb::getInstance();
        
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
        return howlate_util::logoURL($this->Subdomain);
    }

    public static function updateLateness($OrgID, $PractitionerID, $ClinicID, $NewLate, $ConsultationTime, $Sticky = 0) {
        $q = "CALL sp_LateUpd(?, ?, ?, ?)";
        $sql = maindb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ssii', $OrgID, $PractitionerID, $NewLate, $Sticky);
        if (!$stmt->execute()) {
            throw new Exception("# Query Error $OrgID, $PractitionerID, $NewLate, $Sticky ( $sql->errno ) "  . $sql->error);
        }
        logging::trlog(TranType::LATE_UPD, "Lateness now $NewLate , Sticky = $Sticky.", $OrgID, $ClinicID, $PractitionerID, null, $NewLate);
        return "lateness updated ok";
    }


    public static function updatesessions($org, $id, $day, $start, $end) {
        $q = "REPLACE INTO sessions (OrgID, ID, Day, StartTime, EndTime) VALUES (?,?,?,?,?)";
        $sql = maindb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('sssii', $org, $id, $day, $start, $end);
        $stmt->execute() or trigger_error('# Query Error (' . $sql->errno . ') ' . $sql->error, E_USER_ERROR);
        logging::trlog(TranType::SESS_UPD, "$day Session updated to [$start,$end]", $org, null, $id, null);
    }

    public function place2($org, $surrogkey, $clinic) {
        $q = "REPLACE INTO placements (OrgID, ID, ClinicID) SELECT OrgID, ID, '$clinic' FROM practitioners WHERE OrgID = ? AND SurrogKey = ?";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('ss', $org, $surrogkey);
        $stmt->execute();
    }
    public function place($org, $id, $clinic) {
        $q = "REPLACE INTO placements (OrgID, ID, ClinicID) SELECT OrgID, ID, '$clinic' FROM practitioners WHERE OrgID = ? AND ID = ?";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('ss', $org, $id);
        $stmt->execute();
    }
    
    public function displace($org, $id, $clinic) {
        $q = "DELETE FROM placements WHERE OrgID = ? AND ID = ? AND ClinicID = ?";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $org, $id, $clinic);
        $stmt->execute();
    }

    public static function createDefaultPractitioner($orgid, $clinic, $name) {
        $q = "CALL sp_CreatePractitioner (?, ?, ?)";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('sis', $orgid, $clinic, $name);
        if (!$stmt->execute()) {
            throw new Exception("sp_CreatePractitioner($orgid, $clinic, $name)" . $stmt->error);
        }
        if ($stmt->affected_rows != 1) {
            throw new Exception("The default practitioner was not created, error= " . $stmt->error, E_USER_ERROR);
        }
        logging::trlog(TranType::MISC_MISC,"Default Practitioner $name created",$orgid);
        return self::getInstance($orgid, $name, 'FullName');
    }
    
    public function enqueueNotification($MobilePhone, $domain = 'how-late.com') {
        
        $MobilePhone = trim($MobilePhone);
        $MobilePhone = preg_replace("/[^0-9]/", "", $MobilePhone);
        device::register($this->OrgID, $this->PractitionerID, $MobilePhone);
        
        $lateness = $this->getCurrentLateness();
        if(!$lateness) {
            return "Practitioner is not assigned to clinic, not enqueued.";
        }
        
        if (strtolower($lateness) == "on time" or strtolower($lateness) == "off duty") 
            return "Practitioner $this->PractitionerName ($this->PractitionerID) is $lateness, not enqueued";

        
        $q = "SELECT COUNT(0) As AlreadyDone FROM notifqueue WHERE OrgID = '$this->OrgID' AND PractitionerID = '$this->PractitionerID' AND ClinicID = $this->ClinicID AND MobilePhone = '$MobilePhone' AND Created >= CURDATE()";
        $row = maindb::getInstance()->query($q)->fetch_object();
        if ($row->AlreadyDone >= 1) {
            return "Patient $MobilePhone already notified today.  Not enqueued.";
        }
        
        $url = "http://m." . $domain . "/late/view&udid=$MobilePhone";
        
        //$msg = $this->PractitionerName . " is running " . $lateness . ". For updates,click " . $url;

        $msg = notification::getMessage($this, $lateness, $MobilePhone, $domain);
        
        // this takes care of duplicates
        $q = "CALL sp_EnqueueNotification(?,?,?,?,?)";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('sisss', $this->OrgID, $this->ClinicID, $this->PractitionerID, $MobilePhone, $msg);
        $stmt->execute() or trigger_error('# Query Error (' . $stmt->errno . ') ' . $stmt->error, E_USER_ERROR);
        
        return "SMS message to $MobilePhone queued.";
    }
    
    
    public function getCurrentLateness() {
        $q = "SELECT MinutesLateMsg FROM vwLateness WHERE OrgID = '$this->OrgID' AND ID = '$this->PractitionerID'";
        // always guaranteed to get one row
        if ($result = maindb::getInstance()->query($q)) {
            $row = $result->fetch_object();
            if (!$row) {
                return null;
            }
            return $row->MinutesLateMsg;
        }
        $result->close(); 
        return null;
    }
}
?>