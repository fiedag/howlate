<?php

/**
 * DEFUNCT
 * 
 * 
 * 
 */

class Notifier  {
    public $Appointments=array();   // examined appointments

    protected $Clinic;
    protected $TimeNow;
    protected $ExaminedApptBook;
    protected $Horizon = 7200; // 2 hours is the default
    
    function __construct(Clinic $Clinic, $TimeNow, $ExaminedApptBook) {
        
        $this->Clinic = $Clinic;
        $this->ExaminedApptBook = $ExaminedApptBook;
        $this->TimeNow = $TimeNow;
    }

    /*
     * Passed a Practitioner class object and an array of elements each being
     * array("Provider","Status","ApptType","ArrivalTime"=>,"AppointmentTime","ConsultationTime","ConsultPredicted","Duration",
     * "MobilePhone","ConsentSMS","Processing","Sequence")
     */
    public function processNotifications() {
        // reject appointments before time_now and after cutoff, and without valid mobile number
        // or where no consent is given
        $uniquePractitioners = array_unique(array_map(function ($i) { return $i['Provider']; }, $this->ExaminedApptBook));
        foreach($uniquePractitioners as $practitioner) {
            $appointments_of_practitioner = array_filter($this->ExaminedApptBook, function($item) use ($practitioner) {return $item['Provider'] == $practitioner;});
            try {
                $Practitioner = Practitioner::getInstance($this->Clinic->OrgID, $practitioner,'FullName');
                $this->processPractitioner($Practitioner, $appointments_of_practitioner);
                
            } catch(Exception $ex) {
                // nothing
            }
            $this->Appointments = array_merge($this->Appointments, $appointments_of_practitioner);
        }
        return $this;        
    }

    private function processPractitioner(Practitioner $Practitioner, &$appts) {
        $allowed = array_filter($appts, function($item) {
            return $this->onlyWhere($item);
        });
        foreach ($allowed as $key => $val) {
            $msg="";
            if (!isset($appts[$key]['SecondsLate'])) {
                continue;
            }
            try {
                $phone = new HowLate_Phone($appts[$key]['MobilePhone'], $this->Clinic);
                if(!$this->alreadySentToday($phone, $Practitioner)) {
                    $msg = $this->smsMessage($phone, $appts[$key]['SecondsLate'], $Practitioner);
                    Device::register($this->Clinic->OrgID, $Practitioner->PractitionerID, $phone->CanonicalMobile);
                    $this->enqueueMessage($msg, $phone, $Practitioner);
                }
            } catch (Exception $ex) {
                Logging::trlog(TranType::QUE_NOTIF, $ex->getMessage(), $Practitioner->OrgID, $this->Clinic->ClinicID, $Practitioner->PractitionerID, $appts[$key]['MobilePhone'], 0);
                continue;
            }
            $appts[$key]['Notified'] = $msg;
        }
    }

    private function smsMessage(HowLate_Phone $Phone, $seconds_late, Practitioner $Practitioner) {
        
        $url = "http://m." . __DOMAIN . "/late?xudid=" . $Phone->XUDID;
        $adj_late_str = HowLate_Time::inSeconds($seconds_late, $Practitioner)->toHrsMinutesAdjusted();

        if($adj_late_str == 'on time') {
            throw new Exception("Do not notify if on time");
        }
        $msg = "Your appointment with " . $Practitioner->PractitionerName . " will probably run about " . $adj_late_str . ". Latest updates here: " . $url;
        return $msg;
    }

    
    
    private function alreadySentToday(HowLate_Phone $MobilePhone, Practitioner $Practitioner) {
        $q = "SELECT COUNT(0) As AlreadyDone FROM notifqueue WHERE OrgID = '" . $this->Clinic->OrgID . "'" .
                " AND PractitionerID = '" . $Practitioner->PractitionerID . "'" . 
                " AND ClinicID = " . $this->Clinic->ClinicID . " AND MobilePhone = '$MobilePhone->CanonicalMobile' AND Created >= CURDATE()";
        $row = MainDb::getInstance()->query($q)->fetch_object();
        if ($row->AlreadyDone != "0") {
            return true;
        }
        
    }
    
    private function enqueueMessage($Message, HowLate_Phone $MobilePhone, Practitioner $Practitioner) {
        
        
        // this takes care of duplicates and suppression based on clinic.SuppressNotifications etc
        $q = "CALL sp_EnqueueNotification(?,?,?,?,?,0)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sisss', $this->Clinic->OrgID, $this->Clinic->ClinicID, $Practitioner->PractitionerID, $MobilePhone->CanonicalMobile, $Message);
        $stmt->execute() or trigger_error('# Query Error (' . $stmt->errno . ') ' . $stmt->error, E_USER_ERROR);
        
    }
    
    /*
     * a filtering function used in array_filter above
     * returns notification candidates where conditions are met
     * 
     */
    private function onlyWhere($candidate) {
        $cutoff = $this->TimeNow + $this->Horizon;

        return (
                $candidate['Processing'] == 'NOTARRIVED' &&
                $candidate['ConsultPredicted'] >= $this->TimeNow  && 
                $candidate['ConsultPredicted'] < $cutoff          &&
                $candidate['MobilePhone'] != ''                   &&
                $candidate['ConsentSMS'] == '1');
    }
    
//    // It must be an earlier appt with another doctor
//    private function found_earlier_appt($appt) {
//        $earlier = array_filter($this->ExaminedApptBook, 
//                function($val) use ($appt) {  
//                  return 
//                    ($val['MobilePhone'] == $appt['MobilePhone'] &&  // same mobile
//                     $val['Provider'] != $appt['Provider'] &&        // different doctor
//                     $val['AppointmentTime'] < $appt['AppointmentTime']);});  // earlier
//
//       return(count($earlier) > 0);
//    }
}

?>