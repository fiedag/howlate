<?php

/*
 * 
 * No need for the $response to also be json encoded
 * 
 */

class APIReturn {
    
    public static function ok($message, $code = 0, $status = 'OK', $response=true) {
        $arr = array("code"=>$code,"status"=>$status,"message"=>$message,"response"=>$response);
        header('Content-type: application/json');
        echo json_encode($arr);
        exit;
    }
    
}