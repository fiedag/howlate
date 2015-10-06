<?php

/* 
 * Because this is always called from m.how-late.com
 * the organisation $this->org is undefined
 * 
 * 
 * 
 */

// debugging with Kint

Class SignupController Extends baseController {
    
    public function index() {
        header("Access-Control-Allow-Origin: http://how-late.com");
        $this->registry->template->controller = $this;
        $this->registry->template->logourl = HowLate_Util::logoURL();
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
        $this->registry->template->logourl = HowLate_Util::logoURL();
        $howlate_site = new HowLate_Site($company,$email);


        
        $this->registry->template->signup_error = "";
        $this->registry->template->signup_result = "Your signup was successful.  Please check your email for a link to the login page.";
        
        try {
            $howlate_site->reduceName()->checkForDupe()->createCPanelSubdomain()->installSSL();
            $howlate_site->createOrgRecord()->createDefaultClinic()->createDefaultUser();
            $howlate_site->sendWelcomeEmail();
            

            $administrator = "61403569377";
            $smstext = "New Signup : " . $company . ", email = " . $email;
            
            howlate_sms::httpSend('CCCTV', $administrator, $smstext);
            
        } catch (Exception $exception) {
            require_once("includes/kint/Kint.class.php");

            d($exception);
            $this->registry->template->signup_error = $howlate_site->Result;
            $this->registry->template->signup_result = "Your signup was not successful.  We have logged the error and will contact you shortly.  There is no need to repeat the signup process.";
            $ip = $_SERVER["REMOTE_ADDR"];
            Logging::write_error(0, 1, $exception->getMessage(), $exception->getFile(), $exception->getLine(), $ip, $exception->getTraceAsString());
                    
        }
        Logging::trlog(TranType::MISC_MISC, $howlate_site->Result);
        $this->registry->template->show('signup_done');

    }


    public function done() {
        $this->registry->template->controller = $this;
        $this->registry->template->logourl = HowLate_Util::logoURL();
        $this->registry->template->signup_error = "";
        $this->registry->template->signup_result = "Your signup was successful.  Please check your email for a link to the login page.";
        $this->registry->template->show('signup_done');
    }
    public function notdone() {
        $this->registry->template->controller = $this;
        $this->registry->template->logourl = HowLate_Util::logoURL();
        $this->registry->template->signup_error = "signup-error";
        $this->registry->template->signup_result = "Your signup was not successful.  We have logged the error and will contact you shortly.  There is no need to repeat the signup process.";
        $this->registry->template->show('signup_done');
    }
    
    
    public function contact() {
        
        $email = filter_input(INPUT_POST,"email");
        $name = filter_input(INPUT_POST,"name");
        $mobile = filter_input(INPUT_POST,"mobile");
        
        $this->registry->template->controller = $this;
        $administrator = "61403569377";
        $smstext = "Contact request: $name, $email, $mobile";
        
        $orgid = "CCCTV";
        $clickatell = new Clickatell();
        //$clickatell->httpSend($administrator, $smstext, $orgid);
        
        $note = "Someone has requested contact.  Name = $name, Mobile = $mobile, Email = $email";
        $mailer = new Howlate_Mailer();
        $mailer->send(HowLate_Util::admin_email(),'Administrator', 'A contact request has been received', $note, 'admin@how-late.com', 'Administrator');
 
        
        if(!$name) {
            $name = $email;
        }
        $note = '{ "item_type": "person", "name": "' . $name . '", "email": "' . $email . '", "visible_to": "1", "owner": "alex@fiedlerconsulting.com.au" }';
        $mailer->send('1.362238@dropbox.pipedrive.com','Administrator', 'A contact request has been received', $note, 'admin@how-late.com', 'Administrator');
        
        
        $this->registry->template->logourl = HowLate_Util::logoURL();
        $this->registry->template->url = "https://how-late.com/master";
        
        ob_start();
        $this->registry->template->show('signup_thanks');
        $body = ob_get_contents();
        ob_end_clean();
        
        $mailer->sendHtml($email, $name, 'Thank you', $body, 'info@how-late.com','How-late.com');
       
        header("location: http://how-late.com");  
    }        
}