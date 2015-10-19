<?php

Class SessionsController Extends baseController {
    
    private $submenu = array ("agent"=>"Agent","sessions"=>"Sessions","appttype"=>"Appt Type","apptstatus"=>"Appt Status");

    public function index() { 
        $this->registry->template->companyname = $this->Organisation->OrgName;
	$this->registry->template->controller = $this;
        $this->registry->template->show('sessions_index');
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        
        $xcrud->table('sessions')->table_name('Sessions from the Practice Mgt System','Updated by the HL Agent')->where('OrgID =', $this->Organisation->OrgID)->limit(30);
        
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');
        echo $xcrud->render();
    }
    
    public function get_submenu() {
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "sessions";
        $this->registry->template->show('submenu_view');
    }
}
?>
