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
        
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->table('practitioners')->where('OrgID =', $this->org->OrgID)->limit(10);
        $xcrud->join("SurrogKey","vwAssigned","SurrogKey");

        $xcrud->columns('FullName,AbbrevName,DateCreated,vwAssigned.Assigned,SurrogKey', false);
        
        $xcrud->readonly('OrgID,ID,SurrogKey,DateCreated,vwAssigned.Assigned');  //  for create/update/delete

        $xcrud->hide_button('view');
        $xcrud->label(array('FullName' => 'Full Name', 'AbbrevName' => 'Abbrev Name', 'DateCreated' => 'Created', 'SurrogKey' => 'Reassign', 'LateToNearest' => 'Late To Nearest', 'LatenessOffset' => "Lateness Offset", 'NotificationThreshold' => 'Notification Threshold'));
        
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');

        $xcrud->pass_default('OrgID', $this->org->OrgID);
        $xcrud->pass_default('ID', $this->nextPractitionerID());

        $xcrud->pass_default('DateCreated', date('Y-m-d'));
        
        $xcrud->column_pattern('SurrogKey', $this->assignSpan());  // display the assignment button


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
        $orgid = $_POST['assignorgid'];
        $surrogkey = $_POST['assignsurrogkey'];
        $clinicID = $_POST['selectedclinic'];

        //$db->place($orgID,$PractID,$clinicID);
        $db->place2($orgid, $surrogkey, $clinicID);

        header("location: http://" . __FQDN . "/pract?ok=yes");
    }

    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to assign to a different clinic or to unassign for the time being...' onClick=\"gotoAssign('{OrgID}','{SurrogKey}');\">Reassign</span>";
        return $span;
    }


    private function nextPractitionerID() {
        $db = new howlate_db();
        return $db->getNextPractID($this->org->OrgID);
    }

}
