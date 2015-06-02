<?php

Class ClinController Extends baseController {

    public $org;

    public function index() {
        //$this->org = organisation::getInstance(__SUBDOMAIN);

        $this->registry->template->controller = $this;
        $this->registry->template->xcrud_content = $this->getAll();
        $this->registry->template->show('clin_index');
    }
    
    
     public function getAll() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('clinicintegration')->table_name('Clinics and Integration',"See CSV export button at end.")->limit(50);
        $xcrud->join('ClinicID','clinics','ClinicID');
        $xcrud->columns('clinics.ClinicName,OrgID,AgentVersion,RunningSince,AgentVersionTarget');
        //$xcrud->columns('Instance,PMSystem,SelectLates,SelectSessions,SelectToNotify,PollInterval,DbName,UID,PWD,ProcessRecalls', true);
        $xcrud->fields('AgentVersion,AgentVersionTarget');
        
        return $xcrud->render();
    }   
    
}

?>