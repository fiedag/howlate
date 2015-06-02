<?php

/**
 * Description of apptbook
 * This class contains the logic for predictive lateness calculation
 * when passed the array of appointments, it is able to calculate
 * the ConsultPredicted time and LatePredicted values for each
 * appointment.
 * 
 * All times here are in seconds since midnight
 * 
 * @author Alex
 */
class ApptBook {
    
    protected static $instance;
    
    public $time_now;
    public $Appointments;  // array of appointment objects as defined below
    
    protected $LatestConsultation_StartTime = -1;
    protected $LatestConsultation_FinishTime = -1;
    protected $LatestAppointmentTime = -1;

    protected $ActualLateness;

    protected $OrgID;  // 
    protected $ClinicID;
    
    public static function getInstance($OrgID, $ClinicID, $appt_array) {
        self::$instance = new self($OrgID, $ClinicID, $appt_array);
        return self::$instance;
    }
    
    function __construct($OrgID, $ClinicID, $appt_array) {
        $this->OrgID = $OrgID;
        $this->ClinicID = $ClinicID;
        foreach ($appt_array as $key => $row) {
            $appt_time[$key]  = $row['AppointmentTime'];
        }
        $this->Appointments = $appt_array;
        array_multisort($appt_time, SORT_ASC, $this->Appointments ); 
        
        $this->findLatestConsultationTime();
    }
    
    public function handle_exception($exception) {
        
        throw new Exception("Exception handler apptbook.class.php: " . $exception->getMessage());
    }    
    
    private function findLatestConsultationTime() {
        $consult_times = array_map(function($details) { return $details['ConsultationTime'];}, $this->Appointments);
        $this->LatestConsultation_StartTime = max($consult_times);

        $consult_fin_times = array_map(function($details) { return $details['ConsultationTime'] + $details['Duration'];}, $this->Appointments);
        $this->LatestConsultation_FinishTime = max($consult_fin_times);
    }


    /*
     * apptstatus table contains a list of 
     * status codes which are to be ignored
     * 
     * 
     */
    public function filterByApptType() {
        $ignore = $this->getIgnoredTypes();  // the array of appt types we ignore
        $this->Appointments = array_filter($this->Appointments, function($val) use($ignore) { return !in_array($val['ApptType'],$ignore); });
        return $this;
    }

    public function filterByApptStatus() {
        $ignore = $this->getIgnoredStatus();  // the array of status codes we ignore
        $this->Appointments = array_filter($this->Appointments, function($val) use($ignore) { return !in_array($val['Status'],$ignore); });
        return $this;
    }
    
    private function getIgnoredTypes() {
        $q = "SELECT TypeDescr FROM appttype WHERE OrgID = '" . $this->OrgID . "' AND ClinicID = " . $this->ClinicID . " AND IgnoreAppt = 1";
        $sql = MainDb::getInstance();

        $typeArray = array();
        if ($result = $sql->query($q)) {
            while ($row = $result->fetch_object()) {
                $typeArray[] = $row->TypeDescr;
            }
            return $typeArray;
        }
        return null;
    }
    
    private function getIgnoredStatus() {
        $q = "SELECT StatusDescr FROM apptstatus WHERE OrgID = '" . $this->OrgID . "' AND ClinicID = " . $this->ClinicID . " AND IgnoreAppt = 1";
        $sql = MainDb::getInstance();

        $statusArray = array();
        if ($result = $sql->query($q)) {
            while ($row = $result->fetch_object()) {
                $statusArray[] = $row->StatusDescr;
            }
            return $statusArray;
        }
        return null;
    }
    
    
    /*
     * A key method which goes through appointments for a practitioner
     * for a day and calculates the predicted consultation start time
     * and lateness for each appointment
     * Only the appts remaining after removal of 
     * ignored status and type codes are considered.
     */
    public function traverseAppointments($time_now) {

        $this->time_now = $time_now;
        $prev_start = $this->LatestConsultation_StartTime;
        $prev_finish = $this->LatestConsultation_FinishTime;
        $prev_duration = $prev_finish - $prev_start;

        // establish if any appt has been completed out of sequence
        $isGap = 0;
        $isOutOfOrder = 0;
        foreach($this->Appointments as $key => $val) {
            // incidentally also zero out the LatePredicted values
            // while we have the chance
            $this->Appointments[$key]['LatePredicted'] = 0;
            if($isGap && $val['ConsultationTime']) {
                $isOutOfOrder = 1;
            }
            if(!$val['ConsultationTime']) {
                $isGap = 1;
            }
        }
        
        // go through appts remaining after
        // ignoring so-marked status and type codes
        foreach($this->Appointments as $key => $val) {
            if ($this->Appointments[$key]['ConsultationTime']) {  // consult has happened
                $this->Appointments[$key]['ConsultPredicted'] = $this->Appointments[$key]['ConsultationTime'];
                if($isOutOfOrder) {
                    continue;
                }
                $prev_start = $this->Appointments[$key]['ConsultationTime'];
                
                $prev_duration = $this->Appointments[$key]['Duration'];
                $prev_finish = $prev_start + $prev_duration;
                continue;
            }
            if($this->Appointments[$key]['AppointmentTime'] < $this->LatestConsultation_FinishTime) {
                // a later appt has been consulted out of order
                $this->Appointments[$key]['ConsultPredicted'] = max($prev_finish, $this->time_now);
                $prev_start = $this->Appointments[$key]['ConsultPredicted'];
                $prev_duration = $this->Appointments[$key]['Duration'];
                $prev_finish = $prev_start + $prev_duration;
                continue;
            }
            else {
                $this->Appointments[$key]['ConsultPredicted'] = max($prev_start + $prev_duration, $this->time_now, $this->Appointments[$key]['AppointmentTime']);
            }
            $prev_start = $this->Appointments[$key]['ConsultPredicted'];
            $prev_duration = $this->Appointments[$key]['Duration'];
            $prev_finish = $prev_start + $prev_duration;
            
        }
        // now we have ConsultPredicted, traverse a final time to fill in LatePredicted
        foreach($this->Appointments as $key => $val) {
            $this->Appointments[$key]['LatePredicted'] = $this->Appointments[$key]['ConsultPredicted'] - $this->Appointments[$key]['AppointmentTime'];
        }
        return $this;
    }
    
    /*
     * @As At is a time of day expressed in seconds since midnight
     * Otherwise taken to be $this->time_now
     * 
     * We need the NEXT appt where the consult has not occurred, i.e. after now
     * Then see when is the earliest this appt can finish?  
     * If before time_now then we are on time
     * If after time_now then we are late by predicted finish - time_now
     */
    public function getLateness($AsAt = null) {
        $at = ($AsAt)?$AsAt:$this->time_now;
        $finishes  = $at;

        $late_seconds = 0;
        
        if (count($this->Appointments) == 0) {
            return $late_seconds;
        }
        $arr_temp = $this->Appointments;
        
        foreach ($arr_temp as $key => $row) {
            $appt_time[$key]  = $row['ConsultPredicted'];
        }
        // sort by ConsultPredicted
        array_multisort($appt_time, SORT_ASC, $arr_temp ); 

        foreach($arr_temp as $key => $val) {
            if($val['ConsultPredicted'] && $val['ConsultPredicted'] >= $at) {
                $late_seconds = $val['ConsultPredicted'] - $val['AppointmentTime'];
                break;
            }
        }
        return $late_seconds;
    }
}


?>