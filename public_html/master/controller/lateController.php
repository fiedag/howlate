<?php

Class lateController Extends baseController {

    function __construct($registry) {
        $this->registry = $registry;  
    }    
    
    public function index() {
        $this->view();
    }


    public function ajax() {
        $udid = filter_input(INPUT_GET,"udid");
        if (!$udid) {
            $xudid = filter_input(INPUT_GET, "xudid");
            if (!$xudid) {
                throw new Exception("Udid or Xudid parameter must be supplied");
            }
            $udid = howlate_util::to_udid($xudid);
        }

        $late_arr = device::getLatenessesAjax($udid);
        $this->registry->template->lates = $late_arr;
        $this->registry->template->show('late_json');
    }
 
    ///
    /// pins is a list of pins delimited by commas
    ///
    public function pins() {
        $pins = filter_input(INPUT_GET,'pins');
        if (!$pins) {
            
            throw new Exception("pins parameter must be supplied.");
        }            
        $pins = explode(',',$pins);
        
        $late_arr = array();
        foreach($pins as $key=>$value) {
            
            list($OrgID,$PractitionerID) = explode('.',$value);
            
            $late_arr[$value] = practitioner::getInstance($OrgID,$PractitionerID)->getCurrentLateness();
        }
        
        $this->registry->template->lates = $late_arr;
        $this->registry->template->show('late_json');
    }

    
    public function view() {
        $this->registry->template->controller = $this;
        $this->registry->template->refresh = 3000;  // milliseconds
        $this->registry->template->when_refreshed = 'Updated ' . date('h:i A');
        $this->registry->template->bookmark_title = "How late";
        $this->registry->template->bookmark_url = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $this->registry->template->icon_url = howlate_util::logoURL(__SUBDOMAIN);
        $this->registry->template->apple_icon_url = howlate_util::logoWhiteBG();

        
        $udid = filter_input(INPUT_GET, 'udid');
        if (!$udid) {
            $xudid = filter_input(INPUT_GET, 'xudid');
            $udid = howlate_util::to_udid($xudid);
        }

        if ($udid) {
            $this->registry->template->UDID = $udid;
            $this->registry->template->refresh_url = "http://m." . __DOMAIN . "/late/ajax?udid=$udid";
            
            $lates = device::getLatenesses($udid); // a two-dimensional array ["clinic name"][array]
            
            if (!empty($lates)) {
                $this->registry->template->lates = $lates;
                $this->registry->template->show('late_view');
            } else {
                $this->registry->template->show('late_none');
            }
        }
    }

    public function view2() {
        $this->registry->template->controller = $this;
        $this->registry->template->refresh = 3000;  // milliseconds
        $this->registry->template->when_refreshed = 'Updated ' . date('h:i A');
        $this->registry->template->bookmark_title = "How late";
        $this->registry->template->bookmark_url = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $this->registry->template->icon_url = howlate_util::logoURL(__SUBDOMAIN);
        $this->registry->template->apple_icon_url = howlate_util::logoWhiteBG();

        
        $udid = filter_input(INPUT_GET, 'udid');
        if (!$udid) {
            $xudid = filter_input(INPUT_GET, 'xudid');
            $udid = howlate_util::to_udid($xudid);
        }

        if ($udid) {
            $this->registry->template->UDID = $udid;
            $this->registry->template->refresh_url = "http://m." . __DOMAIN . "/late/ajax?udid=$udid";
            
            $lates = device::getLatenesses($udid); // a two-dimensional array ["clinic name"][array]
            
            if (!empty($lates)) {
                $this->registry->template->lates = $lates;
                $this->registry->template->show('late_view2');
            } else {
                $this->registry->template->show('late_none');
            }
        }
    }
    
    public function cancel() {
        $OrgID = filter_input(INPUT_POST,"OrgID");
        $PractitionerID = filter_input(INPUT_POST,"PractitionerID");
        $PractitionerName = filter_input(INPUT_POST,"PractitionerName2");
        $ClinicID = filter_input(INPUT_POST,"ClinicID");
        $UDID = filter_input(INPUT_POST,"UDID");
        clinic::getInstance($OrgID, $ClinicID)->cancelAppointmentMessage($OrgID, $PractitionerID, $PractitionerName, $UDID);
    }
    
    
    public function selfreg() {
        $this->registry->template->controller = $this;
        $this->registry->template->refresh = 3000;  // milliseconds
        $this->registry->template->when_refreshed = 'Updated ' . date('h:i A');
        $this->registry->template->bookmark_title = "How late";
        $this->registry->template->bookmark_url = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $this->registry->template->icon_url = howlate_util::logoURL(__SUBDOMAIN);
        $this->registry->template->apple_icon_url = howlate_util::logoWhiteBG();

        
        $pin = filter_input(INPUT_GET, 'pin');
        if (!$pin) {
            throw new Exception("Requires pin parameter");
        }

        howlate_util::validatePin($pin);
        $org = howlate_util::orgFromPin($pin);
        $clinic = howlate_util::idFromPin($pin);
        
        $lates = device::getLatenesses($clinic, "ClinicID"); // a two-dimensional array ["clinic name"][array]
            
            if (!empty($lates)) {
                $this->registry->template->lates = $lates;
                $this->registry->template->show('late_view');
            }
   
    } 
}

?>
