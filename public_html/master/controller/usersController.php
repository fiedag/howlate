<?php

Class usersController Extends baseController {

    public $org;

    public function index() {
      
	$this->org = organisation::getInstance(__SUBDOMAIN);
	$this->registry->template->controller = $this;
        $this->registry->template->show('users_index');
    }
    
    
    public function passwordreset() {
        $resetemail = filter_input(INPUT_POST, "resetemail");
	$this->org = organisation::getInstance(__SUBDOMAIN);
        $this->org->getRelated();
        $this->org->SendResetEmails($resetemail);
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->controller = $this;
        $this->registry->template->show('users_index');
        
        
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlDb());
        
        $xcrud->table('orgusers')->where('OrgID =', $this->org->OrgID)->limit(10)->table_name('Users Table','You can add or delete as many users as you want');
        $xcrud->columns('OrgID,DateCreated,SecretQuestion1,SecretAnswer1', true);
        $xcrud->readonly('OrgID');
        $xcrud->fields('XPassword,SecretQuestion1,SecretAnswer1,DateCreated', true);
        
        $xcrud->label(array('UserID' => 'User ID', 'EmailAddress' => 'Email', 'FullName' => 'Name', 'XPassword'=>'Reset Password'));

        $xcrud->pass_default('OrgID',$this->org->OrgID);     
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->unset_view(true);     
        
        $xcrud->column_pattern('XPassword', $this->assignSpan());  // display the assignment button

        echo $xcrud->render();
    }
    
    
    
    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to reset the password for this user...' onClick=\"gotoReset('{OrgID}','{UserID}','{EmailAddress}');\">Reset Password</span>";
        return $span;
    }
}
?>