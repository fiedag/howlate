<?php

Class DevicesController Extends baseController {

    public $org;
    private $submenu = array ("index"=>"Devices","recent" => "Recents");
    
    public function index2() {
      
	$this->org = Organisation::getInstance(__SUBDOMAIN);
        $this->registry->template->companyname = $this->org->OrgName;
	$this->registry->template->controller = $this;
		
    }
    
    
    public function index() {
	$this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getDevices();
        $this->registry->template->show('devices_index');
    }    
    
    
    public function recent() {
	$this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getRecentDevices();
        $this->registry->template->show('devices_index');
    }    

    
    
    public function getDevices() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        
        $xcrud->table('devicereg')->table_name('Registered phone devices','You can add or delete registered mobile phones here')->limit(30);
        
        $xcrud->column_pattern('UDID', $this->assignSpan());  // display the assignment button
        
        $xcrud->pass_default('OrgID', $this->org->OrgID);
        $xcrud->hide_button('view');
        $xcrud->order_by('Created','desc');   
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        echo $xcrud->render();
    }

    
    public function getRecentDevices() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        
        $xcrud->table('devicereg')->where("Created >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)")->table_name('Registered phone devices','You can add or delete registered mobile phones here')->limit(30);
        
        $xcrud->column_pattern('OrgID', $this->assignSpan());  // display the assignment button
        
        $xcrud->pass_default('OrgID', $this->org->OrgID);
        $xcrud->hide_button('view');
        $xcrud->order_by('Created','desc');   
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        echo $xcrud->render();
    }
    
    
    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to view what this device sees...' onClick=\"openView('{UDID}');\">{UDID}</span>";
        return $span;
    }
        
}
?>
