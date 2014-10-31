<?php

class clinic extends howlate_basetable {

    protected static $instance;
    
    public $OrgID;
    public $ClinicID;
    public $ClinicName;
    public $Timezone;
    public $Address1;
    public $Address2;
    public $City;
    public $Country;
    public $Phone;
    public $State;

    public static function getInstance($OrgID, $ClinicID) {
        $q = "SELECT * FROM clinics WHERE OrgID = '$OrgID' AND ClinicID = $ClinicID";

        $sql = maindb::getInstance();
        
        if ($result = $sql->query($q)->fetch_object()) {
            if (!self::$instance) {
                self::$instance = new self();
            }
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            return self::$instance;
        } else
            return null;
    }

    
    public function getClinicIntegration() {
        $q = "SELECT * FROM clinicintegration WHERE OrgID = '$this->OrgID' AND ClinicID = $this->ClinicID";
        if ($result = maindb::getInstance()->query($q)) {
            return $result->fetch_object();
        }
        
    }
    
    // creates all the clinic integration records.  one for each clinic of the organisation
    public function createClinicIntegration() {
        $q = "CALL sp_CreateClinicIntegration('$this->OrgID', $this->ClinicID)";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->execute();
    }

    
    public function updateClinicIntegration($instance, $database, $uid, $pwd, $interval, $hluserid, $processrecalls) {
        $q = "REPLACE INTO clinicintegration (Instance, DbName, UID, PWD, PollInterval, HLUserID, OrgID, ClinicID, ProcessRecalls ) VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('ssssissis', $instance, $database, $uid, $pwd, $interval, $hluserid, $this->OrgID, $this->ClinicID, $processrecalls);
        $stmt->execute();
        if($stmt->affected_rows == 0)
        {
            throw new Exception("Clinic Integration was not updated.");
        }

    }    
    
    public static function createDefaultClinic($orgid) {
        $q = "INSERT INTO clinics (OrgID, ClinicName) SELECT OrgID, OrgName FROM orgs WHERE OrgID = ?";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('s', $orgid);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The default clinic record was not created, error= " . $this->conn->error, E_USER_ERROR);
        }
    }
    
}
