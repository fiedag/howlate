<?php

Class LoginController Extends baseController {

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
        
        $this->Organisation = Organisation::getInstance(__SUBDOMAIN);
        if(!isset($this->Organisation)) {
             header("location: http://" . __SUBDOMAIN . "." . __DOMAIN . "/error404");
        }
        
        $this->registry->template->companyname = $this->Organisation->OrgName;
        $this->registry->template->logourl = HowLate_Util::logoURL(__SUBDOMAIN);
        $this->registry->template->show('login_index');
    }

    public function fail() {
        $this->registry->template->password_incorrect = 1;
        $this->index();
    }

    public function attempt() {

        $userid = $_POST["username"];
        $passwd = md5($_POST["password"]);

        $this->Organisation = Organisation::getInstance(__SUBDOMAIN);
        $this->registry->template->companyname = $this->Organisation->OrgName;
        $this->registry->template->logourl = $this->Organisation->LogoURL;

        //echo md5($userid);
        if ($this->Organisation->isValidPassword($userid, $passwd)) {
            setcookie("USER", $userid, time() + 3600 * 8);
            setcookie("ORGID", $this->Organisation->OrgID, time() + 3600 * 8);
            
            session_start();
            
            $_SESSION["ORGID"] = $this->Organisation->OrgID;
            $_SESSION["USER"] = $userid;
            $_SESSION['LAST_ACTIVITY'] = time();

            header("location: https://" . __SUBDOMAIN . "." . __DOMAIN . "/main");
        } else {
            header("location: https://" . __SUBDOMAIN . "." . __DOMAIN . "/login/fail");
        }
    }

    public function forgot() {
        $email = $_POST["email"];
        $this->Organisation = Organisation::getInstance(__SUBDOMAIN);
        $this->Organisation->getRelated();
        $this->Organisation->sendResetEmails($email);
        $this->registry->template->email = $email;
        
        $this->registry->template->sentok = 1;
        $this->index();
    }

}

?>
