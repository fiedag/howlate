<?php

Class devicesController Extends baseController {

    public $org;

    public function index() {
      
	$this->org = organisation::getInstance(__SUBDOMAIN);
        $this->registry->template->companyname = $this->org->OrgName;
	$this->registry->template->controller = $this;
        $this->registry->template->show('devices_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlDb());
        
        $xcrud->table('devicereg')->table_name('Registered phone devices','You can add or delete registered mobile phones here')->limit(30);
        
       
        $xcrud->pass_default('OrgID', $this->org->OrgID);
        $xcrud->hide_button('view');
        $xcrud->order_by('Created','desc');   
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        echo $xcrud->render();
    }

}
?>
