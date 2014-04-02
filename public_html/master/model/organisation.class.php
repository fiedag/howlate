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
    public $LogoURL;  // relative to master 

    public function getby($fieldval, $fieldname) {
        $db = new howlate_db();

        $org = $db->getOrganisation($fieldval, $fieldname);
        foreach ($org as $key => $val) {
            $this->$key = $val;
        }

        $clin = $db->getallclinics($this->OrgID, 'OrgID');
        foreach ($clin as $key => $val) {
            $c = new clinic($val);
            $this->Clinics[] = $c;
        }

        $prac = $db->getallpractitioners($this->OrgID, 'OrgID');
        foreach ($prac as $key => $val) {
            $p = new practitioner($val);
            $this->Practitioners[] = $p;
        }

        $users = $db->getallusers($this->OrgID, 'OrgID');
        foreach ($users as $key => $val) {
            $u = new howlate_user($val);
            $this->Users[] = $u;
        }
        
        $this->LogoURL = "/pri/$this->Subdomain/logo.png";
        return $this;
    }
    
    
    public function isValidPassword($userid, $password) {
        $db = new howlate_db();
        return $db->isValidPassword($this->OrgID, $userid, $password);
    }

    public function getLatenesses($clinic) {
        $db = new howlate_db();
        return $db->getlatenessesByClinic($this->OrgID, $clinic);
        
    }
    
}

?>