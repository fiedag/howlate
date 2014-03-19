<?php

Class loginController Extends baseController {

	public function index() 
	{
            $org = new organisation();
            $org->getby(__SUBDOMAIN, "Subdomain");
            $this->registry->template->companyname = $org->OrgName;
            $this->registry->template->logourl = $org->LogoURL;
            $this->registry->template->show('login_index');
	}
        
        
        public function attempt() {
            
            echo "A login attempt has been made.  Check password<br>";
            
            
            
        }
}
?>
