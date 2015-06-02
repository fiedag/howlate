<?php

Class UsersController Extends baseController {

    public $org;

    public function index() {
      
	$this->org = Organisation::getInstance(__SUBDOMAIN);
	$this->registry->template->controller = $this;
        $this->registry->template->show('users_index');
    }
    
    
    public function passwordreset() {
        $resetemail = filter_input(INPUT_POST, "resetemail");
	$this->org = Organisation::getInstance(__SUBDOMAIN);
        $this->org->getRelated();
        $this->org->SendResetEmails($resetemail);
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->controller = $this;
        $this->registry->template->show('users_index');
        
        
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        
        $xcrud->table('orgusers')->table_name('Users Table','You can add or delete as many users as you want')->limit(50);
        $xcrud->columns('SecretQuestion1,SecretAnswer1', true);
        
        $xcrud->label(array('UserID' => 'User ID', 'EmailAddress' => 'Email', 'FullName' => 'Name', 'XPassword'=>'Reset Password'));

        $xcrud->column_pattern('XPassword', $this->assignSpan());  // display the assignment button

        echo $xcrud->render();
    }
    
    
    
    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to reset the password for this user...' onClick=\"gotoReset('{OrgID}','{UserID}','{EmailAddress}');\">Reset Password</span>";
        return $span;
    }
}
?>