<?php

Class loginController Extends baseController {

	public function index() 
	{
            $org = new organisation();
            $org->getby(__SUBDOMAIN, "Subdomain");
            $this->registry->template->companyname = $org->OrgName;
            $this->registry->template->logourl = $org->LogoURL;
            $this->registry->template->show('login_index');
	}
        
        public function fail() 
        {
            $this->registry->template->failmessage = "Incorrect username or password.  Please try again.";
            $this->index();
        }
        
        public function attempt() {
            
            $userid = $_POST["username"];
            $passwd = md5($_POST["password"]);

            $org = new organisation();
            $org->getby(__SUBDOMAIN, "Subdomain");
            $this->registry->template->companyname = $org->OrgName;
            $this->registry->template->logourl = $org->LogoURL;
            //echo md5($userid);
            if ($org->isValidPassword($userid, $passwd)) {
                session_start();
                $_SESSION["ORGID"] = $org->OrgID;
                $_SESSION["USER"] = $userid;
                $_SESSION['LAST_ACTIVITY'] = time(); 
                header("location: http://" . __SUBDOMAIN . ".how-late.com/main");
            }
            else {
                 header("location: http://" . __SUBDOMAIN . ".how-late.com/login/fail");
            }
        }
}
?>
