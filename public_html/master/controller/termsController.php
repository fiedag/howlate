<?php

Class termsController Extends baseController {

    public $org;
    public $currentClinic;
    public $currentClinicName;
    

    public function index() {
        if (!isset($this->org)) {
            $this->org = new organisation();
            $this->org->getby(__SUBDOMAIN, "Subdomain");
            $this->registry->template->companyname = $this->org->OrgName;
            $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);
        }
        
        if (isset($_SESSION["clinic"])) {
            $this->currentClinic = $_SESSION["clinic"];
            $this->currentClinicName = $_SESSION["clinicname"];
        }
        else {
            $this->currentClinic = $this->org->Clinics[0]->ClinicID;
            $this->currentClinicName = $this->org->Clinics[0]->ClinicName;
            $_SESSION["clinic"] = $this->currentClinic;
            $_SESSION["clinicname"] = $this->currentClinicName;
        }
        
        $this->registry->template->controller = $this;
        $this->registry->template->show('terms_index');
        
    }


}
