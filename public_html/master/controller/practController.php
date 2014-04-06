<?php

Class practController Extends baseController {

    public $org;


    public function index() {
	    $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;

		$this->registry->template->controller = $this;
        $this->registry->template->show('pract_index');
		
		
    }
    


}
