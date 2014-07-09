<?php

Class practController Extends baseController {

    public $org;


    public function index() {
	$this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;

        $this->registry->template->controller = $this;
        $this->registry->template->show('pract_index');
    }
   
    public function getXcrudTable() {
        //session_start();
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->table('practitioners')->where('OrgID =', $this->org->OrgID)->limit(10);
        //$xcrud->join('SurrogKey','vwAssigned','SurrogKey'); // join to get assigned column
        //$xcrud->readonly('OrgID,ID,SurrogKey,DateCreated');  //  for create/update/delete
        //$xcrud->columns('OrgID, ID', true);  // hide column in grid
        $xcrud->columns('FullName,AbbrevName,DateCreated', false);
        //$xcrud->fields('vwAssigned.Assigned', true);
        $xcrud->hide_button('view');
        //$xcrud->label(array('FullName' => 'Full Name', 'AbbrevName' => 'Abbrev Name', 'DateCreated' => 'Created', 'SurrogKey' => 'Surrog Key'));
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');

        $xcrud->pass_default('OrgID',$this->org->OrgID);
        $xcrud->pass_default('ID', $this->nextPractitionerID());
        //$xcrud->pass_default('SurrogKey', $this->org->OrgID . "." . $this->nextPractitionerID());
        $xcrud->pass_default('DateCreated', date('Y-m-d'));
        
        //$xcrud->column_pattern('SurrogKey', $this->assignSpan());  // display the assignment button
        
        echo $xcrud->render();
    }

    
    public function get_clinic_options() {
        foreach ($this->org->Clinics as $value) {
            echo "<option value='" . $value->ClinicID . "'>$value->ClinicName</option>";
        }
        echo "<option value='0'>No clinic</option>";

    }
     
    public function assign() {
        $db = new howlate_db();

        $orgID = $_POST['assignorgid'];
        $PractID = $_POST['assignpract'];
        $clinicID = $_POST['selectedclinic'];

        $db->place($orgID,$PractID,$clinicID);
     
        header("location: http://" . __FQDN . "/pract?ok=yes");
    }

    
    private function assignSpan() {
        $span = "<span class='invite' title='Click to assign' onClick=\"gotoAssign('{SurrogKey}');\">Assign</span>";
       return $span;
    }
    
    private function nextPractitionerID() {
        $db = new howlate_db();
        return $db->getNextPractID($this->org->OrgID);
    }
    
}
