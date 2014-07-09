<?php

Class devicesController Extends baseController {

    public $org;

    public function index() {
      
	$this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;

	$this->registry->template->controller = $this;
       
        $this->registry->template->show('devices_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->table('devicereg')->where('OrgID =', $this->org->OrgID)->limit(30);
        
        
        $xcrud->pass_default('OrgID', $this->org->OrgID);

        $xcrud->hide_button('view');

      
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     

        
        echo $xcrud->render();
    }

}
