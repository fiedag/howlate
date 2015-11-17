<?php

Class AgentController Extends baseController {

    public $currentClinic;
    public $currentSystem;
    public $currentUserID;

    private $submenu = array ("agent"=>"Agent","sessions"=>"Sessions","appttype"=>"Appt Type","apptstatus"=>"Appt Status");
    
    public function index() {
        if (isset($_SESSION["CLINIC"]) ) {
           $this->currentClinic = $_SESSION['CLINIC'];
        } else {
            $this->Organisation->getRelated();
            $this->currentClinic = $this->Organisation->Clinics[0]->ClinicID;        
        }
        $this->registry->template->controller = $this;
        $this->clinicselect($this->currentClinic); 
    }

    public function clinicselect($clinicID = null) {
        // one of the post parameters was the selected clinic
        // so retrieve that.

        $this->Organisation->getRelated();
        $this->registry->template->controller = $this;
    
        if (is_null($clinicID)) {
            $this->currentClinic = filter_input(INPUT_POST, "ClinicID");
        } else {
            $this->currentClinic = $clinicID;
        }

        $result = Clinic::getInstance($this->Organisation->OrgID,$this->currentClinic)->getClinicIntegration();
        if (is_null($result)) {
            echo "creating clinic integration record!!!" . $this->Organisation->OrgID . ", $this->currentClinic";
            Clinic::getInstance($this->Organisation->OrgID,$this->currentClinic)->createClinicIntegration();
            $result = Clinic::getInstance($this->Organisation->OrgID,$this->currentClinic)->getClinicIntegration();
        }
        
        $this->registry->template->ClinicID = $result->ClinicID;
        $this->registry->template->PMSystem = $result->PMSystem;
        $this->registry->template->ConnectionType = $result->ConnectionType;
        $this->registry->template->ConnectionString = $result->ConnectionString;       
        $this->registry->template->PollInterval = $result->PollInterval;        
        $this->registry->template->HLUserID = $result->HLUserID;
        
        $this->registry->template->show('agent_index');       
    }
    
    public function update() {

        $OrgID = filter_input(INPUT_POST,"OrgID");
        $ClinicID = filter_input(INPUT_POST,"ClinicID");
        $_SESSION['CLINIC'] = $ClinicID;
        $URL = "https://" . __FQDN . "/api?ver=post";
        $PollInterval = filter_input(INPUT_POST, "PollInterval");  
        $HLUserID = filter_input(INPUT_POST, "HLUserID");  
        
        $PMSystem = filter_input(INPUT_POST, "PMSystem");
        $ConnectionType = filter_input(INPUT_POST,"ConnectionType");
        $ConnectionString = filter_input(INPUT_POST,"ConnectionString");
        
        $clin = Clinic::getInstance($OrgID,$ClinicID);
        $clin->updateClinicIntegration2($PollInterval, $HLUserID, $PMSystem, $ConnectionType, $ConnectionString);
        
        $action = filter_input(INPUT_POST,"action");
        if ($action == 'download') {
            $this->download_config($clin);
        }
        else {
            $this->index();
        }
        
    }

    private function download_config($clin) {
        // this will initiate a download of HowLateAgent.exe.config
        $result = agent::getInstance($this->Organisation->OrgID, $clin->ClinicID);
        
        $this->registry->template->record = $result;
        $this->registry->template->URL = "https://" . __FQDN . "/api";
        $this->registry->template->Credentials = $result->HLUserID . "," . $result->XPassword;
        
        $this->registry->template->show('agent_config'); 
        
    }
    
    public function further() {
        $this->Organisation = Organisation::getInstance(__SUBDOMAIN);
        $this->registry->template->controller = $this;
        $this->registry->template->show('agent_further');
    }

    private function exe_info() {
        // this will initiate a download of HowLateAgent.exe
        $this->Organisation = Organisation::getInstance(__SUBDOMAIN);
        if (isset($_SESSION["CLINIC"]) ) {
           $this->currentClinic = $_SESSION['CLINIC'];
        } else {
            $this->currentClinic = $this->Organisation->Clinics[0]->ClinicID;        
        }
        $result = Clinic::getInstance($this->Organisation->OrgID,$this->currentClinic)->getClinicIntegration();

        
        return $result;
        
    }
    
    public function exe() {
        if (isset($_SESSION["CLINIC"]) ) {
           $this->currentClinic = $_SESSION['CLINIC'];
        } else {
            $this->currentClinic = $this->Organisation->Clinics[0]->ClinicID;        
        }

        $agent = agent::getInstance($this->Organisation->OrgID, $this->currentClinic);
        $agent->get_exe();
    }
    
    public function install() {
        $this->Organisation = Organisation::getInstance(__SUBDOMAIN);
        if (isset($_SESSION["CLINIC"]) ) {
           $this->currentClinic = $_SESSION['CLINIC'];
        } else {
            $this->currentClinic = $this->Organisation->Clinics[0]->ClinicID;        
        }
        $result = Clinic::getInstance($this->Organisation->OrgID,$this->currentClinic)->getClinicIntegration();
        
        $this->registry->template->interface = $result->Name;
        $this->registry->template->show('agent_install');
    }

    public function get_clinic_options($clinicid) {
        $i = 0;
        foreach ($this->Organisation->Clinics as $value) {
            echo "<option value='" . $value->ClinicID . "' ";
            if ($value->ClinicID == $clinicid) {
                echo "selected";
            }
            echo ">$value->ClinicName</option>";
        }
    }

    
    public function get_system_options($system) {
        $i = 0;
        $systems = PmSystem::getAllImplemented();
        
        foreach ($systems as $value) {
            echo "<option value='" . $value->ID . "' ";
            if ($value->ID == $system) {
                echo "selected";
            }
            echo ">$value->Name</option>";
        }
    }
    
    public function get_user_options($userid) {
        $i = 0;
        $users = Organisation::findUsers($this->Organisation->OrgID);
        
        
        foreach ($users as $value) {
            echo "<option value='" . $value->UserID . "' ";
            if ($value->UserID == $userid) {
                echo "selected";
            }
            echo ">$value->FullName</option>";
        }
    }
    
    public function get_connection_options($connectiontype) {
            
            echo "<option value='ODBC DSN' " .  (($connectiontype=="ODBC DSN")?"selected":"") . ">ODBC DSN</option>";
            echo "<option value='Sql Native Client' " .  (($connectiontype=="Sql Native Client")?"selected":"") . ">Sql Native Client</option>";
    }    
    
    
    public function systemselect($systemID = null) {
        // one of the post parameters was the selected clinic
        // so retrieve that.

        $this->Organisation->getRelated();
        $this->registry->template->controller = $this;
    
        if (is_null($systemID)) {
            $this->currentSystem = filter_input(INPUT_POST, "System");
        } else {
            $this->currentSystem = $clinicID;
        }

        $result = PmSystem::getInstance($this->currentSystem);
        
        $this->registry->template->subdomain = __SUBDOMAIN;
        $this->registry->template->clinic = $this->currentClinic;
        $this->registry->template->instance = $result->Instance;
        $this->registry->template->database = $result->DbName;
        $this->registry->template->uid = $result->UID;
        $this->registry->template->pwd = $result->PWD;
        $this->registry->template->interval = $result->PollInterval;        
        $this->registry->template->hluserid = $result->HLUserID;
     
        $this->registry->template->show('agent_index');       
    }
    
    /*
     * Shows the second level of menu options below the integration menu option
     * 
     * 
     */
    public function get_subheader() {
        $this->registry->template->show('subheader_view');
    }

    public function get_submenu() {
        
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "agent";
        $this->registry->template->show('submenu_view');
    }
    
    
    
}

?>
