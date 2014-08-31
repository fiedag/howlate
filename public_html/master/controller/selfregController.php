<?php

/* 
 Permits a patient to register their own device
 It displays a form for a mobile device and permits the user to enter their mobile number and hit save
 */

Class selfregController Extends baseController {
    
    private $org;

    private $invitepin;
    
    public function index() {
        $this->invitepin = filter_input(INPUT_GET, "invitepin");
        if (!isset($this->invitepin)) {
            trigger_error("Program called with incorrect parameters", E_USER_ERROR);
        }

        $org = howlate_util::orgFromPin($this->invitepin);
        $id = howlate_util::idFromPin($this->invitepin);

        howlate_util::validatePin($this->invitepin);
        
        $this->org = new organisation();
        $this->org->getby($org, "OrgID");

        if (__SUBDOMAIN != $this->org->Subdomain) {
            trigger_error("Program called from incorrect subdomain or with incorrect orgid", E_USER_ERROR);
        }
        
        $found = false;
        foreach($this->org->Practitioners as $key => $val) {
            if ($val->ID == $id) 
                $found = true;
        }
        if (!$found)
            trigger_error("This is not a valid practitioner for this organisation", E_USER_ERROR);

        
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);

        $this->registry->template->invitepin = $this->invitepin;
        $this->registry->template->show('selfreg_index');

    }

    
    public function register() {
        $this->invitepin = filter_input(INPUT_POST,"invitepin");
        $device = filter_input(INPUT_POST,"device");
        $submit = filter_input(INPUT_POST,"submit");
 
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        
        if ($submit == "reg") {
            $this->org->register($this->invitepin, $device);
           $this->registry->template->action = "registered";
        }
        elseif ($submit == "unreg") {
           $this->org->unregister($this->invitepin, $device);
           $this->registry->template->action = "deregistered";
        }
        
        $this->registry->template->show('selfreg_success');
    }
    
}