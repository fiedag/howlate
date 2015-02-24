<?php
Class headerController Extends baseController {
    //public $org;
    
    public function index() {
        $this->view();
    }

    public function view($org) {
        if (isset($_COOKIE["USER"])) {
            $this->registry->template->usercookie = $_COOKIE["USER"];
        }
        if (isset($_COOKIE["ORGID"])) {
            $this->registry->template->orgidcookie = $_COOKIE["ORGID"];
        }

        $this->org = $org;
        $this->registry->template->org = $org;
        
        
        $this->registry->template->show('header_view');
    }
}
?>