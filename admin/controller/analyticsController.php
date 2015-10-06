<?php

Class AnalyticsController Extends baseController {

    private $submenu = array ("orgdevices"=>"Org Devices",
        "devicesbyfreq"=>"Multi-Org Devices",
        "practdevices"=>"Practitioner Devices",
        "toplategets"=>"Top Late Gets",
        "freqrecipients"=>"Frequent Recipients"
     );
    
    public function index() {
	$this->orgdevices();
    }
    
    public function orgdevices() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getOrgDevices();
        $this->registry->template->show('analytics_index');
    }

    public function practdevices() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getPractDevices();
        $this->registry->template->show('analytics_index');
    }
    public function toplategets() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getTopLateGets();
        $this->registry->template->show('analytics_index');
    }
    public function freqrecipients() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getFrequentRecipients();
        $this->registry->template->show('analytics_index');
    }
    public function devicesbyfreq() {
	$this->registry->template->controller = $this;
        
        $this->get_header();
        $this->registry->template->submenu = $this->submenu;
        $this->registry->template->view_name = __FUNCTION__;
        $this->registry->template->show('submenu_view');
        $this->registry->template->xcrud_content = $this->getDevicesByFreq();
        $this->registry->template->show('analytics_index');
    }
    
    
    
    public function getFrequentRecipients() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlAnalyticsDb());
        $xcrud->table('vwFrequentRecipients')->limit(50);
        $xcrud->column_pattern('UDID', $this->assignSpan());
        return $xcrud->render();
    }    
    
    public function getTopLateGets() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlAnalyticsDb());
        $xcrud->table('vwTopLateGets')->limit(50);
        $xcrud->column_pattern('UDID', $this->assignSpan());  // display the assignment button
        
        return $xcrud->render();
    }    
    
    public function getOrgDevices() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlAnalyticsDb());
        $xcrud->table('vwOrgDevices')->limit(50);
        $xcrud->order_by('NumDevices','desc');        
        return $xcrud->render();
    }

    public function getPractDevices() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlAnalyticsDb());
        $xcrud->table('vwPractitionerDevices')->limit(50);
        $xcrud->order_by('NumDevices','desc');        
        return $xcrud->render();
    }

    public function getDevicesByFreq() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlAnalyticsDb());
        $xcrud->table('vwDevices')->limit(50);
        $xcrud->where('NumOrgs > 1');
        $xcrud->order_by('NumOrgs,NumDoctors','desc');  
        $xcrud->column_pattern('UDID', $this->assignSpan());  // display the assignment button
        return $xcrud->render();
    }
    
    
    
    private function assignSpan() {
        $span = "<span class='xcrud-button' title='Click to view what this device sees...' onClick=\"openView('{UDID}');\">{UDID}</span>";
        return $span;
    }
    
}
?>
