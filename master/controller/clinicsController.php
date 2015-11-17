<?php

Class ClinicsController Extends baseController {

    public function index() {
        $this->Organisation = Organisation::getInstance(__SUBDOMAIN);
	$this->registry->template->controller = $this;
        $this->registry->template->show('clinics_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance('Main');
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
               
        $xcrud->table('clinics')->where('OrgID =', $this->Organisation->OrgID)->limit(10);
        $xcrud->columns('OrgID, Country, Location, Zip, Timezone,LateMessage', true);
        $xcrud->fields('OrgID,LateMessage',true);
        $xcrud->readonly('OrgID');
        
        $xcrud->hide_button('view');
        $xcrud->label(array('ClinicName' => 'Clinic', 'Address1' => 'Address', 
            'Address2' => 'Address', 'Timezone' => 'Time Zone',
                'LatLong'=>'Latitude/Longitude','NotifDestination'=>'Notifications To',
            'DisplayPolicy'=>'Display Policy'));

        $xcrud->pass_default('OrgID',$this->Organisation->OrgID);
        $tz = $this->Organisation->getTimezones();
        $tz_csv="";
        foreach($tz as $val) {
            $tz_csv .= ($tz_csv=="")?$val:",$val";
        } 
        $tz = trim($this->Organisation->Timezone);
        //echo "[Organisation Timezone is $tz]";
        $xcrud->change_type('Timezone', 'select', $tz, $tz_csv); 
        $xcrud->pass_default('Timezone',$tz);
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        $xcrud->after_remove("clinic_deleted");
        
        $xcrud->relation('NotifDestination','vwNotifDestinations','UserID',array('FullName','EmailAddress'),"vwNotifDestinations.UserID = 'SMS' or vwNotifDestinations.OrgID = '" . $this->Organisation->OrgID . "'");
        $xcrud->field_tooltip('NotifDestination', 'Send Mock SMS emails to any defined user.  Once tested, set to SMS Gateway to send real SMS messages to patients.');
        
        $xcrud->change_type('DisplayPolicy','select',DisplayPolicy::UDID_GEN_ONTIME,array(DisplayPolicy::UDID_GEN_ONTIME=>"Device-specific else Generic else On-time",DisplayPolicy::UDID_GEN_NONE=>"Device-specific else Generic else no display"));

        echo $xcrud->render();
    }
    
    
    public function get_help() {
        $this->registry->template->show('main_help');
        
    }
}
?>