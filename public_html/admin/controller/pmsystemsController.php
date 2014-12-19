<?php

Class pmsystemsController Extends baseController {

    public $org;

    public function index() {

        $this->registry->template->controller = $this;
        $this->registry->template->xcrud_content = $this->getAll();
        $this->registry->template->show('pmsystems_index');
    }
    
    
     public function getAll() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlDb());
        $xcrud->table('pmsystems')->table_name('Practice Management Systems',"See CSV export button at end.")->limit(10);
        $xcrud->order_by('Priority','desc');        
        $xcrud->change_type('SelectLates,SelectToNotify,SelectSessions', 'texteditor');
        return $xcrud->render();
    }   
    
    
}

?>