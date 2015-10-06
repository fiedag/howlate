<?php

///
/// This controller is for the main page which is used by Clinic admins to update lateness
/// and send invitations.
Class MainController Extends baseController {

    public $currentClinic;
    public $currentClinicName;
    public $currentClinicTimezone;

    public $UTC;
    
    public function index() {
        $this->org->getRelated();
        $ClinicID = $this->getCurrentClinicID();
        $clin = Clinic::getInstance($this->org->OrgID,$ClinicID);
        
        date_default_timezone_set($clin->Timezone);

        $time = time();
        $check = $time - date("Z", $time);
        $this->UTC = strftime("%H:%M:%S UTC", $check);

        
        $this->currentClinic = $ClinicID;
        $this->currentClinicName = $clin->ClinicName;
        $this->currentClinicTimezone = $clin->Timezone;

        $this->registry->template->controller = $this;
        $this->registry->template->show('main_index');
    }

    private function getCurrentClinicID() {
        if(isset($_SESSION["CLINIC"])) {
            return $_SESSION["CLINIC"];
        }
        if(isset($this->org->ActiveClinics)) {
            return $this->org->ActiveClinics[0]->ClinicID;
        }
        if(isset($this->org->Clinics)) {
            return $this->org->Clinics[0]->ClinicID;
        }
    }
    private function setCurrentClinicID($ClinicID) {
        $_SESSION["CLINIC"] = $ClinicID;
    }
    
    
    public function save() {
        $pin = filter_input(INPUT_POST,'id');
        
        $elems = explode('.', $pin);
        $org = $elems[0];
        $id = $elems[1];
        $sticky = $elems[2];

        $newlate = filter_input(INPUT_POST,'value');
        
        $manual = 1;
        Practitioner::getInstance($org,$id)->updateLateness($newlate, $sticky, $manual);
        echo $newlate;
    }
    public function savechk() {
        $pin = filter_input(INPUT_POST,'id');
        $elems = explode('.', $pin);
        $org = $elems[0];
        $id = $elems[1];
        $sticky = filter_input(INPUT_POST,'sticky');
        $newlate = filter_input(INPUT_POST,'late');
        
        $manual = 1;
        Practitioner::getInstance($org,$id)->updateLateness($newlate, $sticky, $manual);
        echo $sticky;
    }
    
   
    public function show_lateness_form() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance('Main');
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $clause = "OrgID ='" . $this->org->OrgID . "' AND ClinicID = $this->currentClinic";
        $xcrud->table('vwLateness')->where($clause);
        $xcrud->columns('OrgID,ID,FullName,DateCreated,OrgName,ClinicID,ClinicName,MinutesLateMsg,AllowMessage,Subdomain,Updated,LatenessCeiling,LateToNearest',true);
        $xcrud->unset_add( true )->unset_csv()->unset_pagination()->unset_limitlist()->unset_search()->unset_print()->unset_numbers()->unset_title();
        $xcrud->column_pattern('MinutesLate',"<span type='text' class='form-control edit notwide' id='{OrgID}.{ID}.{Sticky}'>{value}</span>");

        $xcrud->column_pattern('LatenessOffset',"<div class='btn btn-primary btn-invite' data-invitepin='{OrgID}.{ID}' data-fullname='{FullName}'>Send SMS Invitation</span>");

        $xcrud->change_type('Sticky', 'select', '', array('0'=>'', '1'=>'checked'));
        $xcrud->column_pattern('Sticky',"<input type='checkbox' class='chekbox' name='{OrgID}.{ID}.{Sticky}' id='{OrgID}.{ID}.{Sticky}' {value}></input>");
        echo $xcrud->render();
        
    }
    
    public function invite() {

        $pin = filter_input(INPUT_POST,'invitepin');
        $udid = filter_input(INPUT_POST,'udid');

        if (!$pin or !$udid)
            throw new Exception("Parameters not supplied.");
        
        list($org,$id) = explode('.',$pin);
        Device::register($org,$id,$udid);
        Device::invite($org, $id, $udid, __DOMAIN);
 
        $this->index();
    }
    
    ///
    /// Put together the clinics dropdown
    ///
    public function get_clinic_options_DeleteMe() {
        $i = 0;
        foreach ($this->org->ActiveClinics as $value) {
            echo "<option value='" . $value->ClinicID . "' ";
            if ($value->ClinicID == $this->currentClinic) {
                echo "selected";
            }
            echo ">$value->ClinicName</option>";
        }
    }
    
    public function setclinic() {
        $selectedclinic = filter_input(INPUT_GET,'clinic');
        $this->setCurrentClinicID($selectedclinic);
        $this->index();
    }
    
}
