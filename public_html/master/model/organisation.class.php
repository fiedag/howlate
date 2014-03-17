<?php 


class organisation {
	public $OrgID;
	public $OrgName;
	public $OrgShortName;
	public $TaxID;
	public $Subdomain;
	public $FQDN;
	public $BillingRef;
	
	public $Clinics;  // array of Clinic objects
	public $Practitioners;  // array of Practitioner objects
	public $Users;

	public function getby($field = 'OrgID', $fieldval) {
		$db = new howlate_db();
		$res = $db->getOrganisation($field, $fieldval);
		foreach($res as $key => $val) {
			$this->$key = $val;
		
		}
		$res = $db->getallclinics($this->OrgID);
		foreach($res as $key => $val) {
			$c = new clinic($val);
			$this->Clinics[] = $c;
		}
		
		$res = $db->getallpractitioners($field, $fieldval);
		
		foreach($res as $key => $val) {
			$p = new practitioner($val);
			$this->Practitioners[] = $p;
		}
		
		return $this;
	}

}



?>