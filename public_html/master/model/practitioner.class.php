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
        
        public $NotificationThreshold;
        public $LateToNearest;
        public $LatenessOffset;
        
        public function logoURL() {
            return howlate_util::logoURL($this->Subdomain);
        }
        
}

?>
