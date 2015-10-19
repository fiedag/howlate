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
    
    
    public $Appointments;  // array of appointment objects as defined below
    public $Predicted;

    
    protected $Notified;
    protected $OrgID;  // 
    protected $ClinicID;
    protected $IgnoredStatuses;
    protected $IgnoredTypes;
    protected $AutoConsultationStatuses;
    protected $AutoConsultationTypes;
    protected $WithDoctor;
    protected $Waiting;
    
    public static function getInstance(Practitioner $Practitioner, $TimeNow, $appt_array) {
        self::$instance = new self($Practitioner, $TimeNow, $appt_array);
        return self::$instance;
    }
    
    function __construct(Practitioner $Practitioner, $TimeNow, $appt_array) {
        $this->OrgID = $Practitioner->OrgID;
        $this->ClinicID = $Practitioner->ClinicID;
        $this->time_now = $TimeNow;
        foreach ($appt_array as $key => $row) {
            $appt_time[$key]  = $row['AppointmentTime'];
        }
        $this->Appointments = $appt_array;
        //array_multisort($appt_time, SORT_ASC, $this->Appointments ); 
        
        $this->AutoConsultationStatuses = $this->getAutoConsultStatuses();
        $this->AutoConsultationTypes = $this->getAutoConsultTypes();
        $this->IgnoredStatuses = $this->getIgnoredStatuses();
        $this->IgnoredTypes = $this->getIgnoredTypes();
        
    }
    
    public function handle_exception($exception) {
        
        throw new Exception("Exception handler apptbook.class.php: " . $exception->getMessage());
    }


    /*
     * 
     * PUBLIC FUnction traverses the appointment book for a single practitioner
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     */
//    public function traverseAppointments() {
//        // first pass checks for status and type and completed
//        $this->classifyAppointments();
//        $this->sequenceAppointments();
//        $this->predict();
//    }

    

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
    private function getIgnoredStatuses() {
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
    private function getAutoConsultStatuses() {
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

    
    private function isAutoConsult($appt) {
        $auto_consult_status = in_array($appt['Status'],$this->AutoConsultationStatuses);
        $auto_consult_type = in_array($appt['ApptType'],$this->AutoConsultationTypes);
        return ($auto_consult_status || $auto_consult_type);
    }

    
    /*
     * Classify the appointments
     * thereby marking any which are 
     * - with doctor
     * - waiting
     * - done
     * - to be ignored
     * - or treated as auto-consult appointments
     * 
     */
    private function classifyAppointments() {
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
    
  
    /*
     * Calculate the likely order in 
     * which the appointments will
     * most likely occur
     */
    private function sequenceAppointments() {
        foreach ($this->Appointments as $key => $val) {
            $this->Appointments[$key]['Sequence'] = -1;
            // appointments in the past
            // if they are auto-consult then do not consider them
            if (strpos($this->Appointments[$key]['Processing'], 'AUTOCONSULTED') !== false) {
                $this->Appointments[$key]['Sequence'] = 0;
            }
            // if they are done then do not consider them
            if (strpos($this->Appointments[$key]['Processing'], 'DONE') !== false) {
                $this->Appointments[$key]['Sequence'] = 0;
            }
        }

        $sequence=1;
        $this->WithDoctor = array_filter($this->Appointments, function($val) { return ($val['Processing'] == 'WITHDOCTOR'); });
        if (count($this->WithDoctor) > 0) {
            foreach($this->Appointments as $key=>$val) {
                if(strpos($this->Appointments[$key]['Processing'],'WITHDOCTOR') !== false) {
                    $this->Appointments[$key]['Sequence'] = $sequence;
                }
            }
        }
        $sequence=2;
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
                        || $this->Appointments[$key]['Processing'] == "AUTOCONSULT" 
                        || $this->Appointments[$key]['Processing'] == "IGNORE" ) {
                    $this->Appointments[$key]['Sequence'] = $sequence++;
                }  
        }
    }
    
    
    /*
     * having just determined the likely order
     * of appointments, it remains to 
     * predict an appointment start time
     * 
     * 
     */
    private function predict() {
        $this->Predicted = array_filter($this->Appointments, function($val) { return $val['Sequence'] > 0; });
 
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
            
            if($this->Predicted[$key]['Processing'] == "IGNORE") {
                $duration = 0;
            }
            else {
                $duration =  $this->Predicted[$key]['Duration'];
            }
                 
            
            // these are IGNORE, WAITING or future appts
            if($consult_end == -1) {
                // none are/were with doctor
                if(!$this->Predicted[$key]['ConsultationTime']) {
                    $this->Predicted[$key]['ConsultPredicted'] = max($this->Predicted[$key]['AppointmentTime'],$this->time_now);
                }
                else {
                    $this->Predicted[$key]['ConsultPredicted'] = $this->Predicted[$key]['ConsultationTime'];
                }
            
                $consult_end = $this->Predicted[$key]['ConsultPredicted'] + $duration;
                
                $this->CurrentLate = max(0, $this->Predicted[$key]['ConsultPredicted'] - $this->Predicted[$key]['AppointmentTime']);
                continue;
            }
            
            $this->Predicted[$key]['ConsultPredicted'] = max($consult_end,$this->Predicted[$key]['AppointmentTime']);
            $consult_end = $this->Predicted[$key]['ConsultPredicted'] + $duration;
        }
        
    }   
}
?>