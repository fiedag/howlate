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
    public $OrgName;
    public $FQDN;
    public $NotificationThreshold;
    public $LateToNearest;
    public $LatenessOffset;

    public static function getInstance($OrgID, $FieldValue, $FieldName = 'PractitionerID') {

        $q = "SELECT OrgID, PractitionerID, Pin, PractitionerName, " .
                "ClinicName, ClinicID, OrgName, FQDN, NotificationThreshold, LateToNearest, LatenessOffset " .
                "FROM vwPractitioners WHERE OrgID = ? AND $FieldName = ?";
        $sql = maindb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ss',$OrgID, $FieldValue);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            return null;
        }

        if (!self::$instance) {
            self::$instance = new self();
        }
        $stmt->bind_result(self::$instance->OrgID, self::$instance->PractitionerID, 
                self::$instance->Pin, self::$instance->PractitionerName, self::$instance->ClinicName, 
                self::$instance->ClinicID, self::$instance->OrgName, self::$instance->FQDN, 
                self::$instance->NotificationThreshold, self::$instance->LateToNearest, self::$instance->LatenessOffset);
        $stmt->fetch();
        return self::$instance;
    }

    public function logoURL() {
        return howlate_util::logoURL($this->Subdomain);
    }

    public static function updateLateness($OrgID, $PractitionerID, $NewLate, $Sticky = 0) {
        $q = "CALL sp_LateUpd(?, ?, ?, ?)";
        $sql = maindb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ssii', $OrgID, $PractitionerID, $NewLate, $Sticky);
        $stmt->execute() or trigger_error('# Query Error (' . $sql->errno . ') ' . $sql->error, E_USER_ERROR);
        logging::trlog(TranType::LATE_UPD, "Lateness updated to $NewLate , Sticky = $Sticky.", $OrgID, null, $PractitionerID, null, $NewLate);
    }


    public static function updatesessions($org, $id, $day, $start, $end) {
        $q = "REPLACE INTO sessions (OrgID, ID, Day, StartTime, EndTime) VALUES (?,?,?,?,?)";
        $sql = maindb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('sssii', $org, $id, $day, $start, $end);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
        logging::trlog(TranType::SESS_UPD, "$day Session updated to [$start,$end]", $org, null, $id, null);
    }

    public function place($org, $id, $clinic) {
        $q = "REPLACE INTO placements (OrgID, ID, ClinicID) VALUES (?,?,?)";
        $stmt = maindb::getInstance()->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sss', $org, $id, $clinic);
        $stmt->execute();
    }

    public function place2($org, $surrogkey, $clinic) {
        $q = "REPLACE INTO placements (OrgID, ID, ClinicID) SELECT OrgID, ID, '$clinic' FROM practitioners WHERE OrgID = ? AND SurrogKey = ?";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('ss', $org, $surrogkey);
        $stmt->execute();
    }

    public function displace($org, $id, $clinic) {
        $q = "DELETE FROM placements WHERE OrgID = ? AND ID = ? AND ClinicID = ?";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $org, $id, $clinic);
        $stmt->execute();
    }

    public static function createDefaultPractitioner($orgid, $name) {
        $q = "CALL sp_CreatePractitioner (?, ?, ?)";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $org, $name, $outID);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The default practitioner was not created, error= " . $this->conn->error, E_USER_ERROR);
        }
        return self::getInstance($orgid, $name, 'FullName');
    }
    
    
    public function enqueueNotification($MobilePhone, $domain = 'how-late.com') {
        $MobilePhone = trim($MobilePhone);
        device::register($this->OrgID, $this->PractitionerID, $MobilePhone);
        
        $lateness = $this->getCurrentLateness();

        if ($lateness == "On time") 
            return;
        
        $url = "http://secure." . $domain . "/late/view&udid=$MobilePhone";
        $msg = $this->PractitionerName . " is running " . $lateness . ".  For updates, click " . $url;

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
            return $row->MinutesLateMsg;
        }
        $result->close(); 
        return null;
    }
}

?>
