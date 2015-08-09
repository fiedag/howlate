<?php

Class TestingController Extends baseController {

    public $org;
    private $submenu = array ("CCEPX"=>"Emerald Medical Group"
   );

    public function index() {
        $this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->show('testing_index');
    }
    
    public function CCEPX() {
        require_once('includes/kint/Kint.class.php');
        $tests = new ApptBookTests($OrgID = 'CCEPX',$ClinicID = 140);
        
        $this->registry->template->controller = $this;
        $this->get_header();
        
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');        
        
        $this->registry->template->tests = $tests;
        
        $this->registry->template->show('testing_index');
        
        $this->get_footer();
    }

    public function displaySummary($summary) {
        d($summary);
        
        $this->registry->template->Time = $summary[0]['Time Now'];
        $this->registry->template->Practitioner = $summary[0]['Time Now'];
        
        $this->registry->template->show('testing_summary');
    }
    
    
    
    
    
}

?>