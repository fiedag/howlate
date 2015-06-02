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
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('pmsystems')->table_name('Practice Management Systems',"See CSV export button at end.")->limit(50);
        $xcrud->order_by('Priority','desc');        
        $xcrud->columns('SelectLates,SelectSessions,SelectToNotify,Timestamp,SelectAppointments,SelectTimeNow', true);
        $xcrud->change_type('SelectLates,SelectToNotify,SelectSessions,SelectAppointments,SelectTimeNow', 'textarea');
        $xcrud->auto_xss_filtering = false; // disable all xcrud's POST and GET data filtering for this page

        return $xcrud->render();
    }    
}
?>