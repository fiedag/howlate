<?php

Class TranLogController Extends baseController {

    private $submenu = array ("translog"=>"Trans Log",
        "speciallog"=>"Special Log",
        "smslog"=>"SMS Log",
        "errorlog"=>"Errors",
        "notifqueue"=>"Notif Queue",
        "late_gets"=>"Late Gets"
     );
    
    public function index() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getWeeksLog();
        $this->registry->template->show('tranlog_index');
    }
    
    public function speciallog() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getWeeksLogSpecial();
        $this->registry->template->show('tranlog_index');
    }
    
    
    
    public function smslog() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getSMSLog();
        $this->registry->template->show('tranlog_index');
    }
   
    
    public function errorlog() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getErrorlog();
        $this->registry->template->show('tranlog_index');
    }
        
    public function notifqueue() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getNotifQueue();
        $this->registry->template->show('tranlog_index');
    }

    public function late_gets() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getLateGets();
        $this->registry->template->show('tranlog_index');
    }

    
    
    public function getWeeksLog() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('vwWeeksLog')->table_name('Transaction Log',"This week's log shown in latest first order.  See CSV export button at end.")->limit(50);
        $xcrud->order_by('Id','desc');        
        return $xcrud->render();
    }
    
    public function getWeeksLogSpecial() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('transactionlog')->where("TransType !",array('LATE_RESET','LATE_UPD','LATE_GET','MISC_MISC','SESS_UPD'))->table_name('Selected transactions',"This week's log of uncommon transactions.")->limit(50);
        $xcrud->order_by('Id','desc');        
        return $xcrud->render();
    }

    
    public function getLateGets() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('transactionlog')->where("TransType = 'LATE_GET'")->table_name('Selected late_get transactions',"All late gets")->limit(100);
        $xcrud->column_pattern('UDID', $this->assignSpan());  // display the assignment button
        $xcrud->order_by('Id','desc');        
        return $xcrud->render();
    }
    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to view what this device sees...' onClick=\"openView('{UDID}');\">{UDID}</span>";
        return $span;
    }    
    
    
    public function getSMSLog() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('sentsms')->table_name('SMS Log',"The SMS messages in latest first order.  See CSV export button at end.")->limit(50);
        $xcrud->order_by('Created','desc');        
        $xcrud->unset_edit()->unset_remove()->unset_add();
        $xcrud->columns(array('API','OrgID','MessageID','SessionID'),true);
        $xcrud->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        return $xcrud->render();
    }
    
    public function getErrorlog() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('errorlog')->table_name('Error Log',"The system exceptions and errors which have been logged.  See CSV export button at end.")->limit(50);
        $xcrud->order_by('Created','desc');        
        return $xcrud->render();
    }
    
    public function getNotifQueue() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('notifqueue')->table_name('Notification Queue',"SMS notification queue.  See CSV export button at end.")->limit(50);
        $xcrud->order_by('Created','desc');        
        return $xcrud->render();
    }
    
    
}
?>
