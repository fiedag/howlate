<?php

Class termsController Extends baseController {

    public function index() {
        $this->registry->template->controller = $this;
        $this->registry->template->show('terms_index');
        
    }


    // very simple banner only
    function get_banner() {
        include 'controller/bannerController.php';
        $header = new bannerController($this->registry);
        $header->view($this->org);
    }

        
    
    
}
