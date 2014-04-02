<?php 

class practitioner extends howlate_basetable {
	public $OrgID;
	public $ID;
        public $FullName;
        public $AbbrevName;
	public $ClinicPlaced;
	public $ClinicName;
        public $Subdomain;
	public $Pin;

        public $PractitionerID;
        public $PractitionerName;
        public $OrgName;
        public $FQDN;
        
        
        
	public function __construct($arr) {
		parent::__construct($arr);
		
		$this->Pin = $this->OrgID . '.' . $this->ID;
	}
}

?>
