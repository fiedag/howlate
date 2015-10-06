<?php

Class UsersController Extends baseController {

    public $org;

    public function index() {
      
	$this->org = Organisation::getInstance(__SUBDOMAIN);
	$this->registry->template->controller = $this;
        $this->registry->template->show('users_index');
    }
    
    
    public function passwordreset_DeleteMe() {
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
        
        $xcrud->table('orgusers')->where('OrgID =', $this->org->OrgID)->limit(10)->table_name('Users Table','You can add or delete as many users as you want');
        $xcrud->columns('OrgID,DateCreated,SecretQuestion1,SecretAnswer1,XPassword', true);
        $xcrud->readonly('OrgID');
        $xcrud->fields('SecretAnswer1,DateCreated', true);
        
        $xcrud->label(array('UserID' => 'User ID', 'EmailAddress' => 'Email', 'FullName' => 'Name', 'XPassword'=>'Password','SecretQuestion1'=>'Confirm'));

        $xcrud->pass_default('OrgID',$this->org->OrgID);     
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->unset_view(true);     
        $xcrud->change_type('XPassword', 'password', 'md5', 20);
        $xcrud->change_type('SecretQuestion1', 'password', 'md5', 20);
        
        echo $xcrud->render();
    }
    
    private function assignSpan_DeleteMe() {
        $span = "<span class='xcrud-button' title='Click to reset the password for this user...' onClick=\"gotoReset('{OrgID}','{UserID}','{EmailAddress}');\">Reset Password</span>";
        return $span;
    }
}
?>