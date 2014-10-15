<?php

class clickatell {

	private $user = "fiedag";
	private $password = "BJ1xmgNO";
	private $api_id = "195729";
	private $baseurl ="http://api.clickatell.com";

	
	public function httpSend($to, $message, $orgid) {
		$text = urlencode($message);
		// do authorisation call
		$authurl = "$this->baseurl/http/auth?user=$this->user&password=$this->password&api_id=$this->api_id";
		//echo $authurl . "<br>";
		$ret = file($authurl);
		$sess = explode(":",$ret[0]);
		if ($sess[0] == "OK") {
			$sess_id = trim($sess[1]); // remove any whitespace
			$sendurl = "$this->baseurl/http/sendmsg?session_id=$sess_id&from=how-late&to=$to&max_credits=3&concat=3&text=$text";
			//echo $sendurl . "<br>";
			$ret = file($sendurl);
			$send = explode(":",$ret[0]);

			if ($send[0] == "ID") {
                                logging::smslog($orgid, $this->api_id, $sess_id, $send[1], $message);  
                                
			} else {
				error_log("send message failed, " . print_r($ret));
			}
		} else {
			error_log("Authentication failure: ". $ret[0]);
		}
	}
	
}
	
?>