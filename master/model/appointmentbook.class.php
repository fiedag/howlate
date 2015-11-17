<?php

Class AppointmentBook {

    /* The Appointments array must have the following mandatory 
     * columns:
     * 'AppointmentTime',
     * 'Duration',
     * 'ConsultationTime',
     * 'ArrivalTime',
     * 'ApptStatus',
     * 'ApptType',
     * 
     */
    
    public $Appointments;  // array of appointments
    
    protected $Clinic; // Clinic object
    protected $Practitioner;
    
    protected $time_now; // seconds since midnight

    protected $IgnoredStatuses;
    protected $IgnoredTypes;
    protected $AutoConsultationStatuses;
    protected $AutoConsultationTypes;
    
    protected $WithDoctor;
    protected $Waiting;
    protected $Ignore;
    protected $Future;
    protected $Relevant;
    
    
    function __construct(Practitioner $Practitioner, Clinic $Clinic, $time_now, $appt_array) {
        $this->Practitioner = $Practitioner;
        $this->Clinic = $Clinic;
        $this->time_now = $time_now;
        $this->Appointments = $appt_array;
        
        $this->AutoConsultationStatuses = $this->getAutoConsultStatuses();
        $this->AutoConsultationTypes = $this->getAutoConsultTypes();
        $this->IgnoredStatuses = $this->getIgnoredStatuses();
        $this->IgnoredTypes = $this->getIgnoredTypes();
        
    }
    
    public function examineAppointments() {
        return $this->classifyAppointments()->sequenceAppointments()->predictConsultTimes();
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
        foreach($this->Appointments as $key => $val) {
            $this->Appointments[$key]['Processing'] = "";
            $this->Appointments[$key]['ConsultPredicted'] = "";
            $this->Appointments[$key]['SecondsLate'] = "";
            
            if(in_array($this->Appointments[$key]['ApptType'],$this->IgnoredTypes)
            || in_array($this->Appointments[$key]['ApptStatus'],$this->IgnoredStatuses)) {
                $this->Appointments[$key]['Processing'] = "IGNORE";
                continue;
            }
            if($this->isAutoConsult($this->Appointments[$key])) {
                if($this->time_now >= $this->Appointments[$key]['AppointmentTime']) {
                    $this->Appointments[$key]['Processing'] = "DONE";
                } else {
                    $this->Appointments[$key]['Processing'] = "AUTOCONSULT";
                }
                continue;
            }
            
            if($this->Appointments[$key]['ConsultationTime']) {  // consultation has begun
                if($this->Appointments[$key]['ConsultationTime'] + $this->Appointments[$key]['Duration'] < $this->time_now ) {
                    $this->Appointments[$key]['Processing'] = "DONE";
                }
                else {
                    $this->Appointments[$key]['Processing'] = "WITHDOCTOR";
                }
            }
            elseif($this->Appointments[$key]['ArrivalTime']) {
                $this->Appointments[$key]['Processing'] = "WAITING";
            }
            else {
                $this->Appointments[$key]['Processing'] = "NOTARRIVED";
            }
        }
        return $this;
    }
    
    /*
     * Calculate the likely order in 
     * which the appointments will
     * most likely occur
     */
    private function sequenceAppointments() {
        $sequence=0;

        $this->Ignore = array_filter($this->Appointments, function($val) { return ($val['Processing'] == 'IGNORE' || $val['Processing'] == 'DONE'); });
        foreach($this->Ignore as $key=>$val) {
            $this->Appointments[$key]['Sequence'] = -1;
        }
        $sequence=0;
        
        $this->WithDoctor = array_filter($this->Appointments, function($val) { return ($val['Processing'] == 'WITHDOCTOR'); });
        $this->WithDoctor = HowLate_Util::array_sort($this->WithDoctor, 'ConsultationTime', SORT_ASC);
        foreach($this->WithDoctor as $key=>$val) {
            $this->Appointments[$key]['Sequence'] = $sequence++;
        }
        
        $this->Waiting = array_filter($this->Appointments, function($val) { return ($val['Processing'] == 'WAITING'); });
        $this->Waiting = HowLate_Util::array_sort($this->Waiting, 'ArrivalTime', SORT_ASC);
        
        foreach($this->Waiting as $key=>$val) {
            $this->Appointments[$key]['Sequence'] = $sequence++;
        }
        
        $this->Future = array_filter($this->Appointments, function($val) { return ($val['Processing'] == 'NOTARRIVED' || $val['Processing'] == 'AUTOCONSULT'); });
        $this->Future = HowLate_Util::array_sort($this->Future, 'AppointmentTime', SORT_ASC);

        foreach($this->Future as $key=>$val) {
            $this->Appointments[$key]['Sequence'] = $sequence++;
        }
        return $this;
    }
    
    
    /*
     * having just determined the likely order
     * of appointments, it remains to 
     * predict an appointment start time for each
     * 
     * If there are >1 WITHDOCTOR, then only treat them as 
     * concurrent if they have the same AppointmentTime
     * else assume that only the last one is WITHDOCTOR
     * and the others merely happened very quickly
     */
    private function predictConsultTimes() {
        $i=0;
        $this->Relevant = array_filter($this->Appointments, function($val) { return ($val['Sequence'] >= 0); });
        $this->Relevant = HowLate_Util::array_sort($this->Relevant, 'Sequence', SORT_ASC);

        $prev_consult_end = 0;
        // has to be either WITHDOCTOR, WAITING, AUTOCONSULT or NOTARRIVED
        foreach($this->Relevant as $key=>$val) {

            $processing = $this->Appointments[$key]['Processing'];
            
            switch($processing) {
                case "WITHDOCTOR":
                    $this->Appointments[$key]['ConsultPredicted'] = $this->Appointments[$key]['ConsultationTime'];
                    break;
                case "WAITING":
                    $this->Appointments[$key]['ConsultPredicted'] = max($prev_consult_end, $this->Appointments[$key]['ArrivalTime']);
                    break;
                case "AUTOCONSULT":
                case "NOTARRIVED":
                    $this->Appointments[$key]['ConsultPredicted'] = max($this->time_now, $prev_consult_end, $this->Appointments[$key]['AppointmentTime']);
                    break;
                default:
                    $this->Appointments[$key]['ConsultPredicted'] = -99999;
                    break;
            }
            $prev_consult_end = $this->Appointments[$key]['ConsultPredicted'] + $this->Appointments[$key]['Duration'];
            $this->Appointments[$key]['SecondsLate'] = ($this->Appointments[$key]['ConsultPredicted'] - $this->Appointments[$key]['AppointmentTime']);
        }
        return $this;
    }   
    
    
    /*  We are going to completely ignore any appt types where the appointment is available to catch up
    */
    private function getIgnoredTypes() {
        $q = "SELECT TypeDescr FROM appttype WHERE OrgID = '" . $this->Clinic->OrgID . "' AND ClinicID = " . $this->Clinic->ClinicID . " AND CatchUp = 1";
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
        $q = "SELECT StatusDescr FROM apptstatus WHERE OrgID = '" . $this->Clinic->OrgID . "' AND ClinicID = " . $this->Clinic->ClinicID . " AND CatchUp = 1";
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
        $q = "SELECT TypeDescr FROM appttype WHERE OrgID = '" . $this->Clinic->OrgID . "' AND ClinicID = " . $this->Clinic->ClinicID . " AND AutoConsultation = 1";
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
        $q = "SELECT StatusDescr FROM apptstatus WHERE OrgID = '" . $this->Clinic->OrgID . "' AND ClinicID = " . $this->Clinic->ClinicID . " AND AutoConsultation = 1";
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
        $auto_consult_status = in_array($appt['ApptStatus'],$this->AutoConsultationStatuses);
        $auto_consult_type = in_array($appt['ApptType'],$this->AutoConsultationTypes);
        return ($auto_consult_status || $auto_consult_type);
    }
}
