<?php

class notification {
    
    public static function getMessage($practitioner, $lateness, $udid, $domain = 'how-late.com') {
        $clinic = clinic::getInstance($practitioner->OrgID, $practitioner->ClinicID);
        
        $url = "http://m." . $domain . "/late/view?udid=$udid";
        if($clinic->AllowMessage) {
            $url .= "&msg=1";
        }
        $msg = $practitioner->PractitionerName . " is running " . $lateness . ". For updates,click " . $url;
        return $msg;
        
    }

    // all durations are seconds (and times are seconds since midnight)
    public static function notify_bulk($OrgID, $ClinicID, $late, $notif_candidates) {
        $horizon = $late['Horizon'];
        $notify_cutoff = $late['ConsultationTime'] + $horizon;
        $appt_end = $late['ConsultationTime'] + $late['AppointmentLength'];
        // the late end is the end of the consultation which triggered the lateness 
        // based on the original appointment schedule, is anyone ready to go in?
        // if so then we cannot catch up
        foreach ($notif_array as $notification) {
            $next_in = $notification['AppointmentTime'];
            if ($next_in > $notify_cutoff) {
                break;
            }
            if ($next_in > $appt_end) {
                $gap = $next_in - $appt_end;
                $notify_cutoff = $notify_cutoff - $gap;
            }
            $appt_end = $notification['AppointmentTime'] + $notification['AppointmentLength'];
        }
    }
    
}