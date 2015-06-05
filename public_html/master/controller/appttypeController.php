<?php

Class ApptTypeController Extends baseController {

    public $org;
    
    private $submenu = array ("agent"=>"Agent","sessions"=>"Sessions","appttype"=>"Appt Type","apptstatus"=>"Appt Status");

        
    public function index() {
      
	$this->org = Organisation::getInstance(__SUBDOMAIN);
        $this->registry->template->companyname = $this->org->OrgName;
	$this->registry->template->controller = $this;
        $this->registry->template->show('appttype_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        
        $xcrud->table('appttype')->table_name('Appointment types.','Updated by the HL Agent.  Mark those appointment types you wish to treat differently for purposes of lateness calculations.  This means they act as if they are gaps, or they may be auto-completed.')->where('OrgID =', $this->org->OrgID)->limit(30);
        
        $xcrud->relation('ClinicID','clinics','ClinicID','ClinicName')->label(array('ClinicID' => 'Clinic'))->columns('OrgID',true);
        $xcrud->fields('OrgID,ClinicID',true);
        $xcrud->label(array('TypeCode' => 'Type Code','TypeDescr'=>'Description','CatchUp'=>'Available for catching up','AutoConsultation'=>'Auto-consultation'));
        $tt = array('CatchUp'=>'Mark if this type of appointment should be available for catching up.  Examples are: meetings, lunch, drug rep visits.',
            'AutoConsultation'=>'Mark if the appointment does not typically result in a consultation.  Once the appointment time has passed, the appointment will be deemed to have occurred, whether a consultation has been recorded or not.  Examples are Unavailable, Procedures.');
        foreach($tt as $key=>$val) {
            $xcrud->field_tooltip($key,$val)->column_tooltip($key,$val);
        }
        
        
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');
        echo $xcrud->render();
    }
    
    public function get_submenu() {
        
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "appttype";
        $this->registry->template->show('submenu_view');
    }

    
    
}
?>
