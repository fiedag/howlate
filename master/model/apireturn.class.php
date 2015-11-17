<?php

/*
 * 
 * No need for the $response to also be json encoded
 * 
 */

class APIReturn {
    
    public static function ok($message, $response=true) {
        $arr = array("code"=>200,"status"=>"OK","message"=>$message,"response"=>$response);
        //header('Content-type: application/json');
        return json_encode($arr);
        
    }

    public static function created($message, $response=true) {
        $arr = array("code"=>201,"status"=>"Created","message"=>$message,"response"=>$response);
        //header('Content-type: application/json');
        return json_encode($arr);
        
    }
    public static function accepted($message, $response=true) {
        $arr = array("code"=>202,"status"=>"Accepted","message"=>$message,"response"=>$response);
        //header('Content-type: application/json');
        return json_encode($arr);
        
    }
    public static function error($message, $response=true) {
        $arr = array("code"=>400,"status"=>"Error","message"=>$message,"response"=>$response);
        //header('Content-type: application/json');
        return json_encode($arr);
        
    }
    public static function unauthorized($message, $response=true) {
        $arr = array("code"=>401,"status"=>"Unauthorized","message"=>$message,"response"=>$response);
        //header('Content-type: application/json');
        return json_encode($arr);
        
    }
    public static function notfound($message, $response=false) {
        $arr = array("code"=>404,"status"=>"Not found","message"=>$message,"response"=>$response);
        //header('Content-type: application/json');
        return json_encode($arr);
        
    }
    public static function internal($message, $response=false) {
        $arr = array("code"=>500,"status"=>"Internal Server Error","message"=>$message,"response"=>$response);
        //header('Content-type: application/json');
        return json_encode($arr);
        
    }

    
}
