<?php

Class meddir_agentController Extends baseController {

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
        
        $this->registry->template->show('genie_agent_index');       
    }
    
    public function get_subheader() {
        $this->registry->template->show('subheader_view');
    }
    
}

?>
