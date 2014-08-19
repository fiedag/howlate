<?php

Class agentController Extends baseController {

    public $org;
    public $currentClinic;

    public function index() {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;


        if (isset($_SESSION["CLINIC"])) {

            $this->currentClinic = $_SESSION['CLINIC'];
            $this->currentClinicName = $_SESSION['CLINICNAME'];
            $this->currentClinicTimezone = $_SESSION['CLINICTZ'];
        } else {
            $this->currentClinic = $this->org->Clinics[0]->ClinicID;
            $this->currentClinicName = $this->org->Clinics[0]->ClinicName;
            $this->currentClinicTimezone = $this->org->Clinics[0]->Timezone;
        }

        $this->registry->template->controller = $this;
        $this->registry->template->show('agent_index');
    }

    public function exe() {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");

        // this will initiate a download of HowLateAgent.exe
        $this->registry->template->show('agent_exe');
    }

    public function config() {

        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = "What the Flick";

        $this->registry->template->logourl = $this->org->LogoURL;

        $this->registry->template->controller = $this;

        $this->registry->template->show('agent_index');
    }

    public function update() {

        // this will initiate a download of HowLateAgent.exe.config
        if (!isset($_SESSION["USER"])) {
            trigger_error("User session variable not defined.", E_USER_ERROR);
        }
        if (!isset($_SESSION["ORGID"])) {
            trigger_error("Org ID variable not defined.", E_USER_ERROR);
        }

        $userid = $_SESSION["USER"];
        $orgid = $_SESSION["ORGID"];

        $db = new howlate_db();
        $res = $db->get_user_data($userid, $orgid);

        $this->registry->template->subdomain = __SUBDOMAIN;
        $this->registry->template->clinic = $_POST["Clinic"];
        $this->registry->template->instance = $_POST["Instance"];
        $this->registry->template->database = $_POST["Database"];
        $this->registry->template->uid = $_POST["UID"];
        $this->registry->template->pwd = $_POST["PWD"];
        $this->registry->template->url = "https://" . __SUBDOMAIN . ".how-late.com/api?met=upd&amp;ver=post";
        $this->registry->template->credentials = $userid . "." . $res->XPassword;


        $this->registry->template->show('agent_config');
    }

    public function update_hide() {
        $db = new howlate_db();
        $db->update_org($org);

        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;

        $this->registry->template->controller = $this;
        $this->registry->template->show('agent_index');
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
