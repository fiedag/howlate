<?php

Class subscribeController Extends baseController {

    public $org;

    public function index() {
        $this->registry->template->controller = $this;

        if (!isset($_POST["company"]) or !isset($_POST["email"])) {
            echo "Invalid parameters.";
            return;
        }

        $company = $_POST["company"];
        $email = $_POST["email"];


        $this->registry->template->CompanyName = $company;
        $this->registry->template->Email = $email;

        $this->registry->template->show('subscribe_view');


        $this->create($company, $email);
    }

    private function create($company, $email) {
        define("__DIAG",1);
        
        echo "<div id='message1'> $company site is being created.  Please wait...</div>";
        $howlate_site = new howlate_site();

        howlate_util::diag("Domain is " . __DOMAIN);

        $subdomain = $howlate_site->create($company, $email);

        $url = "http://" . $subdomain . "." . __DOMAIN . "/login";

        echo "<a href='" . $url . "'>Click here to continue</a>";
    }


}
