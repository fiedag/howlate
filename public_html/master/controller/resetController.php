<?php

Class resetController Extends baseController {
    public $org;
    

    private function getorg() {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, 'Subdomain');
        $this->registry->template->org = $this->org;
        $this->registry->template->controller = $this;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);
    }
    public function index() {
        
        $this->getorg();
        
        if (!isset($_GET["token"])) {
            $this->registry->template->login_info = "Bad link";
            $this->registry->template->show('reset_index');
        } else {
            $token = $_GET["token"];
            $db = new howlate_db();
            $result = $db->check_token($token, $this->org->OrgID);
            if ($result[0] == "OK") { 
                $this->registry->template->token = $token;
                $this->registry->template->userid = $result[1];
                $this->registry->template->login_info = "Please enter a new password";
                
                $this->registry->template->show_form = '<script>$("#notification_page").hide();$("#notification_page_button").hide();</script>';
                
            } else {
                $this->registry->template->login_info = $result[0];
                $this->registry->template->no_access = 1;
                $this->registry->template->notification_header = "You are not allowed to access what you are looking for.";
            }
            $this->registry->template->show("reset_index");         
            
        }
    }

    private function check_token($token) {
        $db = new howlate_db();
        $result = $db->check_token($token, $this->org->OrgID);
        if ($result[0] == "OK") {
            return "Please enter a new password";
        }
        else {
            return $result[0];
        }
    }
    
    public function change() {
        $password = $_POST["password"];
        $password2 = $_POST["password2"];
        $userid = $_POST["userid"];
        $token = $_POST["token"];
        //echo "[$password, $password2, $userid, $token]";
        $err = "";
        if ($password != $password2) {
            $err .= "Passwords do not match";
        }
        if (strlen($password) < 6) {
            $err .= (($err=="")?"Password":",") . " is too short";
        }
        if( !preg_match("#[0-9]+#", $password) or !preg_match("#[a-zA-Z]+#", $password)) {
            $err .= (($err=="")?"Password":" and") . " must include at least one number and one letter";
        }

        $this->getorg();
        
        if ($err == "") {
            $xpassword = md5($password);
            $db = new howlate_db();
            $db->change_password($userid, $xpassword, $this->org->OrgID);
            $this->registry->template->no_access = 1;
            $this->registry->template->login_info = "Password changed.";
            $this->registry->template->notification_header = "Success!  Password has been changed.  Click to go to the login page.";
            $this->registry->template->show("reset_index");
            
        }
        else { 

            $this->registry->template->password_message = $err;

            $this->registry->template->token = $token;
            $this->registry->template->userid = $userid;
        
            $this->registry->template->no_access = 0;

            $this->registry->template->login_info = "Please enter a new password";
            $this->registry->template->show("reset_index");  
           
        }

    }

}

?>