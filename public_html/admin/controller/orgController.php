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
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('orgs')->table_name('Organisations',"See CSV export button at end.")->limit(50);
        $xcrud->order_by('Created','desc');        
        $xcrud->column_pattern('FQDN', $this->assignSpan());  // display the assignment button
        
        return $xcrud->render();
    }   

    
    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to go to site...' onClick=\"gotoSite('https://{FQDN}');\">Site</span>";
        return $span;
    }    
    
}

?>