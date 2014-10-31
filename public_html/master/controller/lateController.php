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
            throw new Exception("Udid parameter must be supplied");
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

    
    public function view_old() {
        $this->registry->template->controller = $this;
        $this->registry->template->refresh = 3000;  // milliseconds
        $this->registry->template->when_refreshed = 'Updated ' . date('h:i A');
        $this->registry->template->bookmark_title = "How late";
        $this->registry->template->bookmark_url = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $this->registry->template->icon_url = howlate_util::logoURL(__SUBDOMAIN);
        $this->registry->template->refresh_url = "http://secure." . __DOMAIN . "/late/pins?pins=";
        $this->registry->template->apple_icon_url = howlate_util::logoWhiteBG();
        
        if (isset($_GET['udid'])) {
            $udid = filter_input(INPUT_GET, 'udid');
            $this->registry->template->UDID = $udid;
            
            $lates = device::getLatenesses($udid); // a two-dimensional array ["clinic name"][array]
            
            if (!empty($lates)) {
                $this->registry->template->lates = $lates;
                $this->registry->template->show('late_view');
            } else {
                $this->registry->template->show('late_none');
            }
        }
    }
    
    public function view() {
        $this->registry->template->controller = $this;
        $this->registry->template->refresh = 3000;  // milliseconds
        $this->registry->template->when_refreshed = 'Updated ' . date('h:i A');
        $this->registry->template->bookmark_title = "How late";
        $this->registry->template->bookmark_url = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $this->registry->template->icon_url = howlate_util::logoURL(__SUBDOMAIN);
        $this->registry->template->apple_icon_url = howlate_util::logoWhiteBG();
        
        if (isset($_GET['udid'])) {
            $udid = filter_input(INPUT_GET, 'udid');
            $this->registry->template->UDID = $udid;
            $this->registry->template->refresh_url = "http://secure." . __DOMAIN . "/late/ajax?udid=$udid";
            
            $lates = device::getLatenesses($udid); // a two-dimensional array ["clinic name"][array]
            
            if (!empty($lates)) {
                $this->registry->template->lates = $lates;
                $this->registry->template->show('late_view');
            } else {
                $this->registry->template->show('late_none');
            }
        }
    }
    
}

?>
