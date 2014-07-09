<?php

class howlate_site {

    // To delete a subdomain again you must delete the organisation record,
    // the subdirectory and also delete the subdomain using cpanel
    //
    
    protected $company;
    protected $email;
    protected $base_path = '/home/howlate/public_html';
    protected $template_path = '/home/howlate/public_html/master';
    protected $private_area;   // a hashed folder location for organisation's logos icons and style sheets
    protected $subdomain;

    protected $org;
    
    
    public function create($company, $email) {
        $this->email = $email;
        $this->company = $company;
        howlate_util::diag("in function create(). Company = $this->company<br>");
        $this->subdomain = $this->reduceName($this->company);
        howlate_util::diag("Name reduced to $this->subdomain<br>");

        $db = new howlate_db();

        $org = $db->getOrganisation($this->subdomain);

        while (!empty($org)) {
            var_dump($org);
            if (is_numeric(substr($this->subdomain, -1))) {
                $digit = substr($this->subdomain, -1) + 1;
                $this->subdomain = substr($this->subdomain, 0, strlen($this->subdomain) - 1) . $digit;
            } else {
                $this->subdomain = $this->subdomain . "1";
            }

            howlate_util::diag("Already exists, changing subdomain to $this->subdomain <br>");
            $org = $db->getOrganisation($this->subdomain);
        }

        howlate_util::diag("Subdomain $this->subdomain is available in the database, using it...<br>");

        $this->private_area = $this->template_path . '/pri/' . $this->subdomain;
        if (file_exists($this->private_area)) {
            howlate_util::diag("Private area $this->private_area exists already<br>");
        } else {
            mkdir($this->private_area);
            howlate_util::diag("Private area $this->private_area created<br>");
        }

        howlate_util::diag("Creating subdomain in cpanel, please wait about 1 minute...<br>");
        $this->create_in_cpanel($this->subdomain);
        howlate_util::diag("Cpanel subdomain has been created, creating org in database<br>");

        $orgid = $db->getNextOrgID();
        howlate_util::diag("Using new OrgId = $orgid <br>");

        $db->create_org($orgid, $this->company, $this->company, $this->subdomain, $this->subdomain . "." . __DOMAIN);
        $db->create_default_clinic($orgid);
        $db->create_default_practitioner($orgid, $this->email);
        $db->create_user($orgid, $this->email, $this->email);

        $this->send_welcome_email($this->email);
        
        howlate_util::diag("Created Organisation, $this->company <br>");

        howlate_util::diag("Welcome to Please check your email at $this->email for further information on logging in<br>");

        return $this->subdomain;
    }

    protected function reduceName($company) {
        // check in database and set if valid, else exception

        $res = preg_replace("/(doctor([s])*( )*Surgery)$/i", "ds", $company);
        $res = preg_replace("/(medical( )*Cent(er|re))$/i", "mc", $res);
        $res = preg_replace("/(medical( )*Clinic)$/i", "mc", $res);
        $res = preg_replace("/(emergency( )*(Dept|Department))$/i", "ed", $res);
        $res = preg_replace("/( )+/", "", $res);
        $res = strtolower($res);
        $res = preg_replace("/[^a-zA-Z0-9]/", "", $res);
        $res = preg_replace("/-/", "", $res);

        return $res;
    }

    protected function create_in_cpanel($subdomain) {
        $username = "howlate";
        $password = "3134-5Q^hP$1";
        $udomain = "how-late.com";
        $authstr = "$username:$password";
        $pass = base64_encode($authstr);
        $ustring = $subdomain;

        $socket2 = fsockopen("how-late.com", 2082);
        if (!$socket2) {
            trigger_error('Socket error trying to connect to cpanel to create the subdomain', E_USER_ERROR);
            return false;
        }

        $hasbeencreated = "has been created!";
        $alreadyexists = "already exists";
        $indom = "GET /frontend/x3/subdomain/doadddomain.html?domain=$subdomain&rootdomain=$udomain&dir=public_html%2Fmaster\r\n HTTP/1.0\r\nHost:$udomain\r\nAuthorization: Basic $pass\r\n\r\n";
        fputs($socket2, $indom);
        while (!feof($socket2)) {
            $buf = fgets($socket2, 128);
            $ret[] = $buf;
            if (strpos($buf, $hasbeencreated)) {
                fclose($socket2);
                return true;
            }
            if (strpos($buf, $alreadyexists)) {
                fclose($socket2);
                trigger_error('Cpanel Subdomain already exists: <b>' . $indom . '</b>', E_USER_ERROR);
                return false;
            }
        }
        fclose($socket2);
        return false;
    }

    protected function send_welcome_email($email) {
        
        $this->org = new organisation();
        $this->org->getby($this->subdomain, 'Subdomain'); 
        
        $db = new howlate_db();
        $users = $db->getallusers($email, 'EmailAddress');
        if (count($users) == 0) {
            return 0;
        }
        $subject = "Welcome to how-late.com! Your login link is enclosed.";

        $body = "";
        if (count($users) > 1) {
            $body = "It looks like you have " . count($users) . " different logins for " . $this->org->OrgName . "'s secure online services.\r\n\r\n";
            $body .= "-------- User Accounts ---------\r\n\r\n";
        }
        $from = $this->org->OrgName . "<" . $users[0]->EmailAddress . ">";

        foreach ($users as $user) {
            $body .= "Username: " . $user->UserID . "\r\n";
            $body .= "Please sign in by following this link:\r\n";
            $token = $db->save_reset_token($user->UserID, $email, $user->OrgID);
            $link = "http://" . $user->FQDN . "/reset?token=$token" . "\r\n";
            $body .= $link . "\r\n";
        }

        $body .= "If you did not send this request, you can safely ignore this email.\r\n";

        $headers = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\n";
        $headers .= "From: $from";
        if (mail($email, $subject, $body, $headers)) {
            $success = "true";
        }
    }

}
?>

