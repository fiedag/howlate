<?php

class HowLate_Site {

    // To delete a subdomain again you must delete the organisation record,
    // the subdirectory and also delete the subdomain using cpanel
    //
    public $CompanyName;
    public $Email;
    public $OrgID;
    public $Subdomain;
    public $PrivateArea;   // a hashed folder location for organisation's logos icons and style sheets
    
    
    protected $base_path;
    protected $template_path;

    protected $DefaultUser;
        
    protected $org;  // will hold organisation() object
    protected $db;
    public $Result;
    
    function __construct($co = '',$email = '')
    {
        $this->base_path = HowLate_Util::basePath();
        $this->template_path = HowLate_Util::masterPath();
        $this->CompanyName = $co;
        $this->Email = $email;        
        $this->db = new howlate_db();
        // the xmlapi is the API to interact with cpanel and
        // thereby create subdomains and apply certificates
        include_once('includes/xmlapi-php-master/xmlapi.php');
    }

    // abbreviate names where possible and make unique
    public function reduceName() {
        // check in database and set if valid, else exception

        $res = preg_replace("/(doctor([s])*( )*Surgery)$/i", "ds", $this->CompanyName);
        $res = preg_replace("/(medical( )*Cent(er|re))$/i", "mc", $res);
        $res = preg_replace("/(medical( )*Clinic)$/i", "mc", $res);
        $res = preg_replace("/(emergency( )*(Dept|Department))$/i", "ed", $res);
        $res = preg_replace("/( )+/", "", $res);
        $res = strtolower($res);
        $res = preg_replace("/[^a-zA-Z0-9]/", "", $res);
        $res = preg_replace("/-/", "", $res);
        $this->Subdomain = $res;
        return $this;
    }

    // check for duplicates and append number to make unique
    public function checkForDupe() {
        $org = Organisation::getInstance($this->Subdomain);

        while (!empty($org)) {
            $this->mylog("$org->OrgName already exists with this subdomain<br>");          
            if (is_numeric(substr($this->Subdomain, -1))) {
                $digit = substr($this->Subdomain, -1) + 1;
                $this->Subdomain = substr($this->Subdomain, 0, strlen($this->Subdomain) - 1) . $digit;
            } else {
                $this->Subdomain = $this->Subdomain . "1";
            }

            $this->mylog("Already exists, changing subdomain to $this->Subdomain <br>");
            $org = Organisation::getInstance($this->Subdomain);
        }
        return $this;
    }
    
    public function createPrivateArea() 
    {
        $this->PrivateArea = $this->template_path . '/pri/' . $this->Subdomain;
        if (file_exists($this->PrivateArea)) {
            $this->mylog("Private area $this->PrivateArea exists already<br>");
        } else {
            if(!file_exists($this->template_path . '/pri')) {
                mkdir($this->template_path . '/pri');
            }
            mkdir($this->PrivateArea);
            $this->mylog("Private area $this->PrivateArea created<br>");
        }
        return $this;
    }
    
    public function createCPanelSubdomain() 
    {
        $xmlapi = new xmlapi('localhost');
        $xmlapi->password_auth(HowLate_Util::cpanelUser(), HowLate_Util::cpanelPassword());
        $xmlapi->set_output("xml");
        $xmlapi->set_protocol("http");
        $xmlapi->set_debug(1);
        
        $subd = $this->Subdomain . "." . __DOMAIN;
        $this->mylog("Creating subdomain for $subd");
        $result = $xmlapi->api2_query('howlate', "SubDomain","addsubdomain", array('domain'=>$subd, 'dir'=>"/public_html/master", 'rootdomain'=>__DOMAIN));
        $this->mylog($result);
        return $this;
    }
    
    public function createOrgRecord() {
        $this->OrgID = Organisation::getNextOrgID();
        $this->mylog("Using new OrgId = $this->OrgID <br>");
        
        $this->org = Organisation::createOrg($this->OrgID, $this->CompanyName, $this->CompanyName, $this->Subdomain, $this->Email, $this->Subdomain . "." . __DOMAIN);
        return $this;
    }
    
    public function createDefaultClinic() {
        Clinic::createDefaultClinic($this->OrgID);
        $this->mylog("Created default clinic for $this->OrgID <br>");
        return $this;
    }
    
    
    public function createDefaultPractitioner() {
        $this->mylog("Creating default practitioner for $this->OrgID, and email $this->Email<br>");
        Practitioner::createDefaultPractitioner($this->OrgID, 0, $this->Email);
        $this->mylog("Created default practitioner <br>");        
        return $this;
    }
    
    public function createDefaultUser() {
        $this->mylog("Creating default user for $this->OrgID, and email $this->Email<br>");
        $this->DefaultUser = OrgUser::createUser($this->OrgID, $this->Email, $this->Email);
        $this->mylog("Created default user <b>$this->DefaultUser</b>");
        return $this;
    }

    public function sendWelcomeEmail() {
        $users = Organisation::findUsers($this->Email, 'EmailAddress');
        if (count($users) == 0) {
            $this->mylog("Error, no users with email $this->Email so no welcome email sent!");
            return false;
        }
        $subject = "Welcome to how-late.com! Your login link is enclosed.";

        $body = "";
        if (count($users) > 1) {
            $body = "It looks like you have " . count($users) . " different logins for " . $this->org->OrgName . "'s secure online services.\r\n\r\n";
            $body .= "-------- User Accounts ---------\r\n\r\n";
        }
        $from =  $users[0]->EmailAddress;
        $fromName = $this->org->OrgName;
        
        foreach ($users as $user) {
            $body .= "Username: " . $user->UserID . "\r\n";
            $body .= "Please sign in by following this link:\r\n";
            $token = Organisation::saveResetToken($user->OrgID, $user->UserID, $this->Email);
            $link = "http://" . $user->FQDN . "/reset?token=$token" . "\r\n";
            $body .= $link . "\r\n";
            $this->mylog("Password reset link generated: $link");
            $terms_url = "https://" . $user->FQDN . "/terms";
        }

        
        $body .= "You may not use the Service if you do not accept the terms.  By logging in with the enclosed link, you are ";
        $body .= "indicating that you have read and accepted the Terms of Service at " . $terms_url;
        $body .= "\r\n\r\n";
        
        $body .= "If you did not send this request, you can safely ignore this email.\r\n";

        $headers = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\n";
        $headers .= "From: $from";

        $mail = new Howlate_Mailer();
        $mail->send($this->Email,$this->Email, $subject,$body, $from, $fromName);   
        $this->mylog("Welcome email sent to $this->Email");
        return $this;
    }

    private function mylog($msg)
    {
        $this->Result .= $msg . "<br>";
    }
    
    
    public function installSSL() {

        $xmlapi = new xmlapi('localhost');
        $xmlapi->password_auth(HowLate_Util::cpanelUser(), HowLate_Util::cpanelPassword());
        $xmlapi->set_output("xml");
        $xmlapi->set_protocol("http");
        $xmlapi->set_debug(1);
        
        $domain = $this->Subdomain . "." . __DOMAIN;
        $this->mylog("Installing certificate for $domain");
        $crt = HowLate_Util::getSSLCertificate();
        $result = $xmlapi->api2_query('howlate', "SSL", "installssl", array('domain'=>$domain,'crt'=>$crt));
        $this->mylog($result);
    } 
    
    public function deleteCPanelSubdomain($subd) {
        $xmlapi = new xmlapi('localhost');
        $xmlapi->password_auth(HowLate_Util::cpanelUser(), HowLate_Util::cpanelPassword());
        $xmlapi->set_output("xml");
        $xmlapi->set_protocol("http");
        $xmlapi->set_debug(1);

        $this->mylog("Deleting subdomain for $subd");
        $result = $xmlapi->api2_query('howlate', "SubDomain", "delsubdomain", array('domain' => $subd, 'dir' => "/public_html/master", 'rootdomain' => __DOMAIN));
        $this->mylog($result);
        return $this;
    }

    
//    public function deldomain()
//    {
//        $subdomain = filter_input(INPUT_GET, "subdomain");
//        
//        $howlate_site = new howlate_site('','');
//        $howlate_site->deleteCPanelSubdomain($subdomain);
//        echo "Subdomain $subdomain deleted.";
//        
//        $db = new howlate_db();
//        $db->deleteSubdomain($subdomain);
//        echo "Org $subdomain deleted.";
//    }
//    
    
    
    
}
?>

