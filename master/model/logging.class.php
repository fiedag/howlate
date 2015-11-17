<?php

class Logging {

    public static function smslog($orgid, $api, $destination, $session, $messageid, $message)
    {
        $q = "INSERT INTO sentsms (OrgID, API, SessionID, MessageID, MessageText, Destination) VALUES (?,?,?,?,?,?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('ssssss',$orgid, $api,$session,$messageid,$message, $destination);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
           trigger_error("The row was not inserted into the sentsms table, error= " . $this->conn->error , E_USER_ERROR);
        }
    }

    public static function trlog($TranType, $Details, $OrgID = null, $ClinicID = null, $PractitionerID = null, $UDID = null, $Late = 0, $AgentVersion = 0) {
        if (!isset($_SERVER["REMOTE_ADDR"])) {
            $IPAddress = "localhost";
        }
        else {
            $IPAddress = $_SERVER["REMOTE_ADDR"];
        }
        
        $q = "INSERT INTO transactionlog (TransType, OrgID, ClinicID, PractitionerID, Details, UDID, IPv4, Late, AgentVersion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = MainDb::getInstance()->prepare($q);
        //$tz = date_default_timezone_get();
        $stmt->bind_param('ssissssis', $TranType, $OrgID, $ClinicID, $PractitionerID, $Details, $UDID, $IPAddress, $Late, $AgentVersion);
        $stmt->execute();  
        if ($stmt->affected_rows == 0) {
           trigger_error("The row was not inserted into the transactionlog table, error= " . $this->conn->error , E_USER_ERROR);
        }
    }
    
    public static function write_error($errno, $errtype, $errstr, $errfile, $errline, $ipaddress = 'localhost', $traceAsString = null) {
        $q = "INSERT INTO errorlog (ErrLevel, ErrType, File, Line, ErrMessage, IPv4, Trace) VALUES (?,?,?,?,?,?,?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sssssss', $errno, $errtype, $errfile, $errline, $errstr, $ipaddress, $traceAsString);
        $stmt->execute();
    }
    
    public static function stdout($msg) {
        echo $msg . "<br>";
        
    }
    
    public static function deleteOld($retain_days = 50) {
        $q = "DELETE FROM transactionlog WHERE TransType IN ('LATE_UPD','MISC_MISC','LATE_GET') AND Timestamp < DATE_SUB(CURRENT_DATE, $retain_days DAY)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->execute();
    }
    
    public static function getlog($FieldValue, $FieldName = 'Details') {
        $rows = array();
        $q = "SELECT * FROM transactionlog WHERE $FieldName = '$FieldValue'";
        if ($result = MainDb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $rows[] = $row;
            }
        }
        return $rows;
    } 


}
