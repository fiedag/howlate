<?php

Class clinicsController Extends baseController {

    public $org;


    public function index() {
      
	$this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;

	$this->registry->template->controller = $this;
       
        $this->registry->template->show('clinics_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->table('clinics')->where('OrgID =', $this->org->OrgID)->limit(10);
        $xcrud->columns('OrgID, Country, Location, Zip, Timezone', true);
        $xcrud->readonly('OrgID');
        
        $xcrud->hide_button('view');
        $xcrud->label(array('ClinicName' => 'Clinic', 'Address1' => 'Address', 'Address2' => 'Address', 'Timezone' => 'Time Zone'));

        $xcrud->pass_default('OrgID',$this->org->OrgID);
      
        $tz = $this->org->gettimezones();
        $tz_csv="";
        foreach($tz as $val) {
            $tz_csv .= ($tz_csv=="")?$val:",$val";
        } 
        
        $xcrud->change_type('Timezone', 'select', 'Australia/Adelaide', $tz_csv);        
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     

        
        echo $xcrud->render();
    }

}
