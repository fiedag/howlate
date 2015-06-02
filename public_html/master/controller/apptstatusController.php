<?php

Class ApptStatusController Extends baseController {

    public $org;
    
    private $submenu = array ("agent"=>"Agent","sessions"=>"Sessions","appttype"=>"Appt Type","apptstatus"=>"Appt Status");
        
    public function index() {
      
	$this->org = Organisation::getInstance(__SUBDOMAIN);
        $this->registry->template->companyname = $this->org->OrgName;
	$this->registry->template->controller = $this;
        $this->registry->template->show('apptstatus_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        
        $xcrud->table('apptstatus')->table_name('Appointment Status codes.','Updated by the HL Agent.  Mark those appointment status codes you wish to ignore for purposes of lateness calculations.  This means they act as if they are gaps.')->where('OrgID =', $this->org->OrgID)->limit(30);
        
        $xcrud->relation('ClinicID','clinics','ClinicID','ClinicName')->label(array('ClinicID' => 'Clinic'))->columns('OrgID',true);
        $xcrud->change_type('IgnoreAppt', 'bool');
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');
        echo $xcrud->render();
    }
    
    public function get_submenu() {
        
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "apptstatus";
        $this->registry->template->show('submenu_view');
    }

    
    
}
?>
