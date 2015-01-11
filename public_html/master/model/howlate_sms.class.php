<?php

class howlate_sms {
    
    public static function httpSend($orgid, $udid, $message, $clinicid = null, $practitionerid = null) {
       $clickatell = new clickatell();

       $clickatell->httpSend( $udid, $message, $orgid);
       logging::trlog(TranType::DEV_SMS, $message, $orgid, $clinicid, $practitionerid, $udid);
    }
}
?>