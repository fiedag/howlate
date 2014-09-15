<<<<<<< HEAD
<?php

Class clinicsController Extends baseController {

    public $org;


    public function index() {
      
	$this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);

	$this->registry->template->controller = $this;
       
        $this->registry->template->show('clinics_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance('Main');
        $xcrud->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlDb());
               
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
        $tz = trim($this->org->Timezone);
        //echo "[Organisation Timezone is $tz]";
        $xcrud->change_type('Timezone', 'select', $tz, $tz_csv); 
        $xcrud->pass_default('Timezone',$tz);
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        $xcrud->after_remove("clinic_deleted");
        
        echo $xcrud->render();
    }
    
}
=======
<?php

Class clinicsController Extends baseController {

    public $org;


    public function index() {
      
	$this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);

	$this->registry->template->controller = $this;
       
        $this->registry->template->show('clinics_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
<<<<<<< master
        $xcrud = Xcrud::get_instance();
=======
        $xcrud = Xcrud::get_instance('Main');
        $xcrud->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlDb());
               
>>>>>>> local
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
        $tz = trim($this->org->Timezone);
        //echo "[Organisation Timezone is $tz]";
        $xcrud->change_type('Timezone', 'select', $tz, $tz_csv); 
        $xcrud->pass_default('Timezone',$tz);
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        $xcrud->after_remove("clinic_deleted");
        
        echo $xcrud->render();
    }
    
}
>>>>>>> 81fa29f1384873cf49ed7f66d6c42f7637aff8dd
