<?php

/* 
 * Because this is always called from secure.how-late.com
 * the organisation $this->org is undefined
 * 
 * 
 * 
 */

// debugging with Kint

Class signupController Extends baseController {
    
    public function index() {
        header("Access-Control-Allow-Origin: http://how-late.com");
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
        header("Access-Control-Allow-Origin: http://how-late.com");
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


        $clickatell = new clickatell();        
        
        $this->registry->template->signup_error = "";
        $this->registry->template->signup_result = "Your signup was successful.  Please check your email for a link to the login page.";
        
        try {
            $howlate_site->reduceName()->checkForDupe()->createCPanelSubdomain()->installSSL();
            $howlate_site->createOrgRecord()->createDefaultClinic()->createDefaultPractitioner()->createDefaultUser();
            $howlate_site->sendWelcomeEmail();
            

            $administrator = "61403569377";
            $smstext = "New Signup : " . $company . ", email = " . $email;
            $clickatell->httpSend( $administrator, $smstext, $howlate_site->OrgID);
        } catch (Exception $exception) {
            require_once("includes/kint/Kint.class.php");

            d($exception);
            $this->registry->template->signup_error = $howlate_site->Result;
            $this->registry->template->signup_result = "Your signup was not successful.  We have logged the error and will contact you shortly.  There is no need to repeat the signup process.";
            $ip = $_SERVER["REMOTE_ADDR"];
            logging::write_error(0, 1, $exception->getMessage(), $exception->getFile(), $exception->getLine(), $ip, $exception->getTraceAsString());
                    
        }
        logging::trlog(TranType::MISC_MISC, $howlate_site->Result);
        $this->registry->template->show('signup_done');

    }


    public function done() {
        $this->registry->template->controller = $this;
        $this->registry->template->logourl = howlate_util::logoURL();
        $this->registry->template->signup_error = "";
        $this->registry->template->signup_result = "Your signup was successful.  Please check your email for a link to the login page.";
        $this->registry->template->show('signup_done');
    }
    public function notdone() {
        $this->registry->template->controller = $this;
        $this->registry->template->logourl = howlate_util::logoURL();
        $this->registry->template->signup_error = "signup-error";
        $this->registry->template->signup_result = "Your signup was not successful.  We have logged the error and will contact you shortly.  There is no need to repeat the signup process.";
        $this->registry->template->show('signup_done');
    }
    
}
?>
