<?php

Class orgController Extends baseController {

    public $org;

    public function index() {
        //$this->org = organisation::getInstance(__SUBDOMAIN);

        $this->registry->template->controller = $this;
        $this->registry->template->xcrud_content = $this->getAll();
        $this->registry->template->show('org_index');
    }
    
    
     public function getAll() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlDb());
        $xcrud->table('orgs')->table_name('Organisations',"See CSV export button at end.")->limit(50);
        $xcrud->order_by('Created','desc');        
        
        return $xcrud->render();
    }   
    
    
}

?>