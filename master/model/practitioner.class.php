<?php

class Practitioner {

    protected static $instance;
    
    public $OrgID;
    public $Subdomain;
    public $PractitionerID;
    public $Pin;
    public $PractitionerName;
    public $FullName;
    public $ClinicName;
    public $ClinicID;
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
            self::$instance = new self();
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            return self::$instance;
        }
        else {
            throw new Exception("Practitioner not found!");
        }
    }
    
    public static function getOrCreateInstance($OrgID, $ClinicID, $FieldValue, $FieldName = 'PractitionerID') {
        $q = "SELECT OrgID, PractitionerID, Pin, PractitionerName, FullName, " .
                "ClinicName, ClinicID, OrgName, FQDN, NotificationThreshold, LateToNearest, LatenessOffset, LatenessCeiling " .
                "FROM vwPractitioners WHERE OrgID = '$OrgID' AND $FieldName = ";
        $q .= ($FieldName == "SurrogKey")?$FieldValue:"'$FieldValue'";
        $sql = MainDb::getInstance();
        
        if ($result = $sql->query($q)->fetch_object()) {
            self::$instance = new self();
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            return self::$instance;
        }
        else {
            self::$instance = new self();
            return self::$instance->createDefaultPractitioner($OrgID, $ClinicID, $FieldValue);
        }
    }
    
    
//    public function logoURL() {
//        return HowLate_Util::logoURL($this->Subdomain);
//    }


    public function updateLateness($NewLate, $Override = 0, $Manual = 1) {

        $q = "CALL sp_LateUpd(?, ?, ?, ?, ?)"; // last parameter denotes manual update
        
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ssiii', $this->OrgID, $this->PractitionerID, $NewLate, $Override, $Manual);
        if (!$stmt->execute()) {
            throw new Exception("# Query Error $this->OrgID, $this->PractitionerID, $NewLate, $Override ( $sql->errno ) "  . $sql->error);
        }
        return array('PractitionerName'=>$this->PractitionerName,'New Late'=>$NewLate);
    }

    public function updateSessions($day, $start, $end) {
        $q = "REPLACE INTO sessions (OrgID, ID, Day, StartTime, EndTime, ClinicID) VALUES (?,?,?,?,?,?)";
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('sssiii', $this->OrgID, $this->PractitionerID, $day, $start, $end, $this->ClinicID);
        $ret = $stmt->execute() or trigger_error('# Query Error (' . $sql->errno . ') ' . $sql->error, E_USER_ERROR);
        Logging::trlog(TranType::SESS_UPD, "Session updated ", $this->OrgID, $this->ClinicID, null, null);
        
        return $ret;
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
    
    public function assign($clinic) {
        $q = "REPLACE INTO placements (OrgID, ID, ClinicID) SELECT OrgID, ID, '$clinic' FROM practitioners WHERE OrgID = ? AND ID = ?";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('ss', $this->OrgID, $this->PractitionerID);
        $stmt->execute();
        
    }
    
    public function displace($org, $id, $clinic) {
        $q = "DELETE FROM placements WHERE OrgID = ? AND ID = ? AND ClinicID = ?";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $org, $id, $clinic);
        $stmt->execute();
    }

    public function createDefaultPractitioner($orgid, $clinic, $name) {
        $q = "CALL sp_CreatePractitioner (?, ?, ?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sis', $orgid, $clinic, $name);
        if (!$stmt->execute()) {
            throw new Exception("sp_CreatePractitioner($orgid, $clinic, $name)" . $stmt->error);
        }
        if ($stmt->affected_rows != 1) {
            throw new Exception("The default practitioner was not created, error= " . $stmt->error, E_USER_ERROR);
        }
        return self::getInstance($orgid, $name, 'FullName');
    }

    
    
//    public function setAppointmentBook($appt_array, $time_now) {
//        $this->AppointmentBook = ApptBook::getInstance($this, $time_now, $appt_array);
//        return $this;
//    }


//    
//    // the new function where $ActualLateSeconds argument is in seconds!
//    public function LatenessUpdate($ActualLateSeconds) {
//        $InMinutes = round($ActualLateSeconds / 60, 0, PHP_ROUND_HALF_UP);
//        $q = "CALL sp_LateUpd(?, ?, ?, 0, 0)";
//        $sql = MainDb::getInstance();
//        $stmt = $sql->prepare($q);
//        $stmt->bind_param('ssi', $this->OrgID, $this->PractitionerID, $InMinutes);
//        if (!$stmt->execute()) {
//            throw new Exception("# Query Error $this->OrgID, $this->PractitionerID, $InMinutes ( $sql->errno ) "  . $sql->error);
//        }
//        Logging::trlog(TranType::LATE_UPD, "Agent update, now $InMinutes late", $this->OrgID, $this->ClinicID, $this->PractitionerID, null, $InMinutes);
//        return "lateness updated ok";
//    }
}
?>