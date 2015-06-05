<?php

class Clinic extends Howlate_BaseTable {

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
    public $AllowMessage;
    public $MsgRecip;

    public $ApptLogging;
    
    public static function getInstance($OrgID, $ClinicID) {
        $q = "SELECT * FROM clinics WHERE OrgID = '$OrgID' AND ClinicID = $ClinicID";

        $sql = MainDb::getInstance();

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
        $q = "SELECT * FROM vwClinicIntegration WHERE OrgID = '$this->OrgID' AND ClinicID = $this->ClinicID";
        if ($result = MainDb::getInstance()->query($q)) {
            return $result->fetch_object();
        }
    }

    public function getAgentVersionTarget() {
        $q = "SELECT AgentVersionTarget FROM clinicintegration WHERE OrgID = '$this->OrgID' AND ClinicID = $this->ClinicID";
        if ($result = MainDb::getInstance()->query($q)) {
            return $result->fetch_object();
        }
    }

    
    // creates all the clinic integration records.  one for each clinic of the organisation
    public function createClinicIntegration() {
        $q = "CALL sp_CreateClinicIntegration('$this->OrgID', $this->ClinicID)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->execute();
    }

    public function updateClinicIntegration($instance, $database, $uid, $pwd, $interval, $hluserid, $processrecalls) {
        $q = "REPLACE INTO clinicintegration (Instance, DbName, UID, PWD, PollInterval, HLUserID, OrgID, ClinicID, ProcessRecalls ) VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('ssssissis', $instance, $database, $uid, $pwd, $interval, $hluserid, $this->OrgID, $this->ClinicID, $processrecalls);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            throw new Exception("Clinic Integration was not updated.");
        }
    }

    public function updateClinicIntegration2($interval, $hluserid, $processrecalls, $pmsystem, $connectiontype, $connectionstring) {
        $q = "REPLACE INTO clinicintegration (OrgID, ClinicID, PollInterval, HLUserID, ProcessRecalls, PMSystem, ConnectionType, ConnectionString ) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('siisisss', $this->OrgID, $this->ClinicID, $interval, $hluserid, $processrecalls, $pmsystem, $connectiontype, $connectionstring);
        $stmt->execute();
        if ($stmt->affected_rows <= 0) {
            throw new Exception(__FUNCTION__ . " Clinic Integration was not updated. Error=" . $stmt->error);
        }
    }

    public static function createDefaultClinic($orgid) {
        $q = "INSERT INTO clinics (OrgID, ClinicName) SELECT OrgID, OrgName FROM orgs WHERE OrgID = ?";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('s', $orgid);
        $stmt->execute();
        if ($stmt->affected_rows != 1) {
            trigger_error("The default clinic record was not created, error= " . $stmt->error, E_USER_ERROR);
        }
    }

    public function getPractitioners() {
        $q = "SELECT * FROM practitioners WHERE OrgID = '$this->OrgID' AND ClinicID = '$this->ClinicID'";
        $sql = MainDb::getInstance();

        $practArray = array();
        $clinArray = array();
        if ($result = $sql->query($q)) {
            $tempArray = array();
            while ($row = $result->fetch_object()) {
                $tempArray[] = $row;
                if (array_key_exists($row->ClinicName, $clinArray)) {
                    $clinArray[$row->ClinicName] = $tempArray;
                } else {
                    unset($tempArray);
                    $tempArray = array();
                    $tempArray[] = $row;
                    $clinArray[$row->ClinicName] = $tempArray;
                }
            }
            return $clinArray;
        }
        return null;
    }

    public function getPlacedPractitioners() {
        
        
        $q = "SELECT FullName FROM vwPlacements WHERE OrgID = '$this->OrgID' AND ClinicID = '$this->ClinicID'";
        $sql = MainDb::getInstance();

        $practArray = array();
        if ($result = $sql->query($q)) {
            while ($row = $result->fetch_object()) {
                $practArray[] = "'" . $row->FullName . "'";
            }
            return $practArray;
        }
        return null;
    }
    
    
    
    public function cancelAppointmentMessage($OrgID, $PractitionerID, $PractitionerName, $UDID) {
        $mail = new Howlate_Mailer();

        $toEmail = $this->MsgRecip;
        $toName = $this->ClinicName;
        $subject = "HOW-LATE Cancellation Advisory $UDID";
        $body = "$UDID has advised that they will not be able to keep their appointment with $PractitionerName";
        $from = HowLate_Util::admin_email();
        $fromName = "How-Late Admin";
        $mail->send($toEmail, $toName, $subject, $body, $from, $fromName);
    }

    // returns local time in seconds since midnight, 
    // given UTC time in seconds since midnight
    public function toLocalTime($utc) {
        $offs = $this->get_timezone_offset("UTC", $this->Timezone);
        return ($utc + $offs)  % 86400;  // in case we have exceeded 24 hours
    }

    function get_timezone_offset($remote_tz, $origin_tz = null) {
        if ($origin_tz === null) {
            if (!is_string($origin_tz = date_default_timezone_get())) {
                return false; // A UTC timestamp was returned -- bail out!
            }
        }
        $origin_dtz = new DateTimeZone($origin_tz);
        $remote_dtz = new DateTimeZone($remote_tz);
        $origin_dt = new DateTime("now", $origin_dtz);
        $remote_dt = new DateTime("now", $remote_dtz);
        $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
        return $offset;
    }

    
    function updateApptTypes($TypeCode, $TypeDescr) {
        $q = "INSERT IGNORE INTO appttype (OrgID, ClinicID, TypeCode, TypeDescr) VALUES (?,?,?,?)";
        Logging::trlog(TranType::APTYPE_UPD, "Appt Type updated [$this->OrgID, $this->ClinicID,$TypeCode,$TypeDescr]", $this->OrgID, $this->ClinicID, null, null);        
        
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ssss', $this->OrgID, $this->ClinicID, $TypeCode, $TypeDescr);
        $stmt->execute() or trigger_error('# Query Error (' . $sql->errno . ') ' . $sql->error, E_USER_ERROR);
        Logging::trlog(TranType::APTYPE_UPD, "Appt Type updated [$TypeCode,$TypeDescr]", $this->OrgID, $this->ClinicID, null, null);        
    }
    
    function updateApptStatus($StatusCode, $StatusDescr) {
        $q = "INSERT IGNORE INTO apptstatus (OrgID, ClinicID, StatusCode, StatusDescr) VALUES (?,?,?,?)";
        
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ssss', $this->OrgID, $this->ClinicID, $StatusCode, $StatusDescr);
        $stmt->execute() or trigger_error('# Query Error (' . $sql->errno . ') ' . $sql->error, E_USER_ERROR);
        Logging::trlog(TranType::APTYPE_UPD, "Appt Status updated [$StatusCode,$StatusDescr]", $this->OrgID, $this->ClinicID, null, null);
    }
    

    public function apptLogging($array) {
        if ($this->ApptLogging) {
            $this->writeLog($array);
        }
        
    }
    
    private function writeLog($array) {
        
        if ($outfile = fopen("/home/howlate/public_html/master/logs/" . $this->OrgID . "." . $this->ClinicID . ".log.inc", "a")) {
            
            $exp = var_export($array,true);
            fwrite($outfile,"<?php" . "\r\n");
            fwrite($outfile, '    $appts[] =' . "\r\n");
            fwrite($outfile, $exp);
            fwrite($outfile, ";\r\n");
            fwrite($outfile,"?>");
            fclose($outfile);
        } else {
            throw new Exception("File open exception in writeLog.");
        }
    }
    
    
    
    
}
