<?php

class HowLate_SMS {
    
    protected $interface;
    
    function __construct($interface = null) {
        if(empty($interface)) {
            $this->interface = new Clickatell();
        }
        else {
            $this->interface = $interface;
        }
    }
    
    public function httpSend($orgid, $udid, $message, $clinicid = null, $practitionerid = null) {
       $this->interface->httpSend( $udid, $message, $orgid);
       Logging::trlog(TranType::DEV_SMS, $message, $orgid, $clinicid, $practitionerid, $udid);
    }
}
?>