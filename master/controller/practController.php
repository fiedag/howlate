<?php

Class PractController Extends baseController {

    public function index() {
        $this->Organisation->getRelated();
        $this->registry->template->controller = $this;
        $this->registry->template->clinic_json = $this->get_clinic_json();
        $this->registry->template->show('pract_index');
    }

    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance('Main');
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('practitioners')->where('OrgID =', $this->Organisation->OrgID)->limit(250);
        $xcrud->join("SurrogKey","vwAssigned","SurrogKey");
        $xcrud->columns('ID,FullName,AbbrevName,DateCreated,vwAssigned.Assigned', false);
        
        $xcrud->readonly('OrgID,ID,SurrogKey,DateCreated,vwAssigned.Assigned');  //  for create/update/delete
        $xcrud->fields('vwAssigned.OrgID,vwAssigned.ID,vwAssigned.ClinicID,DateCreated',true);
        $xcrud->hide_button('view');
        $xcrud->label(array('vwAssigned.Assigned' => 'Clinic Assigned', 'FullName' => 'Full Name', 'AbbrevName' => 'Abbrev Name', 'DateCreated' => 'Created', 'SurrogKey' => 'Reassign', 'LateToNearest' => 'Late To Nearest', 'LatenessOffset' => "Lateness Offset", 'NotificationThreshold' => 'Notification Threshold', 'LatenessCeiling' => 'Lateness Ceiling'));
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');
        $xcrud->unset_sortable(true);  // because it breaks inline editing
        $xcrud->pass_default('OrgID', $this->Organisation->OrgID);

        $xcrud->pass_default('DateCreated', date('Y-m-d'));      

        // make editable via jquery.jeditable.min.js
        $xcrud->column_pattern('vwAssigned.Assigned', "<span type='text' class='edit notwide' id='{OrgID}.{ID}'>{value}</span>");
        
        $xcrud->field_tooltip('NotificationThreshold','Lateness less than this (minutes) is shown as On Time');
        $xcrud->field_tooltip('LateToNearest','Round to nearest number of minutes');
        $xcrud->field_tooltip('LatenessOffset','Finally, subtract this number of minutes');
        $xcrud->field_tooltip('LatenessCeiling','Maximum reported lateness.  All later times are reported as this. Zero = no ceiling.');
        echo $xcrud->render();
    }

    
    private function get_clinic_json() {
        $r = "{";
        foreach ($this->Organisation->Clinics as $value) {
            $r .= "'$value->ClinicID' : '$value->ClinicName',";
        }
        $r .= "'0': 'Not assigned'}";
        return $r;
    }


    public function saveassign() {
        $pin = filter_input(INPUT_POST,'id');
        
        $elems = explode('.', $pin);
        $org = $elems[0];
        $id = $elems[1];

        $newclinic = filter_input(INPUT_POST,'value');
        
        Practitioner::getInstance($org,$id)->assign($newclinic);

        if($newclinic == 0) {
            echo 'Not assigned';
        }
        else {
            $newclinicname = Clinic::getInstance($org,$newclinic)->ClinicName;
            echo $newclinicname;
        }
    }
    
    public function get_help() {
        $this->registry->template->show('main_help');
        
    }
    
}
?>