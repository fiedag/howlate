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
    
    public $arr;
    
    public function __construct($message, $code = 400, $status = 'Bad Request', $response=false) {
        $this->arr = array("code"=>$code,"status"=>$status,"message"=>$message,"response"=>$response);
        header('Content-type: application/json');
        echo json_encode($this->arr);
        exit;
    }
    
}
