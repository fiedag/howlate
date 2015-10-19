<?php

class HowLate_Time {

    protected static $instance;

    private $minutes;
    private $Practitioner;
    
    public static function inMinutes($Minutes, $Practitioner = null) {
        self::$instance = new self();
        self::$instance->minutes = $Minutes;
        self::$instance->Practitioner = $Practitioner;
        
        return self::$instance;
    }
    public static function inSeconds($Seconds, $Practitioner = null) {
        self::$instance = new self();
        self::$instance->minutes = round($Seconds / 60, 0);
        self::$instance->Practitioner = $Practitioner;
        
        return self::$instance;
    }
    
    public function toHrsMinutesAdjusted() {
        if (self::$instance->Practitioner == null) {
            $adj_minutes = self::$instance->minutes;
        } else {
            $adj_minutes = self::adjust(self::$instance->Practitioner, self::$instance->minutes);
        }
        
        if($adj_minutes==0) {
            return 'on time';
        } else {
            return $this->toHrsMinutes($adj_minutes);
        }
    }
    

    private function toHrsMinutes($minutes = -1) {
        if ($minutes == -1) {
            $minutes = self::$instance->minutes;
        }
        $result = "";
        $hours = floor($minutes / 60);
        $minutes = floor($minutes % 60);
        if ($hours == 0) {
            $result = "";
        } elseif ($hours == 1) {
            $result = "an hour ";
        } else {
            $result = "$hours hours ";
        }
        if ($minutes != 0) {
            $result .= $minutes . " minute";
            if($minutes > 1) {
                $result .= "s";
            }
        }
        return trim($result . " late");
    }

    
    private function adjust(Practitioner $Practitioner) {
        $minutes = self::$instance->minutes;
        if ($minutes <= 0 || $minutes < $Practitioner->NotificationThreshold) {
            $result = 0;
            return $result;
        }
        if($Practitioner->LatenessCeiling > 0 && $minutes > $Practitioner->LatenessCeiling) {
            $result = $Practitioner->LatenessCeiling;
            return $result;
        }
        $tonearest = max($Practitioner->LateToNearest,1);
        $rounded = $tonearest * round($minutes / $tonearest,0,PHP_ROUND_HALF_UP);
        $result = $rounded - $Practitioner->LatenessOffset;
        return $result;
    }
    
}
