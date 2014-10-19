<?php

Class termsController Extends baseController {

    public function index() {
        $this->registry->template->controller = $this;
        $this->registry->template->show('terms_index');
        
    }


}
