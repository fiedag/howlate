<?php

Class supportController Extends baseController {
    public $org;
    public $controller;

    public function index() {
        $this->getOrg();
        $this->registry->template->controller = $this;
        $this->registry->template->show('support_upgrade');
    }

    public function pricing() {
        $this->getOrg();
        $this->registry->template->controller = $this;
        $this->registry->template->monthly_fee = "$45";
        $this->registry->template->num_clinics = 2;
        $this->registry->template->num_practitioners = 20;
        
        $this->registry->template->show('support_pricing');
    }

    public function newfeatures() {
        $this->getOrg();
        $this->registry->template->controller = $this;

        $this->registry->template->show('support_newfeatures');
    }

    public function contact() {
        $this->getOrg();
        $this->registry->template->controller = $this;
        $this->registry->template->show('support_contact');
    }

    private function getOrg() {
        if (!isset($this->org)) {
            $this->org = new organisation();
            $this->org->getby(__SUBDOMAIN, "Subdomain");
            $this->registry->template->companyname = $this->org->OrgName;
            $this->registry->template->logourl = $this->org->LogoURL;
        }
    }

}

?>
