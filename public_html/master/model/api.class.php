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
    public static function updateLateness($OrgID, $NewLate, $PractitionerName) {
        $pract = practitioner::getInstance($OrgID,$PractitionerName,'FullName');
        if(!$pract)
        {
            $pract = practitioner::createDefaultPractitioner($OrgID,$PractitionerName);
        }
        if ($NewLate < 0) {
            $NewLate = 0;
        }
        return practitioner::updateLateness($OrgID,$pract->PractitionerID,$NewLate, 0);
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
    
    
    public static function notify($OrgID, $PractitionerName, $MobilePhone, $Domain = 'how-late.com') {
        
        logging::trlog(TranType::MISC_MISC,"API:notify for practitioner=$PractitionerName, notify = $MobilePhone");
        if(!$pract = practitioner::getInstance($OrgID,$PractitionerName, 'FullName'))
        {
            $pract = practitioner::createDefaultPractitioner($OrgID,$PractitionerName);
        }
        return $pract->enqueueNotification($MobilePhone, $Domain);
        
    }
    
}
