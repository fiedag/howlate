<?php

Class mainController Extends baseController {

    public $org;
    public $currentClinic;
    public $currentClinicName;
    

    public function index() {
        if (!isset($this->org)) {
            $this->org = new organisation();
            $this->org->getby(__SUBDOMAIN, "Subdomain");
            $this->registry->template->companyname = $this->org->OrgName;
            $this->registry->template->logourl = $this->org->LogoURL;
        }
        
        if (isset($_SESSION["clinic"])) {
            $this->currentClinic = $_SESSION["clinic"];
            $this->currentClinicName = $_SESSION["clinicname"];
        }
        else {
            $this->currentClinic = $this->org->Clinics[0]->ClinicID;
            $this->currentClinicName = $this->org->Clinics[0]->ClinicName;
            $_SESSION["clinic"] = $this->currentClinic;
            $_SESSION["clinicname"] = $this->currentClinicName;
        }
        
        $this->registry->template->controller = $this;
        $this->registry->template->show('main_index');
        
    }

    public function save() {
        $db = new howlate_db();
        foreach ($_POST['lateness'] as $pin => $newlate) {
            if (!isset($newlate) or (!is_numeric($newlate) and $newlate != 'On time') or ($newlate == $_POST['oldlateness'][$pin])) {
                continue;
            }
            if ($newlate == 'On time') {
                $newlate = 0;
            }
            $elems = split('\.', $pin);
            $org = $elems[0];
            $id = $elems[1];
            $db->updatelateness($org, $id, $newlate);
        }

        $this->index();
        //header("location: http://" . __FQDN . "/main");
    }

    public function get_header() {
        include 'controller/headerController.php';
        $header = new headerController($this->registry);
        $header->view($this->org);
    }

    public function get_footer() {
        include 'controller/footerController.php';
        $footer = new footerController($this->registry);
        $footer->view($this->org);
    }

    //
    // returns html text of a container div inside which is a lateness
    // form and additional tabs for other clinics. 
    public function show_lateness_form() {
        echo '<form name="lateness" action="/main/save" method="POST">';
        echo "<table class='lateness-admin'>";
        echo "<tr>";
        echo "<th>Practitioner</th>";
        echo "<th class='lateness-value'>How Late</th>";
        echo "<th></th>";
        echo "</tr>";
        // dodgy this for now

        // returns a ONE dimensional array
        $lates = $this->org->getLatenesses($this->currentClinic);
        $max = 360;
        foreach($lates as $clinic => $latepract) {
            foreach($latepract as $key => $value) {
                $pin = $this->org->OrgID . "." . $value->ID;
                echo "<tr>";
                    echo "<td class='col-80pct'>" . $value->AbbrevName . "</td>";
                    echo "<td class='lateness-value'>";
                        echo "<input type='number' class='lateness-admin-entry' name='lateness[$pin]' list='valid_latenesses' min='0' value='$value->MinutesLate' >";
                        echo "<input type='hidden' name='oldlateness[$pin]' value='$value->MinutesLate' >";
                    echo "</td>";
                    echo "<td>";
                        echo "<input type='submit' class='lateness-admin-save-button' id='save' name='Save' value='Save' />";
                    echo "</td>";
                echo "</tr>";
                
            }
            
        }
        echo "</table>";
        echo '</form>';
    }

    public function get_valid_lateness_datalist() {
        echo <<<EOT
<datalist id='valid_latenesses'>
<option value='0'>On time</option>
<option value='5'>5 minutes late</option>
<option value='10'>10 minutes late</option>
<option value='15'>15 minutes late</option>
<option value='20'>20 minutes late</option>
<option value='30'>30 minutes late</option>
<option value='45'>45 minutes late</option>
<option value='60'>1 hour late</option>
<option value='90'>1.5 hours late</option>
<option value='120'>2 hours late</option>
<option value='150'>2.5 hours late</option>
<option value='180'>3 hours late</option>
<option value='240'>4 hours late</option>
</datalist>
EOT;
    }
    
    
    public function show_clinic_header() {
        
        echo<<<EOT
        
        <h1>$this->currentClinicName</h1>
        
EOT;
        
    }
}
