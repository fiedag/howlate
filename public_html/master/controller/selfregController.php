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
        
        $this->org = organisation::getInstance($org,'OrgID');

        
        $found = false;
        foreach($this->org->Practitioners as $key => $val) {
            if ($val->ID == $id) 
                $found = true;
        }
        if (!$found)
            trigger_error("This is not a valid practitioner for this organisation", E_USER_ERROR);

        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;

        $this->registry->template->invitepin = $this->invitepin;
        $this->registry->template->show('selfreg_index');

    }

    
    public function register() {
        $this->invitepin = filter_input(INPUT_POST,"invitepin");
        $org = howlate_util::orgFromPin($this->invitepin);
        $id = howlate_util::idFromPin($this->invitepin);

        $device = filter_input(INPUT_POST,"device");
        $submit = filter_input(INPUT_POST,"submit");
 
        $this->org = organisation::getInstance($orgID, 'OrgID');
        
        if ($submit == "reg") {
           $this->org->register($org, $id, $device);
           $this->registry->template->action = "registered";
        }
        elseif ($submit == "unreg") {
           $this->org->unregister($org, $id, $device);
           $this->registry->template->action = "deregistered";
        }
        
        $this->registry->template->show('selfreg_success');
    }
    
}