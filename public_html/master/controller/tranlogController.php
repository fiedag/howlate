<?php

Class tranlogController Extends baseController {

    public $org;

    public function index() {
      
	$this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);

	$this->registry->template->controller = $this;
       
        $this->registry->template->show('tranlog_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlDb());
        
        $xcrud->table('vwWeeksLog')->table_name('Transaction Log',"This week's log shown in latest first order.  See CSV export button at end.")->where('OrgID =', $this->org->OrgID)->limit(50);
        $xcrud->order_by('Id','desc');        
        $xcrud->columns('OrgID', true);
        $xcrud->pass_default('OrgID', $this->org->OrgID);
        //$xcrud->hide_button('view');
      
        $xcrud->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');     
        echo $xcrud->render();
    }

}
?>
