<?php

Class practController Extends baseController {

    public $org;

    public function index() {
        $this->org = organisation::getInstance(__SUBDOMAIN);
        $this->org->getRelated();
        $this->registry->template->controller = $this;
        $this->registry->template->show('pract_index');
    }

    public function getXcrudTable() {
        
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance('Main');
        $xcrud->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlDb());
        
        $xcrud->table('practitioners')->where('OrgID =', $this->org->OrgID)->limit(10);
        $xcrud->join("SurrogKey","vwAssigned","SurrogKey");

        $xcrud->columns('FullName,AbbrevName,DateCreated,vwAssigned.Assigned,SurrogKey', false);
        
        $xcrud->readonly('OrgID,ID,SurrogKey,DateCreated,vwAssigned.Assigned');  //  for create/update/delete
        $xcrud->fields('vwAssigned.OrgID,vwAssigned.ID,vwAssigned.ClinicID,DateCreated',true);
        $xcrud->hide_button('view');
        $xcrud->label(array('FullName' => 'Full Name', 'AbbrevName' => 'Abbrev Name', 'DateCreated' => 'Created', 'SurrogKey' => 'Reassign', 'LateToNearest' => 'Late To Nearest', 'LatenessOffset' => "Lateness Offset", 'NotificationThreshold' => 'Notification Threshold'));
        
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');

        $xcrud->pass_default('OrgID', $this->org->OrgID);
        //$xcrud->pass_default('ID', $this->nextPractitionerID());

        $xcrud->pass_default('DateCreated', date('Y-m-d'));      
        $xcrud->column_pattern('SurrogKey', $this->assignSpan());  // display the assignment button

        
        $xcrud->field_tooltip('NotificationThreshold','Lateness less than this (minutes) is shown as On Time');
        $xcrud->field_tooltip('LateToNearest','Round to nearest number of minutes');
        $xcrud->field_tooltip('LatenessOffset','Finally, subtract this number of minutes');
        echo $xcrud->render();
    }

    public function get_clinic_options() {
        foreach ($this->org->Clinics as $value) {
            echo "<option value='" . $value->ClinicID . "'>$value->ClinicName</option>";
        }
        echo "<option value='0'>No clinic</option>";
    }

    public function assign() {
        
        $orgid = $_POST['assignorgid'];
        $surrogkey = $_POST['assignsurrogkey'];
        $clinicID = $_POST['selectedclinic'];

        practitioner::getInstance($orgid, $surrogkey, 'SurrogKey')->place2($orgid, $surrogkey, $clinicID);

        $this->org = organisation::getInstance(__SUBDOMAIN);
        $this->org->getRelated();
        $this->registry->template->controller = $this;
        $this->registry->template->show('pract_index');

    }

    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to assign to a different clinic or to unassign for the time being...' onClick=\"gotoAssign('{OrgID}','{SurrogKey}');\">Reassign</span>";
        return $span;
    }


}
?>