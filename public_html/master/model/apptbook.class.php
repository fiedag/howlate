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
    public $CurrentLate = 0;
    protected $Appointments;  // array of appointment objects as defined below
    protected $Predicted;
    protected $OrgID;  // 
    protected $ClinicID;
    protected $IgnoredStatuses;
    protected $IgnoredTypes;
    protected $AutoConsultationStatuses;
    protected $AutoConsultationTypes;
    protected $WithDoctor;
    protected $Waiting;
    
    public static function getInstance($OrgID, $ClinicID, $TimeNow, $appt_array) {
        self::$instance = new self($OrgID, $ClinicID, $TimeNow, $appt_array);
        return self::$instance;
    }
    
    function __construct($OrgID, $ClinicID, $TimeNow, $appt_array) {
        $this->OrgID = $OrgID;
        $this->ClinicID = $ClinicID;
        $this->time_now = $TimeNow;
        foreach ($appt_array as $key => $row) {
            $appt_time[$key]  = $row['AppointmentTime'];
        }
        $this->Appointments = $appt_array;
        array_multisort($appt_time, SORT_ASC, $this->Appointments ); 
        
        $this->AutoConsultationStatuses = $this->getAutoConsultStatus();
        $this->AutoConsultationTypes = $this->getAutoConsultTypes();
        $this->IgnoredStatuses = $this->getIgnoredStatus();
        $this->IgnoredTypes = $this->getIgnoredTypes();
        
    }
    
    public function handle_exception($exception) {
        
        throw new Exception("Exception handler apptbook.class.php: " . $exception->getMessage());
    }
    
    /*
     *  We are going to completely ignore any appt types where the appointment is available to catch up
     */
    private function getIgnoredTypes() {
        $q = "SELECT TypeDescr FROM appttype WHERE OrgID = '" . $this->OrgID . "' AND ClinicID = " . $this->ClinicID . " AND CatchUp = 1";
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
    /*
     * We are going to ignore any appt status where the appointment is available to catch up
     */
    private function getIgnoredStatus() {
        $q = "SELECT StatusDescr FROM apptstatus WHERE OrgID = '" . $this->OrgID . "' AND ClinicID = " . $this->ClinicID . " AND CatchUp = 1";
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
     *  Auto-consult appointments will be deemed to have occurred on time once
     *  Time_Now exceeds AppointmentTime + Duration
     */
    private function getAutoConsultTypes() {
        $q = "SELECT TypeDescr FROM appttype WHERE OrgID = '" . $this->OrgID . "' AND ClinicID = " . $this->ClinicID . " AND AutoConsultation = 1";
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
    /*
     *  Auto-consult appointments will be deemed to have occurred on time once
     *  Time_Now exceeds AppointmentTime + Duration
     */
    private function getAutoConsultStatus() {
        $q = "SELECT StatusDescr FROM apptstatus WHERE OrgID = '" . $this->OrgID . "' AND ClinicID = " . $this->ClinicID . " AND AutoConsultation = 1";
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

    
    private function isAutoConsult($val) {
        $auto_consult_status = in_array($val['Status'],$this->AutoConsultationStatuses);
        $auto_consult_type = in_array($val['ApptType'],$this->AutoConsultationTypes);
        return ($auto_consult_status || $auto_consult_type);
    }
    
    public function traverseAppointments2() {
        // first pass checks for status and type and completed
        $this->firstPass();
        $this->sequenceFutureAppointments();
        $this->predict();
        
    }

    
    private function firstPass() {
        // classify
        foreach($this->Appointments as $key => $val) {
            $this->Appointments[$key]['Processing'] = "";
            if($this->Appointments[$key]['ConsultationTime']) {
                if($this->Appointments[$key]['ConsultationTime'] + $this->Appointments[$key]['Duration'] < $this->time_now) {
                    $this->Appointments[$key]['Processing'] .= ((!$this->Appointments[$key]['Processing'])?"":",") . "DONE";
                }
                else {
                    $this->Appointments[$key]['Processing'] .= ((!$this->Appointments[$key]['Processing'])?"":",") . "WITHDOCTOR";
                }
            }
            elseif($this->isAutoConsult($this->Appointments[$key]) && $this->Appointments[$key]['AppointmentTime'] < $this->time_now) {
                $this->Appointments[$key]['Processing'] .= ((!$this->Appointments[$key]['Processing'])?"":",") . "AUTOCONSULTED";
            }
            elseif($this->Appointments[$key]['ArrivalTime']) {
                $this->Appointments[$key]['Processing'] .= ((!$this->Appointments[$key]['Processing'])?"":",") . "WAITING";
            }
            
            if(in_array($this->Appointments[$key]['ApptType'],$this->IgnoredTypes) ||
                    in_array($this->Appointments[$key]['Status'],$this->IgnoredStatuses)) {
                $this->Appointments[$key]['Processing'] .= ((!$this->Appointments[$key]['Processing'])?"":",") . "IGNORE";
            }
        }
        
    }
    
    private function sequenceFutureAppointments() {
        $sequence = 0;
        foreach ($this->Appointments as $key => $val) {
            // appointments in the past
            // if they are auto-consult then do not consider them
            if (strpos($this->Appointments[$key]['Processing'], 'AUTOCONSULTED') !== false) {
                $this->Appointments[$key]['Sequence'] = $sequence;
            }
            // if they are done then do not consider them
            if (strpos($this->Appointments[$key]['Processing'], 'DONE') !== false) {
                $this->Appointments[$key]['Sequence'] = $sequence;
            }
        }

        $sequence = 1;
        
        $this->WithDoctor = array_filter($this->Appointments, function($val) { return ($val['Processing'] == 'WITHDOCTOR'); });
        if (count($this->WithDoctor) > 0) {
            foreach($this->Appointments as $key=>$val) {
                if(strpos($this->Appointments[$key]['Processing'],'WITHDOCTOR') !== false) {
                    $this->Appointments[$key]['Sequence'] = $sequence;
                }
            }
        }
        $sequence++;
        $this->Waiting = array_filter($this->Appointments, function($val) { return ($val['Processing'] == 'WAITING'); });
        if (count($this->Waiting) > 0) {
            foreach($this->Appointments as $key=>$val) {
                if($this->Appointments[$key]['Processing'] == 'WAITING') {
                    $this->Appointments[$key]['Sequence'] = $sequence++;
                }
            }
        }
        
        // final pass
        foreach($this->Appointments as $key=>$val) {
                if($this->Appointments[$key]['Processing'] == '' 
                        || $this->Appointments[$key]['Processing'] == "AUTOCONSULT") {
                    $this->Appointments[$key]['Sequence'] = $sequence++;
                }  
        }
    }
    
    private function predict() {
        $this->Predicted = array_filter($this->Appointments, function($val) { return $val['Sequence'] != '0'; });
 
        if ($this->Predicted) {
            foreach ($this->Predicted as $key => $row) {
                $seq[$key] = $row['Sequence'];
            }
            array_multisort($seq, SORT_ASC, $this->Predicted);
        }
        

        $consult_end = -1;
        foreach($this->Predicted as $key=>$val) {
            if($this->Predicted[$key]['Processing'] == "WITHDOCTOR") {
                $this->Predicted[$key]['ConsultPredicted'] = $this->Predicted[$key]['ConsultationTime'];
                $consult_end = $this->Predicted[$key]['ConsultPredicted'] + $this->Predicted[$key]['Duration'];
                $this->CurrentLate = max(0, $this->Predicted[$key]['ConsultPredicted'] - $this->Predicted[$key]['AppointmentTime']);
                continue;
            }
            
            // these are waiting or future appts
            if($consult_end == -1) {
                // none are/were with doctor
                if(!$this->Predicted[$key]['ConsultationTime']) {
                    $this->Predicted[$key]['ConsultPredicted'] = max($this->Predicted[$key]['AppointmentTime'],$this->time_now);
                }
                else {
                    $this->Predicted[$key]['ConsultPredicted'] = $this->Predicted[$key]['ConsultationTime'];
                }
            
                $consult_end = $this->Predicted[$key]['ConsultPredicted'] + $this->Predicted[$key]['Duration'];
                
                $this->CurrentLate = max(0, $this->Predicted[$key]['ConsultPredicted'] - $this->Predicted[$key]['AppointmentTime']);
                continue;
            }
            
            $this->Predicted[$key]['ConsultPredicted'] = max($consult_end,$this->Predicted[$key]['AppointmentTime']);
            $consult_end = $this->Predicted[$key]['ConsultPredicted'] + $this->Predicted[$key]['Duration'];
        }
        
    }
    
}
