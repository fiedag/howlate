<?php

Class lateController Extends baseController {

    public function index() {
        $this->view();
    }

    public function view() {
        $this->registry->template->refresh = 30;  // seconds
        $this->registry->template->when_refreshed = 'Updated ' . date('h:i A');
        $this->registry->template->bookmark_title = "How late";
        $this->registry->template->bookmark_url = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $this->registry->template->icon_url = howlate_util::logoURL(__SUBDOMAIN);
        if (isset($_GET['udid'])) {
            $udid = filter_input(INPUT_GET, 'udid');
            $this->registry->template->UDID = $udid;
            $db = new howlate_db();
            $lates = $db->getlatenessesByUDID($udid); // a two-dimensional array ["clinic name"][array]
            $db->trlog(TranType::LATE_GET, 'Lateness got by device ' . $udid, null, null, null, $udid);
            if (!empty($lates)) {
                $this->registry->template->lates = $lates;
                $this->registry->template->show('late_view');
            } else {
                $this->registry->template->show('late_none');
            }
        }
    }

    public function reg() {
        // registers for lateness updates then redirects to the view() above
        $api = new howlate_api();
        $pin = filter_input(INPUT_GET, "pin");
        $udid = filter_input(INPUT_GET, "udid");
        if ($pin != "" and $udid != "") {

            howlate_util::validatePin($pin);

            $org = howlate_util::orgFromPin($pin);
            $id = howlate_util::idFromPin($pin);
            $db = new howlate_db();
            $db->validatePin($org, $id);
            $db->register($udid, $org, $id);
            $db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'registered pin ' . $pin);
        }
        $this->view();
    }

    private function addressURL($clin) {
        $str = "http://maps.google.com/maps?q=$clin->Address1";
        if ($clin->Address2 != '') {
            $str .= "+$clin->Address2";
        }
        if ($clin->City != '') {
            $str .= "+$clin->City";
        }
        if ($clin->Zip != '') {
            $str .= "+$clin->Zip";
        }

        return $str;
    }

}

?>
