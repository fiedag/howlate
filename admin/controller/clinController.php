<?php

Class ClinController Extends baseController {

    public $org;

    public function index() {
        //$this->org = organisation::getInstance(__SUBDOMAIN);

        $this->registry->template->controller = $this;
        $this->registry->template->xcrud_content = $this->getAll();
        $this->registry->template->show('clin_index');
    }
    public function diag() {
        $this->registry->template->controller = $this;

         $this->registry->template->diag_content = "diagnostic content";
        $this->registry->template->show('clin_diag');
        
    }
    
     public function getAll() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('clinics')->table_name('Clinics',"clinics table.")->limit(50);
        $xcrud->columns("ClinicID,ClinicName,Timezone,OrgID,SuppressNotifications,ApptLogging",false);
        $xcrud->column_pattern('ClinicID', $this->assignSpan());
        $xcrud->fields('OrgID',true);
        return $xcrud->render();
    }   

    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to get diagnostics...' onClick=\"openDiag('{ClinicID}');\">{ClinicID}</span>";
        return $span;
    }
           
    
    
    

}

?>