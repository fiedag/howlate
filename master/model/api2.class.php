<?php
/*
 * This class is already environment agnostic. 
 * It should not rely on anything web-related like POST variables etc.
 * 
 * every method shoulr return the results of the model method called
 * 
 * 
 */

class Api2 {
    
    protected $notifier;
    
    function __construct(INotifier $n ) {
        $this->notifier = $n;
    }
    
    public function processAppointments($OrgID, $ClinicID, $appt_bulk, $time_now) {
        // an array of practitioners to iterate through
        $uniquePractitioners = array_unique(array_map(function ($i) { return $i['Provider']; }, $appt_bulk));

        //$notifier = new Notifier($OrgID, $ClinicID, $appt_bulk, $time_now);
        
        // appt_ret will contain the returned array after processing and ready for notifications
        $appt_ret = [];
        foreach($uniquePractitioners as $pract) {
            // $appts is the array of appointments for this practitioner $pract
            $appts = array_filter($appt_bulk, function($item) use ($pract) { return $item['Provider'] == $pract; });
            
            $p = Practitioner::getOrCreateInstance($OrgID, $ClinicID, $pract, 'FullName');

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
            
            
            $this->notifier->processNotifications($p, $p->AppointmentBook->Predicted);
            
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
   
}
