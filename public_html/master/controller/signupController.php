<?php

Class signupController Extends baseController {

    public $org;

    public function index() {

        $this->registry->template->controller = $this;

        $this->registry->template->show('signup_view');
    }

    
    
}
