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
        $this->org->send_reset_emails($email);
    }

}

?>
