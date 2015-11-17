<?php

/**
 * DEFUNCT
 * 
 * 
 * 
 */
class Notifier {

    public $Appointments = array();   // examined appointments
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
        $uniquePractitioners = array_unique(array_map(function ($i) {
                    return $i['Provider'];
                }, $this->ExaminedApptBook));
        foreach ($uniquePractitioners as $practitioner) {
            $appointments_of_practitioner = array_filter($this->ExaminedApptBook, function($item) use ($practitioner) {
                return $item['Provider'] == $practitioner;
            });
            try {
                $Practitioner = Practitioner::getInstance($this->Clinic->OrgID, $practitioner, 'FullName');
                $this->processPractitioner($Practitioner, $appointments_of_practitioner);
            } catch (Exception $ex) {
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
            $phone = new HowLate_Phone($appts[$key]['MobilePhone'], $this->Clinic);
            $msg = "";
            if (!isset($appts[$key]['SecondsLate'])) {
                $appts[$key]['Notified'] = 'SecondsLate not set';
                continue;
            }
            if (!($appts[$key]['ConsentSMS'])) {
                $appts[$key]['Notified'] = 'Consent Not given';
                continue;
            }
            if ($appts[$key]['MobilePhone'] == "") {
                $appts[$key]['Notified'] = 'No number';
                continue;
            }
            if ($appts[$key]['ConsultPredicted'] < $this->TimeNow) {
                $appts[$key]['Notified'] = 'Consult is in the past';
                continue;
            }
            if ($appts[$key]['ConsultPredicted'] > $this->TimeNow + $this->Horizon) {
                $appts[$key]['Notified'] = 'Consult is beyond horizon';
                continue;
            }

            try {
                $time = HowLate_Time::fromSeconds($appts[$key]['SecondsLate'], $Practitioner);
                Device::register($this->Clinic->OrgID, $Practitioner->PractitionerID, $phone->CanonicalMobile);
                Device::updatePerspective($Practitioner, $time, $phone->CanonicalMobile);
                $msg = $this->smsText($phone, $time, $Practitioner);
                if(!$this->isAlreadySentToday($phone, $this->Clinic, $Practitioner->PractitionerID)) {
                    $this->enqueueMessage($msg, $phone, $Practitioner);
                }
            } catch (Exception $ex) {
                Logging::trlog(TranType::QUE_NOTIF, $ex->getMessage(), $Practitioner->OrgID, $this->Clinic->ClinicID, $Practitioner->PractitionerID, $appts[$key]['MobilePhone'], 0);
                continue;
            }
            $appts[$key]['Notified'] = $msg;
        }
    }

    private function smsText(HowLate_Phone $Phone, HowLate_Time $time, Practitioner $Practitioner) {

        $url = "http://m." . __DOMAIN . "/late?xudid=" . $Phone->XUDID;
        $adj_late_str = $time->toHrsMinutesAdjusted();

        if ($adj_late_str == 'on time') {
            throw new Exception("Do not notify if on time");
        }
        $msg = "Your appointment with " . $Practitioner->PractitionerName . " will probably run about " . $adj_late_str . ". Latest updates here: " . $url;
        return $msg;
    }

    private function enqueueMessage($Message, HowLate_Phone $MobilePhone, Practitioner $Practitioner) {
        $status = 'Queued';

        $q = "INSERT INTO notifqueue (OrgID, ClinicID, PractitionerID, MobilePhone, Message, Status)" .
                " VALUES (?,?,?,?,?,?)";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('ssssss', $this->Clinic->OrgID, $this->Clinic->ClinicID, $Practitioner->PractitionerID, $MobilePhone->CanonicalMobile, $Message, $status);
        $stmt->execute() or die($stmt->error);
    }

    /*
     * a filtering function used in array_filter above
     * returns notification candidates where conditions are met
     * 
     */

    private function onlyWhere($candidate) {
        $cutoff = $this->TimeNow + $this->Horizon;

        return (
                $candidate['Processing'] == 'NOTARRIVED'
                //&&
                //$candidate['ConsultPredicted'] >= $this->TimeNow  && 
                //$candidate['ConsultPredicted'] < $cutoff          &&
                //$candidate['MobilePhone'] != ''                   &&
                //$candidate['ConsentSMS'] == '1'
                );
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


    function isAlreadySentToday(HowLate_Phone $MobilePhone, Clinic $Clinic, $PractitionerID) {
        $q = "SELECT COUNT(0) As AlreadyDone FROM notifqueue" .
                " WHERE OrgID = '" . $Clinic->OrgID . "'" .
                " AND PractitionerID = '" . $PractitionerID . "'" .
                " AND ClinicID = " . $Clinic->ClinicID .
                " AND MobilePhone = '$MobilePhone->CanonicalMobile'" .
                " AND Status = 'Sent'" .
                " AND Created >= DATE_SUB(NOW(), INTERVAL 12 HOUR) ";
        $stmt = db::getInstance()->query($q);
        $result = $stmt->fetchObject();

        if ($result->AlreadyDone > 0) {
            return true;
        }
        return false;
    }

}

?>