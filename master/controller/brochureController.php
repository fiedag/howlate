<?php

Class BrochureController Extends baseController {
    public $currentClinic;
    public $currentClinicName;        
    public $pract;
    
    public function index() {
        
        if (isset($_SESSION["CLINIC"])) {
            $this->currentClinic = $_SESSION["CLINIC"];
            
        }
        if (isset($_SESSION["CLINICNAME"])) {
            $this->currentClinicName = $_SESSION["CLINICNAME"];
        }
        
        $lates = $this->Organisation->getLatenesses($this->currentClinic);
        
        $this->pract = reset($lates);
        if(count($this->pract) == 1) {
            $this->registry->template->entry_instruction = "Enter the following code";
        }
        else
            $this->registry->template->entry_instruction = "Enter one of the following codes";
        
        
        $this->registry->template->icon_url = HowLate_Util::logoURL(__SUBDOMAIN);
        $this->registry->template->blog_heading = 'This is the blog Index';
        
        $this->registry->template->controller = $this;
        
        $this->registry->template->show('brochure_index');
    }

}

?>
