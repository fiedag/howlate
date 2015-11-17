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
        $this->Organisation->getRelated();
        $ClinicID = $this->getCurrentClinicID();
        
        $clin = Clinic::getInstance($this->Organisation->OrgID, $ClinicID);
        
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
        if(isset($this->Organisation->ActiveClinics) && count($this->Organisation->ActiveClinics)>0) {
            return $this->Organisation->ActiveClinics[0]->ClinicID;
        }
        if(isset($this->Organisation->Clinics)) {
            return $this->Organisation->Clinics[0]->ClinicID;
        }
        return '-1';
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
        $override = filter_input(INPUT_POST,'override');
        $newlate = filter_input(INPUT_POST,'late');
        
        $manual = 1;
        $ret = Practitioner::getInstance($org,$id)->updateLateness($newlate, $override, $manual);
    }
    
   
    public function show_lateness_form() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance('Main');
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $clause = "OrgID ='" . $this->Organisation->OrgID . "' AND ClinicID = $this->currentClinic";
        $xcrud->table('vwLateness')->where($clause);
        $xcrud->columns('OrgID,ID,FullName,DateCreated,OrgName,ClinicID,ClinicName,MinutesLateMsg,Subdomain,Updated,LatenessCeiling,LateToNearest',true);
        $xcrud->unset_add( true )->unset_csv()->unset_pagination()->unset_limitlist()->unset_search()->unset_print()->unset_numbers()->unset_title();
        $xcrud->column_pattern('MinutesLate',"<span type='text' class='form-control edit notwide' id='{OrgID}.{ID}.{Override}'>{value}</span>");

        $xcrud->column_pattern('LatenessOffset',"<div class='btn btn-primary btn-invite' data-invitepin='{OrgID}.{ID}' data-fullname='{FullName}'>Send SMS Invitation</span>");

        $xcrud->change_type('Override', 'select', '', array('0'=>'', '1'=>'checked'));
        $xcrud->column_pattern('Override',"<input type='checkbox' class='chekbox' name='{OrgID}.{ID}.{Override}' id='{OrgID}.{ID}.{Override}' {value}></input>");
        echo $xcrud->render();
        
    }
    
    public function invite() {

        $pin = filter_input(INPUT_POST,'modal-invitepin');
        $udid = filter_input(INPUT_POST,'udid');

        if (!$pin or !$udid) {
            throw new Exception("Parameters not supplied.");
        }
        list($org,$id) = explode('.',$pin);
        Device::register($org,$id,$udid);
        Device::invite($org, $id, $udid, __DOMAIN);
 
        $this->index();
    }
    
    public function setclinic() {
        $selectedclinic = filter_input(INPUT_GET,'clinic');
        $this->setCurrentClinicID($selectedclinic);
        $this->index();
    }
    
    public function agentindicator() {
        $clinicid = filter_input(INPUT_GET,'clinicid');
        if(!isset($clinicid)) {
            return 0;
        }
        $clin = Clinic::getInstance($this->Organisation->OrgID, $clinicid);
        $ret = $clin->lastAgentUpdate();
        
        echo $ret;
    }
    
    public function get_help() {
        $this->registry->template->show('main_help');
        
    }
}
