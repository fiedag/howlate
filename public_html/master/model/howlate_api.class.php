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
                
                $db->trlog(TranType::LATE_UPD, 'Practitioner ' . $practitioner->PractitionerName . ' is now ' . $newlate . ' minutes late', $org->OrgID, null, $practitioner->PractitionerID, null);
                return "Lateness for $providername has been updated to $newlate minutes";
            } catch (Exception $ex) {
                $db->trlog(TranType::LATE_UPD, 'Practitioner ' . $practitioner . ' lateness update failed, exception =' . $ex, $org->OrgID, null, $null, $null);
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

}

?>