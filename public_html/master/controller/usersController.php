<?php

Class usersController Extends baseController {

    public $org;


    public function index() {
      
	$this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;

	$this->registry->template->controller = $this;
        $this->registry->template->show('users_index');
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->table('orgusers')->where('OrgID =', $this->org->OrgID)->limit(10)->table_name('Users','You can add or delete as many users as you want');
        $xcrud->columns('OrgID, XPassword,DateCreated,SecretQuestion1,SecretAnswer1', true);
        $xcrud->readonly('OrgID');
        $xcrud->fields('XPassword,SecretQuestion1,SecretAnswer1,DateCreated', true);
        
        $xcrud->hide_button('view');
        $xcrud->label(array('UserID' => 'User ID', 'EmailAddress' => 'Email', 'FullName' => 'Name'));

        $xcrud->pass_default('OrgID',$this->org->OrgID);
      
        $tz = $this->org->gettimezones();
        $tz_csv="";
        foreach($tz as $val) {
            $tz_csv .= ($tz_csv=="")?$val:",$val";
        } 
        
        $xcrud->change_type('Timezone', 'select', 'Australia/Adelaide', $tz_csv);        
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        $xcrud->after_remove("clinic_deleted");
        
        echo $xcrud->render();
    }
    
}
