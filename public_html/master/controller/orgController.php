<?php

Class orgController Extends baseController {

    public $org;

    public function index() {
        $this->org = organisation::getInstance(__SUBDOMAIN);

        $this->registry->template->controller = $this;
        $this->registry->template->show('org_index');
    }
    
    public function update() {
        $this->org = organisation::getInstance(__SUBDOMAIN);

        foreach ($_POST as $key => $value) {
            if (isset($this->org->$key)) {
                $org[$key] = $value;
            }
        }

        $this->org->update_org($org);

        $this->org = organisation::getInstance(__SUBDOMAIN);

        $this->registry->template->controller = $this;       
        $this->registry->template->show('org_index');
    }
    
    public function get_tz_options() {
        $tz = $this->org->getTimezones();
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