<?php
/* 
 * Because this is always called from secure.how-late.com
 * the organisation $this->org is undefined
 * 
 * 
 * 
 */

// debugging with Kint
require("includes/kint/Kint.class.php");

Class signupController Extends baseController {
    
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
        
        try {
            $howlate_site->reduceName()->checkForDupe()->createPrivateArea()->createCPanelSubdomain()->installSSL();

            $howlate_site->createOrgRecord()->createDefaultClinic()->createDefaultPractitioner()->createDefaultUser();

            $howlate_site->sendWelcomeEmail();
        } catch (Exception $ex) {
            d($ex);
        }

        $this->registry->template->signup_result = $howlate_site->Result;
        
        d($this);
        $this->registry->template->show('signup_view');

    }

    
}
?>
