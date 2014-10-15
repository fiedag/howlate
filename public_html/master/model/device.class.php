<?php

/* 
 * Device e.g. a smartphone or similar which has 
 * registered to receive lateness updates
 */

class device {
    protected static $instance;

    public $udid; // unique device id

    public $registrations;  // array of registrations for this
    
    public static function getLatenesses($udid) {
        $q = "SELECT ClinicID, ClinicName, AbbrevName, MinutesLate, MinutesLateMsg, OrgID, ID, Subdomain FROM vwMyLates WHERE UDID = '" . $udid . "'";
        $sql = maindb::getInstance();

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

    public static function invite($org, $id, $udid, $domain) {
        $prac = practitioner::getInstance($org,$id);

       $message = "Current delays for $prac->PractitionerName at $prac->ClinicName  can be checked at " .
       "http://secure.$domain/late/view&udid=$udid";

       $clickatell = new clickatell();
       $clickatell->httpSend( $udid, $message, $org);
       
       logging::trlog(TranType::DEV_SMS, $message, $prac->OrgID, $prac->ClinicID, $prac->PractitionerID, $udid);
    }
    
    public static function register($OrgID, $PractitionerID, $UDID) {
        $q = "REPLACE INTO devicereg (ID, OrgID, UDID, Expires) VALUES (?,?,?, CURDATE() + INTERVAL 12 MONTH )";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $PractitionerID, $OrgID, $UDID);
        $stmt->execute();
        
    }
}

?>