<?php

Class sessionsController Extends baseController {

    public $org;
    
    private $submenu = array ("agent"=>"Agent","sessions"=>"Sessions");
        
    public function index() {
      
	$this->org = organisation::getInstance(__SUBDOMAIN);
        $this->registry->template->companyname = $this->org->OrgName;
	$this->registry->template->controller = $this;
        $this->registry->template->show('sessions_index');
		
    }
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlDb());
        
        $xcrud->table('sessions')->table_name('Sessions from the Practice Mgt System','Updated by the HL Agent')->where('OrgID =', $this->org->OrgID)->limit(30);
        
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');
        echo $xcrud->render();
    }

    
    public function get_submenu() {
        
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = "sessions";
        $this->registry->template->show('submenu_view');
    }

}
?>
