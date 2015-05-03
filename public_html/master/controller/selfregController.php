<?php

/* 
 Permits a patient to register their own device
 It displays a form for a mobile device and permits the user to enter their mobile number and hit save
 */

Class selfregController Extends baseController {
    
    private $invitepin;

    // should display all doctors from subdomain as links
    public function index() {
        
        $this->registry->template->message = "";
        $this->registry->template->icon_url = howlate_util::logoURL();
        $this->registry->template->show('selfreg_index');
    }
    
    public function register() {
        
        $this->invitepin = strtoupper(filter_input(INPUT_POST,"invitepin"));
        howlate_util::validatePin($this->invitepin);
        
        $OrgID = howlate_util::orgFromPin($this->invitepin);
        $PractitionerID = howlate_util::idFromPin($this->invitepin);

        $this->org = organisation::getInstance($OrgID,"OrgID");
        if(!isset($this->org)) {
            $this->registry->template->icon_url = howlate_util::logoURL();
            $this->registry->template->message = "This is a bad code.  Please re-enter it.";
            $this->registry->template->show('selfreg_index');
            return;
        }
        $pract = practitioner::getInstance2($OrgID, $PractitionerID);
        if(!isset($pract)) {
            $this->registry->template->icon_url = howlate_util::logoURL();
            $this->registry->template->message = "This is a bad code.  Please re-enter it.";
            $this->registry->template->show('selfreg_index');
            return;
        }
        

        $UDID = filter_input(INPUT_POST,"device");
        
        if(!isset($UDID) || $UDID == "") {
            if(isset($_COOKIE["UDID"])) {
                $UDID = $_COOKIE["UDID"];
            } 
            else {
                $UDID = uniqid();
                setcookie("UDID", $UDID, time() + (10 * 365 * 24 * 60 * 60),"/");
            }
        }
        
        device::register($OrgID, $PractitionerID, $UDID);
        
        header("location: http://m." . __DOMAIN . "/late/view?udid=" . $UDID);
    }
    
}