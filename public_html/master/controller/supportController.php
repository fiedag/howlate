<?php

Class supportController Extends baseController {
    
    public $controller;
    
    public function index() {
        $this->registry->template->controller = $this;
        $this->registry->template->show('support_upgrade');
        
        
    }
    
    public function fupgrade() {
        $this->registry->template->controller = $this;
        
        $this->registry->template->show('support_upgrade');
        
    }
    
    public function newfeatures() {
        $this->registry->template->controller = $this;

        $this->registry->template->show('support_newfeatures');
        
        
    }
    
    public function contact() {
        $this->registry->template->controller = $this;

        $this->registry->template->show('support_contact');
        
        
    }
    
    
    
}


?>
