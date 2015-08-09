<?php

Class BillingController Extends baseController {

    private $submenu = array ("clinics"=>"Clinics","unbilledsms" => "Unbilled SMS",'tools' =>'Tools');
    
    public $clinic;
    public $package;
    public $package_line;
    
    public function index() {
	$this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getClinics();
        $this->registry->template->show('billing_index');
    }

    
    
    public function unbilledsms() {
	$this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getUnbilledSMS();
        $this->registry->template->show('billing_index');
    }

    public function tools() {
	$this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->show('billing_tools');
    }
    

    public function adjust() {

        $this->registry->template->controller = $this;
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');

        $OrgID = filter_input(INPUT_GET,'org');
        if(!$OrgID) {
            throw new Exception('Must enter an org parameter e.g. AAAHH');
        }
        
        $org = Organisation::getInstance($OrgID,'OrgID');
        $org->getRelated();
        $billing = new Billing();
        $billing->adjustPackage($org);
        
        $this->registry->template->show('billing_tools');
        
        
    }
    
    
    public function delete_co_cust() {
        $CustomerID = filter_input(INPUT_POST,'CustomerID');
        if(!$CustomerID) {
            throw new Exception('Must enter a Customer ID e.g. 17');
        }
        $billing = new Billing();
        $billing->deleteCustomer($CustomerID);
        
        $this->tools();
    }
    
    private function getClinics() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('clinics')->table_name('Clinics',"All clinics and billing links.  See CSV export button at end.")->limit(50);
        $xcrud->columns('OrgID, ClinicID,ClinicName,LatLong,Address1,Address2,City,State');
        $xcrud->column_pattern('ClinicID', $this->assignSpan()); 
        return $xcrud->render();
    }
    
    private function getUnbilledSMS() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('vwUnbilledSMS')->table_name('vwUnbilledSMS',"Not billed so far")->limit(50);
        return $xcrud->render();
    }
    
    private function assignSpan() {
        $span = "<a class='xcrud-button' href=\"/billing/clinic?org={OrgID}&clin={ClinicID}\">{ClinicID}</span>";
        return $span;
    }
    
    public function clinic() {
        require_once("includes/kint/Kint.class.php");
        $this->registry->template->controller = $this;

        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        
        $OrgID = filter_input(INPUT_GET,"org");
        $ClinicID = filter_input(INPUT_GET,"clin");
        $this->clinic = Clinic::getInstance($OrgID, $ClinicID);
        
        $this->registry->template->xcrud_content = $this->getAll($ClinicID);
        
        $billing = new Billing();
        $this->package = $billing->getChargeoverPackage($OrgID);

        $this->registry->template->show('billing_clinic');
    }
     
    private function getAll($ClinicID) {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlDb());
        $xcrud->table('vwBillingClinPract')->table_name('Billing info for clinics',"See CSV export button at end.");
        $xcrud->where("ClinicID = " . $ClinicID);
        return $xcrud->render();
    }   
    
    public function packageline() {
        
        $OrgID = filter_input(INPUT_POST,"OrgID");
        $descrip = filter_input(INPUT_POST,"descrip");
        $package_id = filter_input(INPUT_POST,"package_id");
        $line_item_id = filter_input(INPUT_POST,"line_item_id");
        $item_quantity = filter_input(INPUT_POST,"item_quantity");
        $external_key = filter_input(INPUT_POST,"external_key");
        $billing = new Billing();
        
        $response = $billing->upgradePackageLine($package_id, $line_item_id, $descrip, $external_key, $item_quantity);
        
        $this->index();
    }
    
    
    
    
}
?>
