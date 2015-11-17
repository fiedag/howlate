<?php

/*
 * Description of apiexception
 *
 * @author Alex
 * 
 * This exception is passed an array instead of a message.
 * The array is then json_encoded and then treated like 
 * normal exception message
 * 
 */
class APIException extends Exception {
    
    public $Content;
    
    public function __construct($message, $code = 400, $status = 'Error', $response=false) {
        $this->arr = array("code"=>$code,"status"=>$status,"message"=>$message,"response"=>$response);
        
        echo json_encode($this->arr);
        exit;
    }
    
    
}
