<?php

/* 
 Permits a patient to register their own device
 It displays a form for a mobile device and permits the user to enter their mobile number and hit save
 */

Class selfregController Extends baseController {
    
    private $invitepin;

    // should display all doctors from subdomain as links
    public function index() {

        $this->org->getRelated();
        $found = false;

        $this->registry->template->icon_url = howlate_util::logoURL();
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