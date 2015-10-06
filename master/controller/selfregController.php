<?php

/* 
 Permits a patient to register their own device
 It displays a form for a mobile device and permits the user to enter their mobile number and hit save
 */

Class SelfRegController Extends baseController {
    
    private $invitepin;

    public function index() {
        
        $this->registry->template->exception = "";
        $this->registry->template->icon_url = HowLate_Util::logoURL();
        $this->registry->template->show('selfreg_index');
    }

    public function handle_exception($exception) {
        try {
            $ip = $_SERVER["REMOTE_ADDR"];
            Logging::write_error(0, 1, $exception->getMessage(), $exception->getFile(), $exception->getLine(), $ip, $exception->getTraceAsString());
        } catch (Exception $ex) {
        }

        $this->registry->template->exception = $exception->getMessage();
        $this->registry->template->icon_url = HowLate_Util::logoURL();
        $this->registry->template->show('selfreg_index');
    }    
    
    
    
    public function register() {
        
        $this->invitepin = strtoupper(filter_input(INPUT_POST,"invitepin"));
        if(!$this->invitepin || $this->invitepin == 'AAAAA.A') {
            throw new Exception("No pin entered.  You must enter a valid PIN.");
        }
        HowLate_Util::validatePin($this->invitepin);
        
        
        $OrgID = HowLate_Util::orgFromPin($this->invitepin);
        $PractitionerID = HowLate_Util::idFromPin($this->invitepin);

        $this->org = Organisation::getInstance($OrgID,"OrgID");
        if(!isset($this->org)) {
            throw new Exception("This is a bad org code.  Please re-enter.");
        }
        $pract = Practitioner::getInstance($OrgID, $PractitionerID);
        if(!isset($pract)) {
            throw new Exception("This is a bad practitioner code.  Please re-enter.");
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
        
        Device::register($OrgID, $PractitionerID, $UDID);
        
        header("location: http://m." . __DOMAIN . "/late?udid=" . $UDID);
    }
    
}