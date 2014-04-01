<?php


Class mainController Extends baseController {
        public $org;
        
	public function index() 
	{
            $this->org = new organisation();
            $this->org->getby(__SUBDOMAIN, "Subdomain");
            $this->registry->template->companyname = $this->org->OrgName;
            $this->registry->template->logourl = $this->org->LogoURL;
            
            $this->registry->template->controller = $this;
            $this->registry->template->show('main_index');
	}
        
        public function get_header() {
            include 'controller/headerController.php';
            $header = new headerController($this->registry);
            $header->view($this->org);
        }
        
        public function get_footer() {
            include 'controller/footerController.php';
            $footer = new footerController($this->registry);
            $footer->view($this->org);
        }   
        
        //
        // returns a html table containing the 
        //
        public function get_table() {
            
            
        }
}