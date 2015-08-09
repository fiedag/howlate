<?php

Class PractController Extends baseController {

    public function index() {
        $this->org = Organisation::getInstance(__SUBDOMAIN);
        $this->org->getRelated();
        $this->registry->template->controller = $this;
        $this->registry->template->show('pract_index');
    }

    public function getXcrudTable() {
        
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance('Main');
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        
        $xcrud->table('practitioners')->where('OrgID =', $this->org->OrgID)->limit(25);
        $xcrud->join("SurrogKey","vwAssigned","SurrogKey");

        $xcrud->columns('ID,FullName,AbbrevName,DateCreated,vwAssigned.Assigned,SurrogKey', false);
        
        $xcrud->readonly('OrgID,ID,SurrogKey,DateCreated,vwAssigned.Assigned');  //  for create/update/delete
        $xcrud->fields('vwAssigned.OrgID,vwAssigned.ID,vwAssigned.ClinicID,DateCreated',true);
        $xcrud->hide_button('view');
        $xcrud->label(array('FullName' => 'Full Name', 'AbbrevName' => 'Abbrev Name', 'DateCreated' => 'Created', 'SurrogKey' => 'Reassign', 'LateToNearest' => 'Late To Nearest', 'LatenessOffset' => "Lateness Offset", 'NotificationThreshold' => 'Notification Threshold', 'LatenessCeiling' => 'Lateness Ceiling'));
        
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');

        $xcrud->pass_default('OrgID', $this->org->OrgID);
        //$xcrud->pass_default('ID', $this->nextPractitionerID());

        $xcrud->pass_default('DateCreated', date('Y-m-d'));      
        $xcrud->column_pattern('SurrogKey', $this->assignSpan());  // display the assignment button

        $xcrud->order_by('vwAssigned.Assigned');
        $xcrud->field_tooltip('NotificationThreshold','Lateness less than this (minutes) is shown as On Time');
        $xcrud->field_tooltip('LateToNearest','Round to nearest number of minutes');
        $xcrud->field_tooltip('LatenessOffset','Finally, subtract this number of minutes');
        $xcrud->field_tooltip('LatenessCeiling','Maximum reported lateness.  All later times are reported as this. Zero = no ceiling.');
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
        $practid = $_POST['assignpractid'];
        $clinicID = $_POST['selectedclinic'];

        if(!$orgid) {
            throw new Exception("Org id not assigned");
        }
        if(!$practid) {
            throw new Exception("Practitioner ID not assigned");
        }

        $pract = Practitioner::getInstance($orgid, $practid);
        
        $pract->place($orgid, $practid, $clinicID);

        $this->org = Organisation::getInstance(__SUBDOMAIN);
        $this->org->getRelated();
        $this->registry->template->controller = $this;
        $this->registry->template->show('pract_index');

    }

    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to assign to a different clinic or to unassign for the time being...' onClick=\"gotoAssign('{OrgID}','{SurrogKey}','{ID}');\">Reassign</span>";
        return $span;
    }


}
?>