<?php

class clickatell {

	private $user = "fiedag";
	private $password = "94Erx86f";
	private $api_id = "655502";
	private $baseurl ="http://api.clickatell.com";

	public function httpSend($to, $message) {
		$text = urlencode($message);
		// do authorisation call
		$authurl = "$this->baseurl/http/auth?user=$this->user&password=$this->password&api_id=$this->api_id";
		echo $authurl . "<br>";
		$ret = file($authurl);
		$sess = explode(":",$ret[0]);
		if ($sess[0] == "OK") {
			$sess_id = trim($sess[1]); // remove any whitespace
			$sendurl = "$this->baseurl/http/sendmsg?session_id=$sess_id&to=$to&text=$text";
			echo $sendurl . "<br>";
			$ret = file($sendurl);
			$send = explode(":",$ret[0]);

			if ($send[0] == "ID") {
				echo "success message ID: ". $send[1];
			} else {
				die("send message failed");
			}
		} else {
			die("Authentication failure: ". $ret[0]);
		}
	}
	
}
	
?>