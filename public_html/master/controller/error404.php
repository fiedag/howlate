<?php

Class error404Controller Extends baseController {

    public $org;
    
    public function index() {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, 'Subdomain');
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;
        $this->registry->template->sorry = "We're sorry.  You have reached this page in error.";
        $this->registry->template->controller = $this;
        $this->registry->template->show('error404');

    }

}

?>
