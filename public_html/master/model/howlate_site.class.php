<?php

class howlate_site {

    // To delete a subdomain again you must delete the organisation record,
    // the subdirectory and also delete the subdomain using cpanel
    //
    public $CompanyName;
    public $Email;
    public $OrgID;
    public $Subdomain;
    public $PrivateArea;   // a hashed folder location for organisation's logos icons and style sheets
    
    
    protected $base_path = '/home/howlate/public_html';
    protected $template_path = '/home/howlate/public_html/master';

    private $username = "howlate";
    private $password = "PzaLQiH9av";
    private $udomain = __DOMAIN;
        
    protected $org;  // will hold organisation() object
    
    protected $db;

    public $Result;
    
    function __construct($co,$email)
    {
        $this->CompanyName = $co;
        $this->Email = $email;        
        $this->db = new howlate_db();
        include_once('includes/xmlapi-php-master/xmlapi.php');
    }
    
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

    public function checkForDupe() {
        $org = $this->db->getOrganisation($this->Subdomain);

        while (!empty($org)) {
            $this->mylog("$org->OrgName already exists with this subdomain<br>");          
            if (is_numeric(substr($this->Subdomain, -1))) {
                $digit = substr($this->Subdomain, -1) + 1;
                $this->Subdomain = substr($this->Subdomain, 0, strlen($this->Subdomain) - 1) . $digit;
            } else {
                $this->Subdomain = $this->Subdomain . "1";
            }

            $this->mylog("Already exists, changing subdomain to $this->Subdomain <br>");
            $org = $this->db->getOrganisation($this->Subdomain);
        }
        return $this;
    }
    
    public function createPrivateArea() 
    {
        $this->PrivateArea = $this->template_path . '/pri/' . $this->Subdomain;
        if (file_exists($this->PrivateArea)) {
            $this->mylog("Private area $this->PrivateArea exists already<br>");
        } else {
            mkdir($this->PrivateArea);
            $this->mylog("Private area $this->PrivateArea created<br>");
        }
        return $this;
    }
    
    public function createCPanelSubdomain() 
    {
        $xmlapi = new xmlapi('localhost');
        $xmlapi->password_auth(howlate_util::cpanelUser(), howlate_util::cpanelPassword());
        $xmlapi->set_output("xml");
        $xmlapi->set_protocol("http");
        $xmlapi->set_debug(1);
        
        $domain = $this->Subdomain . "." . $this->udomain;
        $this->mylog("Creating subdomain for $domain");
        $result = $xmlapi->api2_query('howlate', "SubDomain","addsubdomain", array('domain'=>$domain, 'dir'=>"/public_html/master", 'rootdomain'=>"how-late.com"));
        $this->mylog($result);
        return $this;
    }
    
    public function createInCPanel_Obsolete() {

        $authstr = howlate_util::$cpanelUser . ":" . howlate_util::$cpanelPassword;
        $pass = base64_encode($authstr);
        $ustring = $this->Subdomain;

        $socket2 = fsockopen($this->udomain, 2082);
        if (!$socket2) {
            trigger_error('Socket error trying to connect to cpanel to create the subdomain', E_USER_ERROR);
            return false;
        }

        $hasbeencreated = "has been created!";
        $alreadyexists = "already exists";
        $accessdenied = "Access denied";
        $indom = "GET /frontend/x3/subdomain/doadddomain.html?domain=$this->Subdomain&rootdomain=$this->udomain&dir=public_html%2Fmaster\r\n HTTP/1.0\r\nHost:$this->udomain\r\nAuthorization: Basic $pass\r\n\r\n";
        fputs($socket2, $indom);
        while (!feof($socket2)) {
            $buf = fgets($socket2, 128);
            $ret[] = $buf;
            if (strpos($buf, $hasbeencreated)) {    //  SUCCESS!!
                fclose($socket2);
                return $this;
            }
            if (strpos($buf, $alreadyexists)) {
                fclose($socket2);
                $this->mylog('Cpanel Subdomain already exists: <b>' . $indom . '</b>');
                return $this;
            }
            if (strpos($buf, $accessdenied)) {
                fclose($socket2);
                $this->mylog('Cpanel access denied: <b>' . $indom . '</b>');
               return $this;
            }

        }
        fclose($socket2);
        $this->mylog("Error creating cPanel subdomain, error=" + var_dump($ret));
        
        return $this;
    }
    
  
    public function createOrgRecord() {
        $this->OrgID = $this->db->getNextOrgID();
        $this->mylog("Using new OrgId = $this->OrgID <br>");
        $this->db->create_org($this->OrgID, $this->CompanyName, $this->CompanyName, $this->Subdomain, $this->Subdomain . "." . __DOMAIN);
        return $this;
    }
    
    public function createDefaultClinic() {
        $this->db->create_default_clinic($this->OrgID);
        $this->mylog("Created default clinic <br>");
        return $this;
    }
    
    public function createDefaultPractitioner() {
        $this->db->create_default_practitioner($this->OrgID, $this->Email);
        $this->mylog("Created default practitioner <br>");        
        return $this;
    }
    
    public function createDefaultUser() {
        $this->db->create_user($this->OrgID, $this->Email, $this->Email);
        $this->mylog("Created default user <b>$this->Email</b>");
        return $this;
    }

    public function sendWelcomeEmail() {
        
        $this->org = new organisation();
        $this->org->getby($this->Subdomain, 'Subdomain'); 
        
        $users = $this->db->getallusers($this->Email, 'EmailAddress');
        if (count($users) == 0) {
            $this->mylog("error, no users with email $this->Email so no welcome email sent!");
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
            $token = $this->db->save_reset_token($user->UserID, $this->Email, $user->OrgID);
            $link = "http://" . $user->FQDN . "/reset?token=$token" . "\r\n";
            $body .= $link . "\r\n";
            $this->mylog("Password reset link generated: $link");
        }

        $body .= "If you did not send this request, you can safely ignore this email.\r\n";

        $headers = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\n";
        $headers .= "From: $from";

        $mail = new mailer();
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
        $xmlapi->password_auth(howlate_util::cpanelUser(), howlate_util::cpanelPassword());
        $xmlapi->set_output("xml");
        $xmlapi->set_protocol("http");
        $xmlapi->set_debug(1);
        
        $domain = $this->Subdomain . "." . $this->udomain;
        $this->mylog("Installing certificate for $domain");
        $crt = howlate_util::getSSLCertificate();
        $result = $xmlapi->api2_query('howlate', "SSL", "installssl", array('domain'=>$domain,'crt'=>$crt));
        $this->mylog($result);
    } 
    
}
?>

