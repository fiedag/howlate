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
    // this implements use-up logic so gaps are used to reduce lateness
    // $late is an array with fields 'Horizon','
    // Example of a notify_bulk record
    //[notify_bulk[1][Patient]] => B 0403569377 Anderson  
    //[notify_bulk[1][InternalID]] => 18  
    //[notify_bulk[1][AppointmentDate]] => 20/03/2015 12:00:00 AM  
    //[notify_bulk[1][AppointmentTime]] => 71100  
    //[notify_bulk[1][Provider]] => Dr Anthony Alvano  
    //[notify_bulk[1][MobilePhone]] => 0403569377      
    //[notify_bulk[1][Status]] => Booked                
    //[notify_bulk[1][AppointmentLength]] => 900  
    //[notify_bulk[1][ConsultationTime]] => 72950  
    
    public static function notify_bulk($Practitioner, $AppointmentTime, $ConsultationTime, $AppointmentLength, $notify_bulk) {
        if (count($notify_bulk)==0) 
            return;
        
        $late_seconds = $ConsultationTime - $AppointmentTime;
        // convert to seconds
        $threshold = $Practitioner->NotificationThreshold * 60;
        
        echo "notify_bulk: $Practitioner->PractitionerName is $late_seconds seconds late with a threshold of $threshold\r\n";
        
        if ($late_seconds < $threshold) {
            echo "$Practitioner->PractitionerName is $late_seconds seconds late which is below threshold of $threshold\r\n";
            return;
        }
        $in = $ConsultationTime;
        $out = $ConsultationTime + $AppointmentLength;

        $horizon = 7200;
        $notify_cutoff = $out + $horizon;

        
        // the late end is the end of the consultation which triggered the lateness 
        // based on the original appointment schedule, is anyone ready to go in?
        // if so then we cannot catch up
        foreach ($notify_bulk as $notification) {
            $in = $notification['AppointmentTime'];
            $duration = $notification['AppointmentLength'];
            $patient = $notification['Patient'];
            echo "Patient $patient is in at $in for a duration of $duration \r\n";
            if ($in > $notify_cutoff) {
                echo "$in is beyond horizon cutoff of $notify_cutoff , DONE ! \r\n";
                break;
            }
            if ($in > $out) {
                $gap = $in - $out;
                $late_seconds = $late_seconds - $gap;
                echo "This appointment starts at $in while the previous one ends at $out, lateness reduced by gap of $gap seconds to $late_seconds\r\n";
            }
            if ($late_seconds < $threshold) {
                echo "Lateness now below threshold of $threshold seconds, done! \r\n";
                return;
            }
            $out = $in + $duration;
            
            $MobilePhone = $notification['MobilePhone'];
            echo "Queuing notification for $patient to $MobilePhone \r\n";
            $Practitioner->enqueueNotification($MobilePhone);
            
        }
        
            
    }
    
    
    public static function notify() {
        
    }
    
    
    
    
}