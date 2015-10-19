<?php
class AppointmentBookExaminer {

    public $Appointments=array();

    private $Clinic;
    private $time_now;
    private $appt_list;
    
    function __construct(Clinic $Clinic, $time_now, &$appt_list) {
        $this->Clinic = $Clinic;
        $this->time_now = $time_now;
        $this->appt_list = $appt_list;
        
        
    }
    
    public function examineAll() {
        $uniquePractitioners = array_unique(array_map(function ($i) { return $i['Provider']; }, $this->appt_list));
        foreach($uniquePractitioners as $practitioner) {
            $appointments_of_practitioner = array_filter($this->appt_list, function($item) use ($practitioner) {return $item['Provider'] == $practitioner;});
            try {
                $Practitioner = Practitioner::getInstance($this->Clinic->OrgID, $practitioner,'FullName');
                $apptbook_of_practitioner = new AppointmentBook($Practitioner,$this->Clinic,$this->time_now, $appointments_of_practitioner);
                $appointments_of_practitioner = $apptbook_of_practitioner->examineAppointments()->Appointments;
                
            } catch(Exception $ex) {
                
            }
            $this->Appointments = array_merge($this->Appointments, $appointments_of_practitioner);
            $this->updateLates($practitioner, $apptbook_of_practitioner->Appointments);
        }
        return $this;        
    }
    
    
    /*
     * Takes the lateness as at the next unarrived patient 
     * and updates the practitioner record accordingly
     */
    private function updateLates($PractitionerName, $appt_book) {

        $LateMinutes = 0;
        $notarriveds = array_filter($appt_book, function($item) { return $item['Processing'] == 'NOTARRIVED';});
        $by_appt_time = HowLate_Util::array_sort($notarriveds, 'Sequence');
        
        foreach ($by_appt_time as $key => $val) {
            if ($appt_book[$key]['Processing'] == 'NOTARRIVED') {
                if (isset($appt_book[$key]['SecondsLate'])) {
                    if ($appt_book[$key]['SecondsLate'] < 0) {
                        continue;
                    }
                    $LateMinutes = round($appt_book[$key]['SecondsLate'] / 60, 0);
                    break;
                }
            }
        }
        $p = Practitioner::getInstance($this->Clinic->OrgID, $PractitionerName, 'FullName');
        $p->updateLateness($LateMinutes);
    }

}