<?php

Class error404Controller Extends baseController {

    public $org;
    
    public function index() {
        $this->org = organisation::getInstance(__SUBDOMAIN);
        if(isset($this->org)) {
            $this->registry->template->companyname = $this->org->OrgName;
        }
        else {
            $this->registry->template->companyname = "HOW-LATE.COM";
        }
        
        $this->registry->template->sorry = "Oops.  We couldn't find what you're looking for.";
        $this->registry->template->sorry_sub = "The requested page could not be found. Please go back and try looking again.";
        $this->registry->template->controller = $this;
        $this->registry->template->show('error404');

    }

    function get_header() {
    }

    function get_footer() {
        
    }
    
    
}

?>
