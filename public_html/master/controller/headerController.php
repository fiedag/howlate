<?php

Class headerController Extends baseController {
    public $org;
    
    public function index() {
        $this->view();
    }

    public function view($org) {
        $this->org = $org;
        $this->registry->template->org = $org;
        $this->registry->template->show('header_view');
    }

}

?>