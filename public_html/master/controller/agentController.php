<?php

Class agentController Extends baseController {

    public $org;
    public $currentClinic;

    public function index() {
        if (isset($_SESSION["CLINIC"]) ) {
           $this->currentClinic = $_SESSION['CLINIC'];
        } else {
            $this->org = new organisation();
            $this->currentClinic = $this->org->Clinics[0]->ClinicID;        
        }
        $this->registry->template->controller = $this;
        $this->clinicselect($this->currentClinic); 
    }

    public function clinicselect($clinicID = null) {
        // one of the post parameters was the selected clinic
        // so retrieve that.

        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);
        $this->registry->template->controller = $this;
    
        if (is_null($clinicID)) {
            $this->currentClinic = filter_input(INPUT_POST, "Clinic");
        } else {
            $this->currentClinic = $clinicID;
        }
        $db = new howlate_db();
        $result = $db->getClinicIntegration($this->org->OrgID, $this->currentClinic);
        if (is_null($result)) {
            $db->createClinicIntegration($this->org->OrgID, $this->currentClinic);
            $result = $db->getClinicIntegration($this->org->OrgID, $this->currentClinic);
        }
        
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

        //echo $orgid . $clinic . $instance . $database . $uid . $pwd . $interval . $hluserid;
        
        $db = new howlate_db();
       
        $db->updateClinicIntegration($orgid, $clinic, $instance, $database, $uid, $pwd, $interval, $hluserid);
        
        
        $this->registry->template->subdomain = __SUBDOMAIN;
        $this->registry->template->clinic = $clinic;
        $this->registry->template->instance = $instance;
        $this->registry->template->database = $database;
        $this->registry->template->uid = $uid;
        $this->registry->template->pwd = $pwd;
        $this->registry->template->interval = $interval;
        
        $url = "https://" . __SUBDOMAIN . "." . __DOMAIN . "/api?ver=post";
        $this->registry->template->url = $url;
        
        $res = $db->get_user_data($hluserid, $orgid);
        $this->registry->template->credentials = $hluserid . "." . $res->XPassword;
        

        $this->registry->template->show('agent_config');
    }
    
    
    
    
    public function further() {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);

        $this->registry->template->controller = $this;
        $this->registry->template->show('agent_further');
    }

    public function exe() {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");

        // this will initiate a download of HowLateAgent.exe
        $this->registry->template->controller = $this;
        $this->registry->template->show('agent_exe');
    }

    public function update_DeleteMe() {

        // this will initiate a download of HowLateAgent.exe.config
        if (!isset($_SESSION["USER"])) {
            trigger_error("User session variable not defined.", E_USER_ERROR);
        }
        if (!isset($_SESSION["ORGID"])) {
            trigger_error("Org ID variable not defined.", E_USER_ERROR);
        }

        $userid = $_SESSION["USER"];
        $orgid = $_SESSION["ORGID"];
        

        
        $this->registry->template->subdomain = __SUBDOMAIN;
        $this->registry->template->clinic = $_POST["Clinic"];
        $this->registry->template->instance = $_POST["Instance"];
        $this->registry->template->database = $_POST["Database"];
        $this->registry->template->uid = $_POST["UID"];
        $this->registry->template->pwd = $_POST["PWD"];
        $this->registry->template->interval = $_POST["interval"];
        $this->registry->template->url = "https://" . __SUBDOMAIN . ".how-late.com/api?ver=post";
        
        $db = new howlate_db();
        $res = $db->get_user_data($userid, $orgid);
        $this->registry->template->credentials = $userid . "." . $res->XPassword;

        $this->registry->template->controller = $this;
        $this->registry->template->show('agent_config');
        
    }

    public function install() {
        $this->registry->template->show('agent_install');
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

}

?>
