<?php

class organisation {

    public $OrgID;
    public $OrgName;
    public $OrgShortName;
    public $TaxID;
    public $Subdomain;
    public $FQDN;
    public $BillingRef;
    public $Address1;
    public $Address2;
    public $City;
    public $Zip;
    public $Country;
    public $Clinics;  // array of Clinic objects
    public $ActiveClinics;  // array of Active Clinic objects having placements
    public $Practitioners;  // array of Practitioner objects
    public $Users;
    public $LogoURL;  // relative to master 
    public $UpdIndic;
    private $columns;

    public function getby($fieldval, $fieldname) {
        $db = new howlate_db();
        $org = $db->getOrganisation($fieldval, $fieldname);

        if (isset($org)) {
            foreach ($db->getOrganisation($fieldval, $fieldname) as $key => $val) {
                $this->$key = $val;
            }

            $clin = $db->getallclinics($this->OrgID, 'OrgID');
            foreach ($clin as $key => $val) {
                $c = new clinic($val);
                $this->Clinics[] = $c;
            }

            $clin = $db->getactiveclinics($this->OrgID, 'OrgID');
            foreach ($clin as $key => $val) {
                $c = new clinic($val);
                $this->ActiveClinics[] = $c;
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

            if (file_exists("pri/$this->Subdomain/logo.png")) {
                $this->LogoURL = "/pri/$this->Subdomain/logo.png";
            } else {
                $this->LogoURL = "/pri/default.gif";
            }
        }

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

    public function gettimezones() {

        $db = new howlate_db();
        return $db->gettimezones($this->Country);
    }

    public function update() {
        // uses the current public attributes to update the record
    }

    // Pin is of form AAABB.A
    // udid is the unique device id
    public function register($pin, $udid) {

        //howlate_util::validatePin($pin);

        $org = howlate_util::orgFromPin($pin);
        $id = howlate_util::idFromPin($pin);

        $db = new howlate_db();
        //$db->validatePin($org, $id);
        $db->register($udid, $org, $id);
        $db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'registered pin ' . $pin, $org, null, $id, $udid);

        $prac = $db->getPractitioner($org, $id);

        $clickatell = new clickatell();

        $message = 'Click for lateness updates from ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
        $message .= ': ';
        $message .= "http://secure." . __DOMAIN . "/late/view&udid=$udid";
        $clickatell->httpSend(null, $udid, $message);
    }

    public function unregister($pin, $udid) {

        //howlate_util::validatePin($pin);

        $org = howlate_util::orgFromPin($pin);
        $id = howlate_util::idFromPin($pin);

        $db = new howlate_db();
        //$db->validatePin($org, $id);
        $db->unregister($udid, $org, $id);
        $db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'unregistered pin ' . $pin, $org, null, $id, $udid);

        $prac = $db->getPractitioner($org, $id);

        $clickatell = new clickatell();

        $message = 'You have chosen to unregister for lateness updates from ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
        $clickatell->httpSend(null, $udid, $message);
    }

}

?>