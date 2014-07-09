<?php

Class clinicinfoController Extends baseController {

    public function index() {

        $clinicid = $_GET["clinicid"];
        $orgid = $_GET["orgid"];

        $org = new organisation();
        $org->getby($orgid, "OrgID");

        $this->registry->template->org = $org;

        foreach ($org->Clinics as $clinic) {
            if ($clinic->ClinicID == $clinicid) {
                $clin = $clinic;
            }
        }

        $this->registry->template->clinic = $clin;
        $this->registry->template->formattedAddress = $this->formatAddress($clin);
        $this->registry->template->addressURL = $this->addressURL($clin);
        $this->registry->template->subdomain = $org->Subdomain;
        $this->registry->template->show('clinicinfo_index');

    }

    private function formatAddress($clin) {
        $str = "$clin->Address1";
        if ($clin->Address2 != '') {
            $str .= " $clin->Address2";
        }
        if ($clin->City != '') {
            $str .= "<br>$clin->City";
        }
        if ($clin->Zip != '') {
            $str .= " $clin->Zip";
        }

        return $str;
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
