<?php


/*
 * called by the apiController class
 * the methods in this class return valid
 * arrays which can be JSON encoded
 * else a string like 
 * "The text of the error".
 * 
 * 
 */
class howlate_api {

    //
    // Updates lateness of a practitioner.  called from HowLateAgent
    //
    public static function updatelateness() {

        $credentials = filter_input(INPUT_POST, "credentials");
        if ($credentials == null) {
            return "Credentials not supplied.";
        }
        list($userid, $passwordhash) = explode(".", $credentials);
        $db = new howlate_db();

        $org = new organisation();
        $org->getby(__SUBDOMAIN, "Subdomain");
        if ($db->isValidPassword($org->OrgID, $userid, $passwordhash)) {
            try {
                $providername = filter_input(INPUT_POST, "Provider");
                if ($providername == null) {
                    return "No Provider specified.";
                }

                $practitioner = $db->getPractitioner($org->OrgID, $providername, 'FullName');
                if ($practitioner->OrgID == null) {
                    $db->create_default_practitioner($org->OrgID, $providername);
                    $practitioner = $db->getPractitioner($org->OrgID, $providername, 'FullName');
                }

                $consultationTime = filter_input(INPUT_POST, "ConsultationTime");
                $appointmentTime = filter_input(INPUT_POST, "AppointmentTime");
                if ($consultationTime == null) {
                    return "Consultation Time not given.";
                }
                if ($appointmentTime == null) {
                    return "Appointment Time not given.";
                }
                $newlate = round(($consultationTime - $appointmentTime) / 60); // in minutes
                if ($newlate < 0) {
                    $newlate = 0;
                }

                $db->updatelateness($practitioner->OrgID, $practitioner->PractitionerID, $newlate);
                
                //$db->trlog(TranType::LATE_UPD, 'Practitioner ' . $practitioner->PractitionerName . ' is now ' . $newlate . ' minutes late', $org->OrgID, null, $practitioner->PractitionerID, null);
                return "Lateness for $providername has been updated to $newlate minutes";
            } catch (Exception $ex) {
                $db->trlog(TranType::LATE_UPD, 'Practitioner ' . $practitioner . ' lateness update failed, exception =' . $ex, $org->OrgID, null, $null, $null);
            }
        } else {
            return "Invalid credentials.";
        }
    }

    // 
    // Create or update the sessions information for the practitioners, 
    // one call per day per practitioner.  done when the agent starts
    public static function updatesessions() {
        $credentials = filter_input(INPUT_POST, "credentials");
        if ($credentials == null) {
            return "Credentials not supplied.";
        }
        list($userid, $passwordhash) = explode(".", $credentials);
        $db = new howlate_db();

        $org = new organisation();
        $org->getby(__SUBDOMAIN, "Subdomain");
        if ($db->isValidPassword($org->OrgID, $userid, $passwordhash)) {
            try {
                $Provider = filter_input(INPUT_POST, "Provider");
                $Day = filter_input(INPUT_POST, "Day");
                $StartTime = filter_input(INPUT_POST, "StartTime");
                $EndTime = filter_input(INPUT_POST, "EndTime");
                
                if ($Provider == null) {
                    return "No Provider specified.";
                }

                $practitioner = $db->getPractitioner($org->OrgID, $Provider, 'FullName');
                if ($practitioner->OrgID == null) {
                    $db->create_default_practitioner($org->OrgID, $Provider);
                    $practitioner = $db->getPractitioner($org->OrgID, $Provider, 'FullName');
                }
                
                $db->updatesessions($practitioner->OrgID, $practitioner->PractitionerID, $Day, $StartTime, $EndTime);
                
                $msg = "Practitioner " . $practitioner->PractitionerName . " works on $Day from $StartTime to $EndTime";
                $db->trlog(TranType::SESS_UPD, $msg, $org->OrgID, null, $practitioner->PractitionerID, null);
                return $msg;
            } catch (Exception $ex) {
                $db->trlog(TranType::SESS_UPD, 'Practitioner ' . $practitioner->PractitionerID . ' session info update failed, exception =' . $ex, $org->OrgID, null, $null, $null);
            }
        } else {
            return "Invalid credentials.";
        }
    }
    
    
    
    
    public static function registerpin($met, $ver) {
       
        $pin = filter_input(($ver == "get") ? INPUT_GET : INPUT_POST, "pin");
        $udid = filter_input(($ver == "get") ? INPUT_GET : INPUT_POST, "udid");
        if ($pin == null) {
            trigger_error("API Error: <b>$met</b> - you must supply the pin parameter <br>", E_USER_ERROR);
        }
        if ($udid == null) {
            trigger_error("API Error: <b>$met</b> - you must supply the udid parameter <br>", E_USER_ERROR);
        }        
        howlate_util::validatePin($pin);

        $org = howlate_util::orgFromPin($pin);
        $id = howlate_util::idFromPin($pin);
        $db = new howlate_db();
        $db->validatePin($org, $id);
        $db->register($udid, $org, $id);
        $db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'registered pin ' . $pin);
        return "Device $udid successfully registered pin $pin<br>";
    }

    
    ///
    /// there are post parameters telling us who to notify.  
    /// We will enter this info in a table and a cron job will process the new records in the table as required.
    public static function notify() {

        $clin = filter_input(INPUT_GET, "clin");

        $credentials = filter_input(INPUT_POST, "credentials");
        if ($credentials == null) {
            throw new Exception("Credentials not supplied.");
        }
        list($userid, $passwordhash) = explode(".", $credentials);
        $db = new howlate_db();

        $org = new organisation();
        $org->getby(__SUBDOMAIN, "Subdomain");
        if (!$db->isValidPassword($org->OrgID, $userid, $passwordhash)) {
            throw new Exception("Invalid user credentials.");
        }

        $Patient = filter_input(INPUT_POST, "Patient");
        $Provider = filter_input(INPUT_POST, "Provider");
        $MobilePhone = filter_input(INPUT_POST, "MobilePhone");

        if ($Patient == null or $Patient == "") {
            throw new Exception("No Patient specified in the post parameters.");
        }
        if ($Provider == null or $Provider == "") {
            throw new Exception("No Provider specified in the post parameters.");
        }
        if ($MobilePhone == null or $MobilePhone == "") {
            throw new Exception("No Mobile Phone number specified in the post parameters.");
        }

        $practitioner = $db->getPractitioner($org->OrgID, $Provider, 'FullName');
        if ($practitioner->OrgID == null) {
            $db->create_default_practitioner($org->OrgID, $Provider);
            $practitioner = $db->getPractitioner($org->OrgID, $Provider, 'FullName');
        }

        $pin = $practitioner->OrgID . "." . $practitioner->PractitionerID;
        $lateness = $db->getSingleLateness($pin);
        if ($lateness == "On time") {
            throw new Exception($practitioner->PractitionerName . " was on time (or would be reported as on time)");
        }
        
        howlate_util::register($pin,$MobilePhone);
        $db->enqueueNotification($practitioner, $Patient, $MobilePhone, $lateness);

        $msg = "Success.  Notification put in queue for " . $MobilePhone;
        $db->trlog(TranType::QUE_NOTIF, $msg, $org->OrgID, null, $practitioner->PractitionerID, null);
        return $msg;
    }

}

?>