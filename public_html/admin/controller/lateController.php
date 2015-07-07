<?php

Class LateController Extends baseController {

    public $org;

    public function index() {

        $udid = filter_input(INPUT_GET, "udid");

        $url = "http://m.how-late.com/late/json?udid=" . $udid;

        $json_result = file_get_contents($url);

        $this->registry->template->json_result = $json_result;
        
        $this->registry->template->show('late_index');
    }


}

?>