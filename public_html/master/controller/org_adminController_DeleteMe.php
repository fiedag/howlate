<?php 
Class org_adminController Extends baseController {

	public function index() 
	{
		$this->view();
	}
	
	public function view(){
		// retrieve the clinics for this organisation
		// and for each clinic, the practitioners assigned
		
		$org = new organisation();
		$org->getby( __SUBDOMAIN, 'Subdomain');

		$this->registry->template->org = $org;
		$this->registry->template->show('org_admin_view');
	}
	

	public function invite() {
		if (!isset($_GET["invitepin"]) or !isset($_GET["udid"])) {
			
			return;
		}
		$pin = $_GET["invitepin"];
		$udid = $_GET["udid"];
		
		howlate_util::validatePin($pin);
	
		$org = howlate_util::orgFromPin($pin);
		$id = howlate_util::idFromPin($pin);
		
		$db = new howlate_db();
		$db->validatePin($org, $id);
		$db->register($udid,$org, $id);
		$db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'registered pin ' . $pin, $org, null, $id, $udid);

		$prac = $db->getPractitioner($org, $id);

		$message = 'To receive lateness updates for ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
		$message .= ', click : ';
		$message .= "http://$prac->FQDN/late/view&udid=$udid";

		howlate_sms::httpSend($org, $udid, $message);
		
		$this->view();
	}
}
?>