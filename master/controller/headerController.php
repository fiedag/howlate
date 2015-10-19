<?php
Class HeaderController Extends baseController {
    
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

        $this->Organisation = $org;
        $this->registry->template->Organisation = $org;
        
        
        $this->registry->template->show('header_view');
    }
}
?>