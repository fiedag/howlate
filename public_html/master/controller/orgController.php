<?php

Class orgController Extends baseController {

    public $org;

    public function index() {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);

        $this->registry->template->controller = $this;
        $this->registry->template->show('org_index');
    }
    
    public function update() {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");

        foreach ($_POST as $key => $value) {
            if (isset($this->org->$key)) {
                $org[$key] = $value;
            }
        }
        $db = new howlate_db();
        $db->update_org($org);

        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);

        $this->registry->template->controller = $this;       
        $this->registry->template->show('org_index');
    }
    
    public function get_tz_options() {
        $tz = $this->org->gettimezones();
        foreach ($tz as $val) {
            echo "<option value='" . $val . "'";
            
            if ($val == $this->org->Timezone) {
                echo "selected";
            }
            echo ">$val</option>";
        }
    }
    
}

?>