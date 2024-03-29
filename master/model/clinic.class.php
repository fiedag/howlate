<?php

class Clinic {

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

    public $NotifDestination;
    public $DisplayPolicy;
    
    public static function getInstance($OrgID, $ClinicID) {
        $q = "SELECT * FROM clinics WHERE OrgID = '$OrgID' AND ClinicID = $ClinicID";
        $sql = MainDb::getInstance();

        if ($result = $sql->query($q)->fetch_object()) {
            self::$instance = new self();
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            return self::$instance;
        } else
            throw new Exception("Clinic $ClinicID not found");
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

    
    // creates the clinic integration record.
    public function createClinicIntegration() {
        $q = "CALL sp_CreateClinicIntegration('$this->OrgID', $this->ClinicID)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->execute();
    }

    public function updateClinicIntegration($instance, $database, $uid, $pwd, $interval, $hluserid) {
        $q = "REPLACE INTO clinicintegration (Instance, DbName, UID, PWD, PollInterval, HLUserID, OrgID, ClinicID ) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('ssssissi', $instance, $database, $uid, $pwd, $interval, $hluserid, $this->OrgID, $this->ClinicID);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            throw new Exception("Clinic Integration was not updated.");
        }
    }

    public function updateClinicIntegration2($interval, $hluserid, $pmsystem, $connectiontype, $connectionstring) {
        $q = "REPLACE INTO clinicintegration (OrgID, ClinicID, PollInterval, HLUserID, PMSystem, ConnectionType, ConnectionString ) VALUES (?,?,?,?,?,?,?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('siissss', $this->OrgID, $this->ClinicID, $interval, $hluserid, $pmsystem, $connectiontype, $connectionstring);
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

    public function getPlacedPractitioners($quoted = true) {
        $q = "SELECT FullName FROM vwPlacements WHERE OrgID = '$this->OrgID' AND ClinicID = '$this->ClinicID'";
        $sql = MainDb::getInstance();

        $practArray = array();
        if ($result = $sql->query($q)) {
            while ($row = $result->fetch_object()) {
                if($quoted) {
                    $practArray[] = "'" . $row->FullName . "'";
                }
                else {
                    $practArray[] = $row->FullName;
                }
            }
            return $practArray;
        }
        return null;
    }
    
    // for a clinic, get all latenesses for a UDID
    public function getLatenesses($UDID) {
        $q = "SELECT * FROM vwClinicDeviceLates" .
             " WHERE OrgID = :orgid AND ClinicID = :clinicid AND UDID = :udid";
        $stmt = db::getInstance()->prepare($q);
        $stmt->bindParam(':orgid', $this->OrgID);
        $stmt->bindParam(':clinicid', $this->ClinicID);
        $stmt->bindParam(':udid',$UDID);
        $stmt->execute();
        $tempArray=array();
        while ($o = $stmt->fetchObject()) {
            $tempArray[] = $o;
        }
        return $tempArray;
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
        
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ssss', $this->OrgID, $this->ClinicID, $TypeCode, $TypeDescr);
        $ret = $stmt->execute() or trigger_error('# Query Error (' . $sql->errno . ') ' . $sql->error, E_USER_ERROR);
        Logging::trlog(TranType::APTYPE_UPD, "Appt Type updated [$TypeCode,$TypeDescr]", $this->OrgID, $this->ClinicID, null, null);        
        return $ret;
        
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
        
        if ($outfile = fopen("/home/howlate/public_html/master/logs/" . $this->OrgID . "/" . $this->ClinicID . ".log.inc", "a")) {
            
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
    
    public function lastAgentUpdate() {
        $q = "select IFNULL(TIMESTAMPDIFF(MINUTE, MAX(timestamp), NOW()), -1) 
            As ElapsedMin from transactionlog where OrgID = '" . $this->OrgID ."' AND ClinicID = " . $this->ClinicID .
            " AND TransType like 'AGT_%'";
        if ($result = MainDb::getInstance()->query($q)) {
            return $result->fetch_row()[0];
        }
    }

    
    public static function providerList($OrgID, $ClinicID) {
        $clinic = Clinic::getInstance($OrgID, $ClinicID);
        
        $pract = $clinic->getPlacedPractitioners();
        $list = implode(",",$pract);
        return "(" . $list . ")";
    }    
}
