<?php

class HowLate_SMS {
    
    public static function httpSend($orgid, $udid, $message, $clinicid = null, $practitionerid = null) {
       $clickatell = HowLate_Util::clickatell();

       $clickatell->httpSend( $udid, $message, $orgid);
       Logging::trlog(TranType::DEV_SMS, $message, $orgid, $clinicid, $practitionerid, $udid);
    }
}
?>