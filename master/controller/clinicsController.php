<?php

Class ClinicsController Extends baseController {

    public $org;


    public function index() {
        $this->org = Organisation::getInstance(__SUBDOMAIN);
	$this->registry->template->controller = $this;
        $this->registry->template->show('clinics_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance('Main');
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
               
        $xcrud->table('clinics')->where('OrgID =', $this->org->OrgID)->limit(10);
        $xcrud->columns('OrgID, Country, Location, Zip, Timezone, PatientReply, ReplyRecip,AllowMessage,MsgRecip,SuppressNotifications,ApptLogging', true);
        $xcrud->fields('PatientReply,ReplyRecip,MsgRecip,AllowMessage,ApptLogging',true);
        $xcrud->readonly('OrgID');
        
        $xcrud->hide_button('view');
        $xcrud->label(array('ClinicName' => 'Clinic', 'Address1' => 'Address', 'Address2' => 'Address', 'Timezone' => 'Time Zone', 'PatientReply' => 'Allow Patient to reply', 'ReplyRecip' => 'Reply Recipient Email', 'SuppressNotifications' =>'Suppress Notifications'));

        $xcrud->pass_default('OrgID',$this->org->OrgID);
        $tz = $this->org->getTimezones();
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
        

        $xcrud->field_tooltip('PatientReply','Whether Lateness view permits patients to reply to the clinic');
        $xcrud->field_tooltip('ReplyRecip','Recipient email address if replies are permitted.');
        
        echo $xcrud->render();
    }
    
}
?>