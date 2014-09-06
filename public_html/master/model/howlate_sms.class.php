<?php

class howlate_sms {
    
    public static function httpSend($from = '', $udid, $message) {
       $clickatell = new clickatell();

       $clickatell->httpSend($from, $udid, $message);
        
    }
    
}