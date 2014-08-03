<?php

Class placeController Extends baseController {

    public $org;

    private function cmp($a, $b) {
        return strcmp($a->ClinicPlaced, $b->ClinicPlaced);
    }

    public function index() {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");

        usort($this->org->Practitioners, array($this, "cmp"));

        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;

        $this->registry->template->controller = $this;


        $this->registry->template->show('place_head');

        $this->registry->template->show('place_clinic');

        $this->registry->template->show('place_foot');
    }

    public function save2() {
        $db = new howlate_db();
        foreach ($_POST['lateness'] as $pin => $newlate) {
            if (!isset($newlate) or (!is_numeric($newlate) and $newlate != 'On time') or ($newlate == $_POST['oldlateness'][$pin])) {
                continue;
            }
            if ($newlate == 'On time') {
                $newlate = 0;
            }
            $elems = explode('.', $pin);
            $org = $elems[0];
            $id = $elems[1];
            $db->updatelateness($org, $id, $newlate);
        }

        //$this->index();
        header("location: http://" . __FQDN . "/place?ok=yes");
    }

    public function save() {
        $result = $_REQUEST["table-1"];
        foreach ($result as $value) {
            echo "$value<br/>";
        }
    }
    
}
