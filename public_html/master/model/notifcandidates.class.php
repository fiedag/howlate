<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of notifcandidates
 *
 * @author Alex
 */
class NotifCandidates {

    public $candidates;
    public $final_candidates;
    public $notified_candidates;

    protected static $instance;
    protected $OrgID;
    protected $ClinicID;
    protected $TimeNow;
    protected $Horizon = 7200; // 2 hours
    
    
    public static function getInstance($OrgID, $ClinicID, $appt_array, $TimeNow) {
        self::$instance = new self($OrgID, $ClinicID, $appt_array, $TimeNow);
        return self::$instance;
    }
    
    function __construct($OrgID, $ClinicID, $appt_array, $TimeNow) {
        $this->OrgID = $OrgID;
        $this->ClinicID = $ClinicID;
        $this->TimeNow = $TimeNow;
        foreach ($appt_array as $key => $row) {
            $appt_time[$key]  = $row['ConsultPredicted'];
        }        
        $this->candidates = $appt_array;
        array_multisort($appt_time, SORT_ASC, $this->candidates ); 
    }
    
    public function processNotifications() {
        // reject appointments before time_now and after cutoff, and without valid mobile number
        // or where no consent is given
        
        $this->final_candidates = array_filter($this->candidates, function($val) { return $this->onlyWhere($val); });
        if(!$this->final_candidates) 
            return;
        
        // sort these final candidates by Provider by ConsultPredicted
        foreach ($this->final_candidates as $key => $row) {
            $provider[$key]  = $row['Provider'];
            $consult_predicted[$key] = $row['ConsultPredicted'];
        }        
        array_multisort($provider, SORT_ASC, $consult_predicted, SORT_ASC, $this->final_candidates ); 
        
        $prev_Provider = "";
        $this->notified_candidates = [];

        foreach($this->final_candidates as $key => $val) {
            if($val['Provider'] != $prev_Provider) {
                $practitioner = Practitioner::getInstance($this->OrgID, $val['Provider'],'FullName');
                $prev_Provider = $val['Provider'];
            }

            // accommodates e.g. nursing appointments before the main appt
            if($this->found_earlier_appt($val)) {
                continue;
            }
            if($practitioner->ClinicID != $this->ClinicID) {
                // this practitioner is not assigned to this clinc in HOW-LATE
                continue;
            }
            
            $late_predicted = $val['LatePredicted'] / 60;
            // $late_predicted is in minutes
            
            $late_predicted_adj_str = $practitioner->getLatenessMsg($late_predicted);
            $practitioner->enqueueAdjustedMessage($this->ClinicID, $val['MobilePhone'], $late_predicted_adj_str);
            
            $this->notified_candidates[] = $val;
        } 
                
        return $this;
    }
    
    private function onlyWhere($candidate) {
        $cutoff = $this->TimeNow + $this->Horizon;
        $ignore_list = array('Arrived','ARRIVED','Waiting','In with doctor');

        return (
                !in_array($candidate['Status'],$ignore_list) &&
                $candidate['ConsultPredicted'] >= $this->TimeNow  && 
                $candidate['ConsultPredicted'] < $cutoff          &&
                $candidate['MobilePhone'] != ''                   &&
                $candidate['LatePredicted'] > 0                   &&
                $candidate['ConsentSMS'] == 1);
    }
    
    // It must be an earlier appt with another doctor
    private function found_earlier_appt($appt) {
        $earlier = array_filter($this->candidates, 
                function($val) use ($appt) {  
                  return 
                    ($val['MobilePhone'] == $appt['MobilePhone'] &&  // same mobile
                     $val['Provider'] != $appt['Provider'] &&        // different doctor
                     $val['AppointmentTime'] < $appt['AppointmentTime']);});  // earlier

       return(count($earlier) > 0);
    }
}

?>