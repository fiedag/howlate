<?php

Class mainController Extends baseController {

    public $org;

    public function index() {
        if (!isset($this->org)) {
            $this->org = new organisation();
            $this->org->getby(__SUBDOMAIN, "Subdomain");
            $this->registry->template->companyname = $this->org->OrgName;
            $this->registry->template->logourl = $this->org->LogoURL;
        }
        $this->registry->template->controller = $this;
        $this->registry->template->show('main_index');
    }

    public function save() {
        $db = new howlate_db();
        foreach ($_POST['lateness'] as $pin => $newlate) {
            if (!isset($newlate) or !is_numeric($newlate)) {
                continue;
            }
            $elems = split('\.', $pin);
            $org = $elems[0];
            $id = $elems[1];
            $db->updatelateness($org, $id, $newlate);
        }


        $this->index();
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
        echo "<th>How Late</th>";
        echo "</tr>";
        // dodgy this for now
        $currentClinic = 1;

        // returns a ONE dimensional array
        $lates = $this->org->getLatenesses($currentClinic);
        $max = 360;
        foreach($lates as $clinic => $latepract) {
            foreach($latepract as $key => $value) {
                $pin = $this->org->OrgID . "." . $value->ID;
                echo "<tr>";
                    echo "<td>" . $value->AbbrevName . "</td>";
                    echo "<td>";
                        echo "<input type='number' class='lateness-admin-entry' name='lateness[$pin]' list='valid_latenesses' value='$value->MinutesLate' >";
                        echo "<input type='submit' class='lateness-admin-save-button' id='save' name='Save' value='Save' />";
                    echo "</td>";
                echo "</tr>";
                
            }
            
        }
        echo "</table>";
        echo '</form>';
    }

    private function show_practitioner_row($prac) {
        echo "<tr>";
        echo "<td>$prac->PractitionerName</td>";
        echo "<td><input type='number' name='lateness' min='0' max='360' step='15'></td>";
        echo "</tr>";
    }

    public function get_valid_lateness_datalist() {
        echo "<datalist id='valid_latenesses'>";
        echo "<option value='0'>";
        echo "<option value='5'>";
        echo "<option value='10'>";
        echo "<option value='15'>";
        echo "<option value='20'>";
        echo "<option value='30'>";
        echo "<option value='45'>";
        echo "<option value='60'>";
        echo "<option value='90'>";
        echo "<option value='120'>";
        echo "<option value='150'>";
        echo "<option value='180'>";
        echo "<option value='240'>";
        echo "</datalist>";
    }
}
