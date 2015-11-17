<?php

/* 
 * Device e.g. a smartphone or similar which has 
 * registered to receive lateness updates
 */

class Device {
    protected static $instance;
    public $udid; // unique device id
    public $canonical_udid;
    public $registrations=array();  // array of registrations for this
    

    public static function getInstance($UDID) {
        $q = "SELECT * FROM devicereg WHERE UDID = '$UDID'";
        $sql = MainDb::getInstance();
        
        if ($result = $sql->query($q)) {        
            while($row = $result->fetch_object()) {
                $this->registrations[] = $row;
            }
        }
    }
    
    /*
     * register a device by UDID for a period of 12 months
     * 
     * 
     */
    public static function register($OrgID, $PractitionerID, $UDID) {
        $q = "REPLACE INTO devicereg (ID, OrgID, UDID, Expires) VALUES (?,?,?, CURDATE() + INTERVAL 12 MONTH )";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $PractitionerID, $OrgID, $UDID);
        $stmt->execute();
    }
    
    public static function updatePerspective(Practitioner $Practitioner, HowLate_Time $time, $UDID) {
        $realminutes = $time->toMinutes();
        $minutes = $time->toMinutesAdjusted();  // threshold, tonearest etc.
        $q = "REPLACE INTO lates (OrgID, ID, UDID, Updated, Minutes, RealMinutes, Override, AgentUpdate)" .
                " VALUES (:orgid,:id,:udid, NOW(), :minutes, :realminutes, 0, 1)";
        $stmt = db::getInstance()->prepare($q);
        $stmt->bindParam(":orgid", $Practitioner->OrgID);
        $stmt->bindParam(":id", $Practitioner->PractitionerID);
        $stmt->bindParam(":udid", $UDID);
        $stmt->bindParam(":minutes", $minutes);
        $stmt->bindParam(":realminutes", $realminutes);
        
        $stmt->execute();
//        if ($stmt->rowCount() != 1) {
//            throw new PDOException("Expected to replace exactly one perspective record, replaced " . $stmt->rowCount());
//        }
    }
    
    /*
     * 
     * Returns an array of latenesses and must be ordered by Clinic Name
     */
    public static function getLatenesses($fieldval, $fieldname = 'UDID') {
        $q = "SELECT ClinicID, ClinicName, AbbrevName, MinutesLate, OrgID, OrgName, ID, Pin, Subdomain FROM vwClinicDeviceLates" . 
                " WHERE $fieldname = :fieldval" . 
                " ORDER BY ClinicName";

        $clinArray = array();
        
        $stmt = db::getInstance()->prepare($q);
        $stmt->bindParam(':fieldval', $fieldval);
        $stmt->execute();
        $practArray = array();
        $clinArray = array();
        while ($row = $stmt->fetchObject()) {
            $row->MinutesLateMsg = "";
            $time = howlate_time::fromMinutes($row->MinutesLate);
            $row->MinutesLateMsg = $time->toHrsMinutesAdjusted();
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
    
    /*
     * 
     */
    public static function getLatenessesLite($udid) {
        $q = "SELECT Pin, OrgID, ID, MinutesLate FROM vwClinicDeviceLates WHERE UDID = '" . $udid . "'";
        $sql = MainDb::getInstance();
        $myArray = array();
        if ($result = $sql->query($q)) {
            while ($row = $result->fetch_object()) {
                $time = howlate_time::fromMinutes($row->MinutesLate);
                $myArray[$row->Pin] = $time->toHrsMinutesAdjusted();
            }
            return $myArray;
        }
    }

    
    public static function invite($org, $id, $udid, $domain) {
        $Practitioner = Practitioner::getInstance($org,$id);

        $Clinic = Clinic::getInstance($org, $Practitioner->ClinicID);
        $phone = new HowLate_Phone($udid, $Clinic);

        Device::register($org, $Practitioner->PractitionerID, $phone->CanonicalMobile);
        
        $url = "http://m." . __DOMAIN . "/late?xudid=" . $phone->XUDID;
        $message = "Current delays for $Practitioner->PractitionerName at $Practitioner->ClinicName can be checked at " . $url;

       $sms = new HowLate_SMS();
       $sms->httpSend($org, $udid, $message);
       Logging::trlog(TranType::DEV_SMS, $message, $Practitioner->OrgID, $Practitioner->ClinicID, $Practitioner->PractitionerID, $phone->CanonicalMobile);
    }
    
}

?>