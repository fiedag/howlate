<?php


Class HowLate_Phone {
    
    public $MobilePhone;
    public $CanonicalMobile;
    public $XUDID;
    
    public function __construct($MobilePhone, Clinic $Clinic = null, $encrypted = false) {
        if (!$encrypted) {
            $this->MobilePhone = $MobilePhone;
            $this->Clinic = $Clinic;
            $this->CanonicalMobile = $this->toCanonical($MobilePhone, $Clinic);
            $this->XUDID = $this->to_xudid($this->CanonicalMobile);
        }
        else {
            $this->XUDID = $MobilePhone;
            $this->CanonicalMobile = $this->to_udid($MobilePhone);
            $this->MobilePhone = $this->CanonicalMobile;
        }
    }

    
    private function toCanonical($MobilePhone, $Clinic) {
        if($Clinic == null) {
            $Country = 'Australia';
        }
        else {
            $Country = $Clinic->Country;
        }
        switch($Country) {
            case "US":
                $cc = '1';
                break;
            case "Australia":
                $cc = '61';
                break;
            default:
                $cc = '61';
                break;
        }
        $MobilePhone = trim($MobilePhone);
        $MobilePhone = preg_replace("/[^0-9]/", "", $MobilePhone);
        if(strlen($MobilePhone) < 9) {
            throw new Exception("Not Enough Digits in the mobile number");
        }
        switch(strlen($MobilePhone)) {
            case 9:
                $result = $cc . $MobilePhone;
                break;
            case 10:
                $result = $cc . substr($MobilePhone,1,9);  // replace zero
                break;
            default:
                $result = $MobilePhone;
                break;
        }
        return $result;
    }
    
    public function to_xudid($udid) {
        return strrev(base_convert($udid,10,26));
    }
    public function to_udid($xudid) {
        return base_convert(strrev($xudid),26,10);
    }
}
