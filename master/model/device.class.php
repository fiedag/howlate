<?php

/* 
 * Device e.g. a smartphone or similar which has 
 * registered to receive lateness updates
 */

class Device {
    protected static $instance;
    public $udid; // unique device id
    public $canonical_udid;
    public $registrations;  // array of registrations for this
    
    
    /*
     * 
     * Returns an array of latenesses and must be ordered by Clinic Name
     */
    public static function getLatenesses($fieldval, $fieldname = 'UDID') {
        $q = "SELECT ClinicID, ClinicName, AbbrevName, MinutesLate, MinutesLateMsg, OrgID, ID, Subdomain, AllowMessage FROM vwMyLates WHERE $fieldname = '" . $fieldval . "' ORDER BY ClinicName";
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
    
    /*
     * 
     */
    public static function getLatenessesByUDID($udid) {
        $q = "SELECT CONCAT(OrgID ,'.',ID) As Pin, MinutesLateMsg FROM vwMyLates WHERE UDID = '" . $udid . "'";
        $sql = MainDb::getInstance();
        $myArray = array();
        if ($result = $sql->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[$row->Pin] = $row->MinutesLateMsg;
            }
            return $myArray;
        }
    }
    
    public static function getLatenessesByUDID2($fieldval, $fieldname = 'UDID') {
        $q = "SELECT ClinicID, ClinicName, AbbrevName, MinutesLate, MinutesLateMsg, OrgID, ID, Subdomain, AllowMessage FROM vwMyLates WHERE $fieldname = '" . $fieldval . "' ORDER BY ClinicName";
        $sql = MainDb::getInstance();
        $myArray = array();
        if ($result = $sql->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
            return $myArray;
        }
    }
    
    public static function invite($org, $id, $udid, $domain) {
       $prac = Practitioner::getInstance($org,$id);
       //$url = "http://m.$domain/late/view&xudid=" . howlate_util::to_xudid($udid);

       $url = "http://m.$domain/late/view&udid=" . $udid;

       $message = "Current delays for $prac->PractitionerName at $prac->ClinicName can be checked at " . $url;

       HowLate_SMS::httpSend($org, $udid, $message);
       Logging::trlog(TranType::DEV_SMS, $message, $prac->OrgID, $prac->ClinicID, $prac->PractitionerID, $udid);
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
}

?>