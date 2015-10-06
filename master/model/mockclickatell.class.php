<?php

class MockClickatell {

	private $user = "fiedag";
	private $password = "BJ1xmgNO";
	private $api_id = "195729";
	private $baseurl ="http://api.clickatell.com";

	
	public function httpSend($to, $message, $orgid) {
            
            $mailer = new Howlate_Mailer('how-late.com','alex@how-late.com','d5yJHg7EPd');
            $mailer->send('alex@how-late.com', 'Alex @ How-Late', "Mock Clickatell message to $to", $message, 'noreply@how-late.com', 'Mock Clickatell');
	}
	
}
	
?>