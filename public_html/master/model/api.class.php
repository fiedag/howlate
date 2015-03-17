<?php
/*
 * This class is already environment agnostic. 
 * It should not rely on anything web-related like POST variables etc.
 * 
 * every method shoulr return the results of the model method called
 * 
 * 
 */


class api {
    public static function updateLateness($OrgID, $NewLate, $PractitionerName, $ConsultationTime) {

        $pract = practitioner::getInstance($OrgID,$PractitionerName,'FullName');
        if(!$pract)
        {
            $pract = practitioner::createDefaultPractitioner($OrgID,$PractitionerName);
        }
        if ($NewLate < 0) {
            $NewLate = 0;
        }
        
        return practitioner::updateLateness($OrgID,$pract->PractitionerID, $NewLate, $ConsultationTime, 0);
    }

    public static function updateSessions($OrgID, $PractitionerName, $Day, $StartTime, $EndTime) {

        $pract = practitioner::getInstance($OrgID,$PractitionerName,'FullName');
        if(!$pract)
        {
            $pract = practitioner::createDefaultPractitioner($OrgID,$PractitionerName);
        }
        practitioner::updateSessions($OrgID, $pract->PractitionerID, $Day, $StartTime, $EndTime);
        return "Session Updated for $PractitionerName";
    }
    
    public static function areCredentialsValid($OrgID, $UserID, $PasswordHash) {
        return (organisation::isValidPassword($OrgID, $UserID, $PasswordHash));
    }
    
    
    public static function notify($OrgID, $PractitionerName, $MobilePhone, $ClinicID, $Domain = 'how-late.com') {
        if(!$pract = practitioner::getInstance($OrgID,$PractitionerName, 'FullName'))
        {
            logging::trlog(TranType::QUE_NOTIF, "api class enqueue notification, creating default practitioner: $PractitionerName", $OrgID);
            $pract = practitioner::createDefaultPractitioner($OrgID,$PractitionerName);
        }
        $result = $pract->enqueueNotification($MobilePhone, $Domain);
        logging::trlog(TranType::QUE_NOTIF, "Enqueue result= $result", $OrgID, $ClinicID, $pract->PractitionerID, $MobilePhone);
        return $result;
    }
    
    
    public static function agent_version($orgID) {
        $version = file_get_contents('downloads/x64/version', true);
        return $version;
    }

    public static function get_exe2($OrgID, $ClinicID) {
        $result = clinic::getInstance($OrgID, $ClinicID)->getClinicIntegration();
        if ($result->Agent32Bit == true) {
            $platform = "x86";
        } else {
            $platform = "x64";
        }
        echo $platform;
    }
    
    // TODO: Theoretically this method violates the 
    // convention of everything being environment agnostic (see header)
    public static function get_exe($OrgID, $ClinicID) {
        $result = clinic::getInstance($OrgID, $ClinicID)->getClinicIntegration();
        if ($result->Agent32Bit == true) {
            $platform = "x86";
        } else {
            $platform = "x64";
        }
        $file = "downloads/$platform/HowLateAgent.exe";

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            trigger_error("File $file does not exist.", E_USER_ERROR);
        }
    }
    
    public static function notifybulk($OrgID, $ClinicID, $notif_array) {
        notification::notify_bulk($OrgID, $ClinicID, $notif_array);
    }
        


}
