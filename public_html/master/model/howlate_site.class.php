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
        $xmlapi->password_auth(howlate_util::$cpanelUser, howlate_util::$cpanelPassword);
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
        $namepart = substr($this->Email,0,strpos($this->Email,'@'));
        if(strpos($namepart,".")) {
            $userid = substr($namepart,strpos($namepart,"."));
        }
        else
            $userid = $namepart;
        if(strlen($userid)>12) {
            $userid = substr($userid,12);
        }      
        $this->db->create_user($this->OrgID, $userid, $this->Email);
        $this->mylog("Created default user <b>$userid</b>");
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
        $xmlapi->password_auth(howlate_util::$cpanelUser, howlate_util::$cpanelPassword);
        $xmlapi->set_output("xml");
        $xmlapi->set_protocol("http");
        $xmlapi->set_debug(1);
        
        $domain = $this->Subdomain . "." . $this->udomain;
        $this->mylog("Installing certificate for $domain");
        $crt = $this->certificate();
        $result = $xmlapi->api2_query('howlate', "SSL", "installssl", array('domain'=>$domain,'crt'=>$crt));
        $this->mylog($result);
    }

    private function certificate()
    {
$crt = <<<EOD
-----BEGIN CERTIFICATE-----
MIIFUjCCBDqgAwIBAgIQOi70mLLxfvw50BzryWb//zANBgkqhkiG9w0BAQsFADCB
kDELMAkGA1UEBhMCR0IxGzAZBgNVBAgTEkdyZWF0ZXIgTWFuY2hlc3RlcjEQMA4G
A1UEBxMHU2FsZm9yZDEaMBgGA1UEChMRQ09NT0RPIENBIExpbWl0ZWQxNjA0BgNV
BAMTLUNPTU9ETyBSU0EgRG9tYWluIFZhbGlkYXRpb24gU2VjdXJlIFNlcnZlciBD
QTAeFw0xNDA3MTIwMDAwMDBaFw0xNTA3MTIyMzU5NTlaMFsxITAfBgNVBAsTGERv
bWFpbiBDb250cm9sIFZhbGlkYXRlZDEdMBsGA1UECxMUUG9zaXRpdmVTU0wgV2ls
ZGNhcmQxFzAVBgNVBAMUDiouaG93LWxhdGUuY29tMIIBIjANBgkqhkiG9w0BAQEF
AAOCAQ8AMIIBCgKCAQEA0buK3REaVs/G+894spJs8n2NPF4Y5h+NjnNkmu8Xp7DY
eswr123W9T+ZuAuwQ3Z2mS+da5g/ds2Rhx34revs2OAk8hGwBMd0GGYZv9fEfgx9
4kR8/njkQlXZQwzc0IeSpFkYD8SRmN56NrF60F7Y4QQianFx6gYeScxT/qWquAct
ZlYw/+YBS6N4PtUpC4ZpcNg2zQDc65kaeibzDJco4sOhjFU6D6V6HWhNJ7uCDIUh
HDhgsnEsQeSJvrKqv6IgdJAy2RRJO1IifBMSfPg/8REQyNTw9fpWiborX4Hmuolh
rH/1b76M/mXIV6sxeCGXoUvcu1WF9FMRtBR8z511dwIDAQABo4IB2jCCAdYwHwYD
VR0jBBgwFoAUkK9qOpRaC9iQ6hJWc99DtDoo2ucwHQYDVR0OBBYEFKuWb/2Xyx50
tRZBz4+MsuKq2NSaMA4GA1UdDwEB/wQEAwIFoDAMBgNVHRMBAf8EAjAAMB0GA1Ud
JQQWMBQGCCsGAQUFBwMBBggrBgEFBQcDAjBQBgNVHSAESTBHMDsGDCsGAQQBsjEB
AgEDBDArMCkGCCsGAQUFBwIBFh1odHRwczovL3NlY3VyZS5jb21vZG8ubmV0L0NQ
UzAIBgZngQwBAgEwVAYDVR0fBE0wSzBJoEegRYZDaHR0cDovL2NybC5jb21vZG9j
YS5jb20vQ09NT0RPUlNBRG9tYWluVmFsaWRhdGlvblNlY3VyZVNlcnZlckNBLmNy
bDCBhQYIKwYBBQUHAQEEeTB3ME8GCCsGAQUFBzAChkNodHRwOi8vY3J0LmNvbW9k
b2NhLmNvbS9DT01PRE9SU0FEb21haW5WYWxpZGF0aW9uU2VjdXJlU2VydmVyQ0Eu
Y3J0MCQGCCsGAQUFBzABhhhodHRwOi8vb2NzcC5jb21vZG9jYS5jb20wJwYDVR0R
BCAwHoIOKi5ob3ctbGF0ZS5jb22CDGhvdy1sYXRlLmNvbTANBgkqhkiG9w0BAQsF
AAOCAQEAeEOvMpPm7vCC3UI/9ekwZWrTUyCuRFlHVbFA609AHyS5lY7SxL2lGgv6
1fWyo3HjuGnI8i2J9hdL2UunIHynGGhviYMv/32/UqmpvT/QNRkEyEoI8Xd91xwv
XqenwGF4LSO0bXBfnCHdSMbd5INC0773Tlu4yor+eRVeLdob5WqaZKFRZR+69ywm
rCG64cbjMR8Z95wbEOvgxeAkewjbAk2taB7D3bBqQB+LfDJHlwdCDe814Sau8nEr
f2CVGj8R4rYgHbWf7mkn4u3oX76Q6bu4vKtQlxjALTbwP2vp5GIOI1jES3JMbD9R
Ec2JP4hnT/LFh0+kOuemIlOI+QnfHw==
-----END CERTIFICATE-----
EOD;
    return $crt;
    }
    
    
}
?>

