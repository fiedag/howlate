<?php

Class loginController Extends baseController {

    public $org;

    public function index() { 
        if (isset($_COOKIE["USER"])){ 
            $this->registry->template->usercookie = $_COOKIE["USER"];
        }
        if (isset($_COOKIE["ORGID"])){ 
            $this->registry->template->orgidcookie = $_COOKIE["ORGID"];
        }
        
        if (isset($_COOKIE["USER"]) and isset($_COOKIE["ORGID"])) {
            // get some info from the cookie

            session_start();

            $_SESSION["ORGID"] = $_COOKIE["ORGID"];
            $_SESSION["USER"] = $_COOKIE["USER"];
            $_SESSION['LAST_ACTIVITY'] = time();
            if (isset($_COOKIE["URL"]) and $_COOKIE["URL"] != "login") {
                
                //header("location: " . $_COOKIE["URL"]);
            }
        }
        define("__DIAG",1);
        
        
        $this->org = new organisation();
        
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);
        $this->registry->template->show('login_index');
    }

    public function fail() {
        $this->registry->template->password_incorrect = 1;
        $this->index();
    }

    public function attempt() {

        $userid = $_POST["username"];
        $passwd = md5($_POST["password"]);

        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);

        //echo md5($userid);
        if ($this->org->isValidPassword($userid, $passwd)) {
            setcookie("USER", $userid, time() + 3600);
            setcookie("ORGID", $this->org->OrgID, time() + 3600);
            
            session_start();
            
            $_SESSION["ORGID"] = $this->org->OrgID;
            $_SESSION["USER"] = $userid;
            $_SESSION['LAST_ACTIVITY'] = time();
            header("location: http://" . __SUBDOMAIN . "." . __DOMAIN . "/main");
        } else {
            header("location: http://" . __SUBDOMAIN . "." . __DOMAIN . "/login/fail");
        }
    }

    public function forgot() {
        $email = $_POST["email"];
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->send_reset_emails($email);

        header("location: http://" . __SUBDOMAIN . "." . __DOMAIN . "/login?sent=ok");

    }

    private function send_reset_emails($email) {
        $db = new howlate_db();
        $users = $db->getallusers($email, 'EmailAddress');
        if (count($users) == 0) {
            return 0;
        }
        
        
        $subject = "Trouble logging in? Your username and password for " . $this->org->OrgName;

        $body = "";
        if (count($users) > 1) {
            $body = "It looks like you have " . count($users) . " different logins for " . $this->org->OrgName . "'s secure online services.\r\n\r\n";
            $body .= "-------- User Accounts ---------\r\n\r\n";
        }
        
        $toName = $users[0]->FullName;
        $from = $users[0]->EmailAddress;
        $fromName = $this->org->OrgName;
        
        foreach ($users as $user) {
            $body .= "Username: " . $user->UserID . "\r\n";
            $body .= "If you have forgotten your password, you can reset it by following this link:\r\n";
            $token = $db->save_reset_token($user->UserID, $email, $user->OrgID);
            $link = "http://" . $user->FQDN . "/reset?token=$token" . "\r\n";
            $body .= $link . "\r\n";
        }

        $body .= "Afterwards, you can securely access your account by going to your login page:\r\n\r\n";
        $body .= "http://" . __SUBDOMAIN . "." . __DOMAIN . "\r\n\r\n";
        $body .= "If you did not send this request, you can safely ignore this email.\r\n";

        $headers = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\n";
        $headers .= "From: $from";

        $mail = new mailer();
        $mail->send($email,$toName, $subject,$body, $from, $fromName);
        
        
    }

}

?>
