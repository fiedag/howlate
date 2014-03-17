<?php 


class practitioner extends howlate_basetable {
	public $OrgID;
	public $ID;
	public $PractitionerID;
	public $Pin;
	public $PractitionerName;
	public $ClinicName;
	public $OrgName;
	public $FQDN;

	
	public function __construct($arr) {
		parent::__construct($arr);
		
		$this->Pin = $this->OrgID . '.' . $this->ID;
	}


}



?>
