<?php

/**
 * Description of Notifier
 * How it is used.  Caller instantiates a notifier object
 * passing it the entire appointment book for a clinic for a day.
 * Caller then repeatedly passes a practitioner object
 * and predictions.
 * @author Alex
 */
class Notifier {

    protected $entire_appt_book;   // entire appt book for a day

    protected $OrgID;
    protected $ClinicID;
    protected $TimeNow;
    protected $Horizon = 7200; // 2 hours is the default
    
    public $notified_candidates;
    
    function __construct($OrgID, $ClinicID, $entire_appt_book, $TimeNow) {
        $this->OrgID = $OrgID;
        $this->ClinicID = $ClinicID;
        $this->entire_appt_book = $entire_appt_book;
        $this->TimeNow = $TimeNow;
        
        foreach ($this->entire_appt_book as $key => $row) {
            $appt_time[$key]  = $row['ConsultPredicted'];  // for sorting only
        }        
        if(count($this->entire_appt_book) > 0) {
            array_multisort($appt_time, SORT_ASC, $this->entire_appt_book); 
        }
    }

    /*
     * Passed a Practitioner class object and an array of elements each being
     * array("Provider","Status","ApptType","ArrivalTime"=>,"AppointmentTime","ConsultationTime","ConsultPredicted","Duration",
     * "MobilePhone","ConsentSMS","Processing","Sequence")
     */
    public function processNotifications($Practitioner, $Predicted) {
        // reject appointments before time_now and after cutoff, and without valid mobile number
        // or where no consent is given
        
        $final_candidates = array_filter($Predicted, function($val) { return $this->onlyWhere($val); });
        
        if(!$final_candidates) 
            return;

        foreach($final_candidates as $key => $val) {
            // accommodates e.g. nursing appointments before the main appt
            if($this->found_earlier_appt($val)) {
                continue;
            }
            
            $late_predicted = ($val['ConsultPredicted'] - $val['AppointmentTime']) ;  // seconds
            if($late_predicted <= 0) {
                break;  // no more once we have caught up due to gaps etc.
            }
            
            $late_predicted_adj_str = $Practitioner->getLatenessMsg($late_predicted / 60);
            
            if(strpos($late_predicted_adj_str,'on time') === false) {
                $Practitioner->enqueueAdjustedMessage($this->ClinicID, $val['MobilePhone'], $late_predicted_adj_str);
                $val["LatePredicted"] = $late_predicted;
                $val["Message"] = $late_predicted_adj_str;
                $this->notified_candidates[] = $val;
            }
        } 
        return $this;
    }
    
    /*
     * a filtering function used in array_filter above
     * returns notification candidates where conditions are met
     * 
     */
    private function onlyWhere($candidate) {
        $cutoff = $this->TimeNow + $this->Horizon;
        $ignore_list = array('Arrived','ARRIVED','Waiting','In with doctor');

        return (
                !in_array($candidate['Status'],$ignore_list) &&
                $candidate['ConsultPredicted'] >= $this->TimeNow  && 
                $candidate['ConsultPredicted'] < $cutoff          &&
                $candidate['MobilePhone'] != ''                   &&
                $candidate['ConsentSMS'] == 1);
    }
    
    // It must be an earlier appt with another doctor
    private function found_earlier_appt($appt) {
        $earlier = array_filter($this->entire_appt_book, 
                function($val) use ($appt) {  
                  return 
                    ($val['MobilePhone'] == $appt['MobilePhone'] &&  // same mobile
                     $val['Provider'] != $appt['Provider'] &&        // different doctor
                     $val['AppointmentTime'] < $appt['AppointmentTime']);});  // earlier

       return(count($earlier) > 0);
    }
}

?>