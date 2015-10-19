<?php

Class TranLogController Extends baseController {

    private $submenu = array ("tranlog/translog"=>"Trans Log","tranlog/smslog"=>"SMS Log","tranlog/dayslog"=>"Today's Log");
    
    public function index() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "tranlog/translog";
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getWeeksLog();
        $this->registry->template->show('tranlog_index');
    }
    
    public function smslog() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "tranlog/smslog";
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getSMSLog();
        $this->registry->template->show('tranlog_index');
    }
    public function dayslog() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "tranlog/dayslog";
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getDaysLog();
        $this->registry->template->show('tranlog_index');
    }
    
    
    public function getWeeksLog() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('vwWeeksLog')->table_name('Transaction Log',"This week's log shown in latest first order.  See CSV export button at end.")->where('OrgID =', $this->Organisation->OrgID)->limit(50);
        $xcrud->order_by('Id','desc');        
        $xcrud->columns('OrgID', true);
        $xcrud->pass_default('OrgID', $this->Organisation->OrgID);
        $xcrud->unset_edit()->unset_remove()->unset_add();
        $xcrud->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        return $xcrud->render();
    }
    public function getSMSLog() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('sentsms')->table_name('SMS Log',"The SMS messages in latest first order.  See CSV export button at end.")->where('OrgID =', $this->Organisation->OrgID)->limit(50);
        $xcrud->order_by('Created','desc');        
        $xcrud->unset_edit()->unset_remove()->unset_add();
        $xcrud->columns(array('API','OrgID','MessageID','SessionID'),true);
        

        $xcrud->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        return $xcrud->render();
    }
    public function getDaysLog() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('vwDaysLog')->table_name("Today's log","All logs for the current day in forward time order.");
        $xcrud->where('OrgID =', $this->Organisation->OrgID)->limit(50)->where("not(TransType = 'LATE_UPD' and Late = 0)");
        $xcrud->order_by('ClinicName,FullName,Timestamp','asc');        
        $xcrud->unset_edit()->unset_remove()->unset_add();

        
        $xcrud->columns(array('OrgID','OrgName'),true);
     
        $xcrud->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        return $xcrud->render();
    }

}
?>
