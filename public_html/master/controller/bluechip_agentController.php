<?php

Class bluechip_agentController Extends baseController {

    public $currentClinic;

    public function index() {
        if (isset($_SESSION["CLINIC"]) ) {
           $this->currentClinic = $_SESSION['CLINIC'];
        } else {
            $this->currentClinic = $this->org->Clinics[0]->ClinicID;        
        }
        $this->registry->template->controller = $this;
        $this->clinicselect($this->currentClinic); 
    }

    public function clinicselect($clinicID = null) {
        // one of the post parameters was the selected clinic
        // so retrieve that.

        $this->org->getRelated();
        $this->registry->template->controller = $this;
    
        if (is_null($clinicID)) {
            $this->currentClinic = filter_input(INPUT_POST, "Clinic");
        } else {
            $this->currentClinic = $clinicID;
        }

        $result = clinic::getInstance($this->org->OrgID,$this->currentClinic)->getClinicIntegration();
        if (is_null($result)) {
            clinic::getInstance($this->org->OrgID,$this->currentClinic)->createClinicIntegration();
            $result = clinic::getInstance($this->org->OrgID,$this->currentClinic)->getClinicIntegration();

        }
        
        $this->registry->template->subdomain = __SUBDOMAIN;
        $this->registry->template->clinic = $this->currentClinic;
        $this->registry->template->instance = $result->Instance;
        $this->registry->template->database = $result->DbName;
        $this->registry->template->uid = $result->UID;
        $this->registry->template->pwd = $result->PWD;
        $this->registry->template->interval = $result->PollInterval;        
        $this->registry->template->hluserid = $result->HLUserID;
        
        $this->registry->template->show('bps_agent_index');       
    }
    
    public function update() {

        // this will initiate a download of HowLateAgent.exe.config

        $orgid = filter_input(INPUT_POST,"OrgID");
        $clinic = filter_input(INPUT_POST,"ClinicID");
        $instance = filter_input(INPUT_POST,"Instance");
        $database = filter_input(INPUT_POST, "Database");
        $uid = filter_input(INPUT_POST, "UID");
        $pwd = filter_input(INPUT_POST, "PWD");
        $interval = filter_input(INPUT_POST, "Interval");  
        $hluserid = filter_input(INPUT_POST, "HLUserID");  

        clinic::getInstance($orgid,$clinic)->updateClinicIntegration($instance, $database, $uid, $pwd, $interval, $hluserid);
        
        $this->registry->template->subdomain = __SUBDOMAIN;
        $this->registry->template->clinic = $clinic;
        $this->registry->template->instance = $instance;
        $this->registry->template->database = $database;
        $this->registry->template->uid = $uid;
        $this->registry->template->pwd = $pwd;
        $this->registry->template->interval = $interval;
        
        $url = "https://" . __SUBDOMAIN . "." . __DOMAIN . "/api?ver=post";
        $this->registry->template->url = $url;
        
        $res = orguser::getInstance($orgid, $hluserid);

        $this->registry->template->credentials = $hluserid . "." . $res->XPassword;
        
        //$this->registry->template->show('bps_agent_config');
    }
 
    public function further() {
        $this->registry->template->controller = $this;
        $this->registry->template->show('bps_agent_further');
    }

    public function exe() {

        // this will initiate a download of HowLateAgent.exe
        $this->registry->template->controller = $this;
        $this->registry->template->show('bps_agent_exe');
    }

    public function install() {
        $this->registry->template->show('bps_agent_install');
    }

    public function get_clinic_options() {
        $i = 0;
        foreach ($this->org->ActiveClinics as $value) {
            echo "<option value='" . $value->ClinicID . "' ";
            if ($value->ClinicID == $this->currentClinic) {
                echo "selected";
            }
            echo ">$value->ClinicName</option>";
        }
    }

    /*
     * Shows the second level of menu options below the integration menu option
     * 
     * 
     */
    public function get_subheader() {
        $this->registry->template->show('subheader_view');
    }
    
}

?>
