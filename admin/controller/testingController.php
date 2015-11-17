<?php

Class TestingController Extends baseController {

    private $submenu = array ("CCEPX"=>"Emerald Medical Group");

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
        
        $this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;

        $this->registry->template->show('submenu_view');        
        
        $this->registry->template->show('testing_body');
        
        $tests = new ApptBookTests($OrgID = 'CCEPX',$ClinicID = 140);
        $count=0;
        foreach($tests->Iterations as $key=>$val) {
            $this->registry->template->count = $count++;
            $this->registry->template->dt = $val['Date'];
            $this->registry->template->test_time = HowLate_Util::toHHMMSS($val['Summary'][0]['Time Now']);
            //$this->registry->template->show('testing_iteration');
            
            $appt_bulk = $val['appt_bulk'];
            $this->registry->template->appt_bulk_count = count($appt_bulk);
            
            $this->registry->template->OrgID = $val['OrgID'];
            $this->registry->template->ClinicID = $val['ClinicID'];
            
            $this->registry->template->show('testing_apptbulk');
        }
        
        $this->get_footer();
    }

    
    public function retrieveTest() {
        $OrgID = filter_input(INPUT_GET,"OrgID");
        $ClinicID = filter_input(INPUT_GET,"ClinicID");
        $TestIndex = filter_input(INPUT_GET,"TestIndex");
        
        $appt_bulk = $this->getTest($OrgID,$ClinicID,$TestIndex);
        require_once('includes/kint/Kint.class.php');
        d($appt_bulk);
    }
    
    
    private function getTest($OrgID, $ClinicID, $index) {
        
        $tests = new ApptBookTests($OrgID,$ClinicID);
        $val = $tests->Iterations[$index];
        $appt_bulk = $val['appt_bulk'];
        return $appt_bulk;        
    }
    
    public function calcLateness() {
        $OrgID = filter_input(INPUT_GET,"OrgID");
        $ClinicID = filter_input(INPUT_GET,"ClinicID");
        $TestIndex = filter_input(INPUT_GET,"TestIndex");
        
        
    }
    
    
    
    public function displaySummary($test) {
        d($test['Date']);
        d($test['appt_bulk']);
        
        //$this->registry->template->Time = $summary[0]['Time Now'];
        //$this->registry->template->Practitioner = $summary[0]['Time Now'];
        
        //$this->registry->template->show('testing_summary');
    }
    
}

?>