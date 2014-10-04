<?php

Class signupController Extends baseController {

    public $org;

    public function index() {

        $this->registry->template->controller = $this;
        $this->registry->template->logourl = howlate_util::logoURL();
        $this->registry->template->signup_result = "";
        $this->registry->template->show('signup_view');
    }

    //
    // We are calling this with AJAX therefore
    // it is easier to use GET parameters
    //
    public function create() {
        define("__DIAG", 1);
        
        
        $db = new howlate_db();
        $db->trlog(TranType::SESS_UPD, "Creating site");
        
        $this->registry->template->controller = $this;

        $company = filter_input(INPUT_GET, "company");
        $email = filter_input(INPUT_GET, "email");
        if (!isset($company) or !isset($email)) {
            throw new Exception("Program called with incorrect parameters");
        }
        
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

    
    public function deldomain()
    {
        $subdomain = filter_input(INPUT_GET, "subdomain");
        
        $howlate_site = new howlate_site('','');
        $howlate_site->deleteCPanelSubdomain($subdomain);
        echo "Subdomain $subdomain deleted.";
        
        $db = new howlate_db();
        $db->deleteSubdomain($subdomain);
        echo "Org $subdomain deleted.";
    }
}
?>
