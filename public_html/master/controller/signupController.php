<?php

Class signupController Extends baseController {

    public $org;

    public function index() {

        $this->registry->template->controller = $this;
        $this->registry->template->logourl = howlate_util::logoURL();
        $this->registry->template->signup_result = "";
        $this->registry->template->show('signup_view');
    }

    public function create() {
        define("__DIAG", 1);

        $this->registry->template->controller = $this;

        if (!isset($_POST["company"]) or !isset($_POST["email"])) {
            echo "Invalid parameters.";
            return;
        }

        $company = $_POST["company"];
        $email = $_POST["email"];

        $this->registry->template->CompanyName = $company;
        $this->registry->template->Email = $email;
        $this->registry->template->logourl = howlate_util::logoURL();
        $howlate_site = new howlate_site($company,$email);
        
        $howlate_site->reduceName()->checkForDupe()->createPrivateArea()->createCPanelSubdomain()->installSSL();
        $howlate_site->createOrgRecord()->createDefaultClinic()->createDefaultPractitioner()->createDefaultUser();
        $howlate_site->sendWelcomeEmail();
        
        $this->registry->template->signup_result = $howlate_site->Result;
        $this->registry->template->show('signup_view');

        
    }

}
