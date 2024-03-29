<?php

Class DevicesController Extends baseController {
    
    private $submenu = array ("devices/index"=>"Devices",
        "devices/perspectives"=>"Perspectives"
     );    

//    public function index() {
//        $this->registry->template->companyname = $this->Organisation->OrgName;
//	$this->registry->template->controller = $this;
//        $this->registry->template->show('devices_index');
//    }
    
    public function index() {
	$this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "devices/" . __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getDevices();
        $this->registry->template->show('devices_index');
    }
    
    public function perspectives() {
	$this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "devices/" . __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getPerspectives();
        $this->registry->template->show('devices_index');
    }    

    
    public function getDevices() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        
        $xcrud->table('devicereg')->table_name('Registered phone devices','You can add or delete registered mobile phones here')->where('OrgID =', $this->Organisation->OrgID)->limit(30);

        $xcrud->column_pattern('OrgID', $this->assignSpan());  // display the assignment button
       
        $xcrud->pass_default('OrgID', $this->Organisation->OrgID);
        $xcrud->hide_button('view');
        $xcrud->order_by('Created','desc');   
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        return $xcrud->render();
    }
    public function getPerspectives() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        
        $xcrud->table('lates')->table_name('lateness table','Records with no UDID show the general perspective')->where('OrgID =', $this->Organisation->OrgID)->limit(30);
        $xcrud->pass_default('OrgID', $this->Organisation->OrgID);
        $xcrud->hide_button('view');
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');
        return $xcrud->render();
    }
    
    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to view what this device sees...' onClick=\"openView('{UDID}');\">View</span>";
        return $span;
    }
    
}
?>
