<?php

Class PmSystemsController Extends baseController {
    public $org;

    private $submenu = array ("index"=>"PM Systems",
        "clinint"=>"Clin Int"
     );    
    
    
    public function index() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getPMSystems();
        $this->registry->template->show('pmsystems_index');
    }    
    public function clinint() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getClinInt();
        $this->registry->template->show('pmsystems_index');
    }    
    
     public function getPMSystems() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('pmsystems')->table_name('Practice Management Systems',"See CSV export button at end.")->limit(50);
        $xcrud->order_by('Priority','desc');
        $xcrud->columns('Certification,SelectLates,SelectSessions,SelectToNotify,Timestamp,SelectAppointments,SelectTimeNow', true);
        $xcrud->change_type('SelectLates,SelectToNotify,SelectSessions,SelectAppointments,SelectTimeNow,SelectAppointments,SelectApptTypes,SelectApptStatus', 'textarea');
        $xcrud->auto_xss_filtering = false; // disable all xcrud's POST and GET data filtering for this page.  Essential!

        return $xcrud->render();
    }    
    
     public function getClinInt() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('clinicintegration')->table_name('Clinic Integrations',"See CSV export button at end.")->limit(50);
        $xcrud->columns('Instance,PMSystem,ConnectionString,UID,PWD',true);
        $xcrud->relation('OrgID','orgs','OrgID','OrgName');
        $xcrud->relation('ClinicID','clinics','ClinicID','ClinicName');
        

        return $xcrud->render();
    }    
    
    
}
?>