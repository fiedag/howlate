<?php

class logging {
    
    public static function smslog($orgid, $api, $destination, $session, $messageid, $message)
    {
        $q = "INSERT INTO sentsms (OrgID, API, SessionID, MessageID, MessageText, Destination) VALUES (?,?,?,?,?,?)";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('ssssss',$orgid, $api,$session,$messageid,$message, $destination);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
           trigger_error("The row was not inserted into the sentsms table, error= " . $this->conn->error , E_USER_ERROR);
        }
    }
    
    public static function trlog($TranType, $Details, $OrgID = null, $ClinicID = null, $PractitionerID = null, $UDID = null, $Late = 0) {
        $IPAddress = $_SERVER["REMOTE_ADDR"];
        if (is_null($IPAddress) or $IPAddress == "") {
            $IPAddress = "localhost";
        }
        $q = "INSERT INTO transactionlog (TransType, OrgID, ClinicID, PractitionerID, Details, UDID, IPv4, Late) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = maindb::getInstance()->prepare($q);
        //$tz = date_default_timezone_get();
        $stmt->bind_param('ssissssi', $TranType, $OrgID, $ClinicID, $PractitionerID, $Details, $UDID, $IPAddress, $Late);
        $stmt->execute();  
        if ($stmt->affected_rows == 0) {
           trigger_error("The row was not inserted into the transactionlog table, error= " . $this->conn->error , E_USER_ERROR);
        }
    }
    
    public static function write_error($errno, $errtype, $errstr, $errfile, $errline, $ipaddress = 'localhost') {
        $q = "INSERT INTO errorlog (ErrLevel, ErrType, File, Line, ErrMessage, IPv4) VALUES (?,?,?,?,?,?)";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('ssssss', $errno, $errtype, $errfile, $errline, $errstr, $ipaddress);
        $stmt->execute();
    }
    
    public static function stdout($msg) {
        echo $msg . "<br>";
        
    }
}
