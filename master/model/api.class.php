<?php
/*
 * This class is already environment agnostic. 
 * It should not rely on anything web-related like POST variables etc.
 * 
 * every method shoulr return an array
 * 
 * 
 */

class Api {
    protected $OrgID;
    protected $ClinicID;

    protected static $instance;
    
    public static function getInstance($OrgID, $ClinicID) {
        self::$instance = new self();
        self::$instance->OrgID = $OrgID;
        self::$instance->ClinicID = $ClinicID;
        return self::$instance;
    }

    public function updateLateness($NewLate, $PractitionerName, $ConsultationTime = 0) {
        $pract = Practitioner::getInstance($this->OrgID, $PractitionerName, 'FullName');
        if(!$pract)
        {
            $pract = Practitioner::createDefaultPractitioner($this->OrgID, $this->ClinicID, $PractitionerName);
        }
        if ($NewLate < 0) {
            $NewLate = 0;
        }
        $sticky = 0;
        $manual = 0;
        
        return $pract->updateLateness($NewLate, $sticky, $manual);
    }

    public function getLateness($PractitionerName) {
        $pract = Practitioner::getInstance($this->OrgID, $PractitionerName, 'FullName');
        $curlate = $pract->getCurrentLateness();
        
    }
    
    public function updateSessions($PractitionerName,  $Day, $StartTime, $EndTime) {
        $pract = Practitioner::getInstance($this->OrgID,$PractitionerName,'FullName');
        if(!$pract)
        {
            $pract = Practitioner::createDefaultPractitioner($this->OrgID, $this->ClinicID, $PractitionerName);
        }
        $pract->updateSessions( $Day, $StartTime, $EndTime);
        return "Session Updated for $PractitionerName";
    }
    
    public function updateApptTypes($OrgID, $ClinicID, $TypeCode, $TypeDescr) {
        $clin = Clinic::getInstance($OrgID, $ClinicID);
        $clin->updateApptTypes($TypeCode, $TypeDescr);
    }
    public function updateApptStatus($OrgID, $ClinicID, $StatusCode, $StatusDescr) {
        Clinic::getInstance($OrgID, $ClinicID)->updateApptStatus($StatusCode, $StatusDescr);
    }
    
    public function areCredentialsValid($OrgID, $UserID, $PasswordHash) {
        return (Organisation::isValidPassword($OrgID, $UserID, $PasswordHash));
    }
    
    
    public function notify($OrgID, $Clinic, $PractitionerName, $MobilePhone, $ClinicID, $Domain = 'how-late.com') {
        if(!$pract = Practitioner::getInstance($OrgID,$PractitionerName, 'FullName'))
        {
            Logging::trlog(TranType::QUE_NOTIF, "api class enqueue notification, creating default practitioner: $PractitionerName", $OrgID);
            $pract = Practitioner::createDefaultPractitioner($OrgID, $Clinic, $PractitionerName);
        }
        $result = $pract->enqueueNotification($MobilePhone, $Domain);
        Logging::trlog(TranType::QUE_NOTIF, "Enqueue result= $result", $OrgID, $ClinicID, $pract->PractitionerID, $MobilePhone);
        return $result;
    }
    
    
    public function agent_version_target($OrgID, $ClinicID) {
        $target = Clinic::getInstance($OrgID, $ClinicID)->getAgentVersionTarget();
        return $target; 
    }
    
    public function agent_version($orgID) {
        $version = file_get_contents('downloads/x64/version', true);
        return $version;
    }

    /*
     * 
     * Permits the HowLateAgentUpdater to download the target HowLateAgent.exe
     */
    public function get_exe($OrgID, $ClinicID, $Version = null) {
        $agent = agent::getInstance($OrgID, $ClinicID);
        $agent->get_exe();
    }
    
    // Only used in versions 2.5.6.5 and earlier
    public function notifybulk($OrgID, $ClinicID, $Provider, $AppointmentTime, $ConsultationTime, $AppointmentLength, $notify_array) {
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
    public function processAppointments($OrgID, $ClinicID, $appt_bulk, $time_now) {
        // an array of practitioners to iterate through
        $uniquePractitioners = array_unique(array_map(function ($i) { return $i['Provider']; }, $appt_bulk));

        $notifier = new Notifier($OrgID, $ClinicID, $appt_bulk, $time_now);
        
        // appt_ret will contain the returned array after processing and ready for notifications
        $appt_ret = [];
        foreach($uniquePractitioners as $pract) {
            // $appts is the array of appointments for this practitioner $pract
            $appts = array_filter($appt_bulk, function($item) use ($pract) { return $item['Provider'] == $pract; });
            
            $p = Practitioner::getInstance($OrgID, $pract, 'FullName');
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
    public function getProviderList() {
        $clinic = Clinic::getInstance($this->OrgID, $this->ClinicID);
        
        $pract = $clinic->getPlacedPractitioners();
        $list = implode(",",$pract);
        return "(" . $list . ")";
    }
   
}
