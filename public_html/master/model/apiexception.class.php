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
    
    public function __construct($arr) {
        $arr2 = array("Exception" => $arr);
        $this->message = json_encode($arr2);
        $this->arr = $arr;
        
        // make sure everything is assigned properly
        parent::__construct($this->message);
    }
    
}
