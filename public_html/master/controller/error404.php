<?php

Class error404Controller Extends baseController {

    public $org;
    
    public function index() {
        
        $this->registry->template->sorry = "We're sorry.  You have reached this page in error.";
        $this->registry->template->controller = $this;
        $this->registry->template->show('error404');

    }

}

?>
