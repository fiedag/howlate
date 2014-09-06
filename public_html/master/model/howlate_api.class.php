<?php

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
    public static function notify() 
    {
        $credentials = filter_input(INPUT_POST, "credentials");
        if ($credentials == null) {
            return "Fatal Error: Credentials not supplied.";
        }      
        list($userid, $passwordhash) = explode(".", $credentials);
        $db = new howlate_db();

        $org = new organisation();
        $org->getby(__SUBDOMAIN, "Subdomain");
        if ($db->isValidPassword($org->OrgID, $userid, $passwordhash)) {
            try {
                $Patient = filter_input(INPUT_POST, "Patient");
                $AppointmentDate = filter_input(INPUT_POST, "AppointmentDate");
                $AppointmentTime = filter_input(INPUT_POST, "AppointmentTime");
                $Provider = filter_input(INPUT_POST, "Provider");
                $MobilePhone = filter_input(INPUT_POST, "MobilePhone");
                
                if ($Patient == null) {
                    return "Fatal Error: No Patient specified in the post parameters.";
                }
                if ($AppointmentDate == null) {
                    return "Fatal Error: No Appointment Date specified in the post parameters.";
                }
                if ($AppointmentTime == null) {
                    return "Fatal Error: No Appointment Time specified in the post parameters.";
                }
                if ($Provider == null) {
                    return "Fatal Error: No Provider specified in the post parameters.";
                }
                if ($MobilePhone == null) {
                    return "Fatal Error: No Mobile Phone number specified in the post parameters.";
                }

                $practitioner = $db->getPractitioner($org->OrgID, $Provider, 'FullName');
                if ($practitioner->OrgID == null) {
                    $db->create_default_practitioner($org->OrgID, $Provider);
                    $practitioner = $db->getPractitioner($org->OrgID, $Provider, 'FullName');
                }

                $db->enqueueNotification($practitioner,$Patient,$AppointmentDate,$AppointmentTime,$MobilePhone);
                
                $msg = "Success.  Notification put in queue for " . $MobilePhone ;
                $db->trlog(TranType::QUE_NOTIF, $msg, $org->OrgID, null, $practitioner->PractitionerID, null);
                return $msg;
            } catch (Exception $ex) {
                $db->trlog(TranType::QUE_NOTIF, 'Practitioner ' . $practitioner->PractitionerID . ' failed to queue notification, exception=' . $ex, $org->OrgID, null, $null, $null);
                return $ex;
            }
        } else {
            return "Fatal Error: Invalid credentials.";
        }
        
        
    }
    
 /*
 Post of entire datarow to https://margateclinic.how-late.com/api?ver=post&clin=31&met=notif

 [credentials] -> alexf.9cbf8a4dcb8e30682b927f352d6559a0
 [Patient] -> Ricardo Richard
 [InternalID] -> 2
 [AppointmentDate] -> 3/09/2014 12:00:00 AM
 [AppointmentTime] -> 36000
 [Provider] -> Dr Anthony Albanese
 [MobilePhone] -> 0403569377     
 and received 
<br />
<b>Fatal error</b>:  Call to undefined method howlate_api::notify() in <b>/home/howlate/public_html/master/controller/apiController.php</b> on line <b>47</b><br />

  * 
  * 
  */   
    
    
    
    
}

?>