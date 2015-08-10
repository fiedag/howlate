<?php
/*
 * This class is already environment agnostic. 
 * It should not rely on anything web-related like POST variables etc.
 * 
 * every method shoulr return the results of the model method called
 * 
 * 
 */

class Api {
    
    public static function updateLateness($OrgID, $Clinic, $NewLate, $PractitionerName, $ConsultationTime) {
        $pract = Practitioner::getInstance($OrgID,$PractitionerName,'FullName');
        if(!$pract)
        {
            $pract = Practitioner::createDefaultPractitioner($OrgID, $Clinic, $PractitionerName);
        }
        if ($NewLate < 0) {
            $NewLate = 0;
        }
        $sticky = 0;
        $manual = 0;
        return $pract->updateLateness($NewLate, $sticky, $manual);
    }

    public static function updateSessions($OrgID, $Clinic, $PractitionerName,  $Day, $StartTime, $EndTime) {
        $pract = Practitioner::getInstance($OrgID,$PractitionerName,'FullName');
        if(!$pract)
        {
            $pract = Practitioner::createDefaultPractitioner($OrgID, $Clinic, $PractitionerName);
        }
        Practitioner::updateSessions($OrgID, $Clinic, $pract->PractitionerID, $Day, $StartTime, $EndTime);
        return "Session Updated for $PractitionerName";
    }
    
    public static function updateApptTypes($OrgID, $ClinicID, $TypeCode, $TypeDescr) {
        Clinic::getInstance($OrgID, $ClinicID)->updateApptTypes($TypeCode, $TypeDescr);
    }
    public static function updateApptStatus($OrgID, $ClinicID, $StatusCode, $StatusDescr) {
        Clinic::getInstance($OrgID, $ClinicID)->updateApptStatus($StatusCode, $StatusDescr);
    }
    
    
    public static function areCredentialsValid($OrgID, $UserID, $PasswordHash) {
        return (Organisation::isValidPassword($OrgID, $UserID, $PasswordHash));
    }
    
    
    public static function notify($OrgID, $Clinic, $PractitionerName, $MobilePhone, $ClinicID, $Domain = 'how-late.com') {
        if(!$pract = Practitioner::getInstance($OrgID,$PractitionerName, 'FullName'))
        {
            Logging::trlog(TranType::QUE_NOTIF, "api class enqueue notification, creating default practitioner: $PractitionerName", $OrgID);
            $pract = Practitioner::createDefaultPractitioner($OrgID, $Clinic, $PractitionerName);
        }
        $result = $pract->enqueueNotification($MobilePhone, $Domain);
        Logging::trlog(TranType::QUE_NOTIF, "Enqueue result= $result", $OrgID, $ClinicID, $pract->PractitionerID, $MobilePhone);
        return $result;
    }
    
    
    public static function agent_version_target($OrgID, $ClinicID) {
        $target = Clinic::getInstance($OrgID, $ClinicID)->getAgentVersionTarget();
        return $target; 
    }
    
    public static function agent_version($orgID) {
        $version = file_get_contents('downloads/x64/version', true);
        return $version;
    }

    /*
     * 
     * Permits the HowLateAgentUpdater to download the target HowLateAgent.exe
     */
    public static function get_exe($OrgID, $ClinicID, $Version = null) {
        $agent = agent::getInstance($OrgID, $ClinicID);
        $agent->get_exe();
    }
    
    // Only used in versions 2.5.6.5 and earlier
    public static function notifybulk($OrgID, $ClinicID, $Provider, $AppointmentTime, $ConsultationTime, $AppointmentLength, $notify_array) {
        $pract = Practitioner::getInstance($OrgID, $Provider, "FullName");
        if (!isset($pract->ClinicID)) {
            // only notify if practitioner is assigned to a clinic
            return;
        }
        Notification::notify_bulk($pract, $AppointmentTime, $ConsultationTime, $AppointmentLength, $notify_array);
    }

    /* Version 3.1.1.1 + only
     * Process appointments, updating lateness and notifying SMS recipients
     * appt_bulk contains a special array with elements as shown:
     *   array('Provider' =>, 'ConsultationTime' => , 'AppointmentTime' => , 'Duration' => , 'Status' =>, 'ConsultPredicted' =>)
     */
    public static function processAppointments($OrgID, $ClinicID, $appt_bulk, $time_now) {
        // an array of practitioners to iterate through
        $uniquePractitioners = array_unique(array_map(function ($i) { return $i['Provider']; }, $appt_bulk));

        $notifier = new Notifier($OrgID, $ClinicID, $appt_bulk, $time_now);
        
        // appt_ret will contain the returned array after processing and ready for notifications
        $appt_ret = [];
        foreach($uniquePractitioners as $pract) {
            // $appts is the array of appointments for this practitioner $pract
            $appts = array_filter($appt_bulk, function($item) use ($pract) { return $item['Provider'] == $pract; });
            
            $p = Practitioner::getInstance2($OrgID, $pract, 'FullName');
            if(!$p) {
                $p = Practitioner::createDefaultPractitioner($OrgID, $ClinicID, $pract);
                continue;
            }
            if(!$p->ClinicID) {
                // practitioner is not assigned to a clinic, create but then skip
                // in practice the SelectAppointments SQL statement should not return any appts
                // for unassigned practitioners.  though it could happen because of a delay.
                continue; 
            }
            
            // populate the apptbook class with the appt array
            $p->setAppointmentBook($appts, $time_now);
            // here the apptbook array elements get their 'LatePredicted' and 'ConsultPredicted' fields calculated
            $p->predictConsultTimes($time_now);

            $actual_lateness_seconds = $p->getActualLateness();
            // Updates the lateness in the lates table
            $p->LatenessUpdate($actual_lateness_seconds);
            
            
            $notifier->processNotifications($p, $p->AppointmentBook->Predicted);
            
            // $summary is populated for diagnostic and unit test purposes
            $summary[] = array(
                "Time Now" => $time_now,
                "Practitioner" => $p->PractitionerName, 
                "Actual Late" => $actual_lateness_seconds, 
                "Original" => $appts, 
                "Predicted" => $p->AppointmentBook->Predicted,
                "Notified" => $notifier->notified_candidates);
        }

        // $ret is for diagnostics and unit testing
        $ret = array(
            "Date" => date('Y-m-d'),
            "OrgID" => $OrgID, 
            "ClinicID" => $ClinicID, 
            "appt_bulk" => $appt_bulk,
            "Summary"=> $summary);
        
        Clinic::getInstance($OrgID, $ClinicID)->apptLogging($ret);
        
        return "Appointments processed ok";
    }
   
    /*
     *  the providerlist is a string like "('Dr A Demo','Dr Anthony Alvano')"
     *  which can be used in the WHERE clause of a SQL statement to return
     *  just the appointments for the assigned doctors of a particular clinic
     */
    public static function providerList($OrgID, $ClinicID) {
        $clinic = Clinic::getInstance($OrgID, $ClinicID);
        
        $pract = $clinic->getPlacedPractitioners();
        $list = implode(",",$pract);
        return "(" . $list . ")";
    }
   
}
