<?php

Class signupController Extends baseController {

    public $org;

    public function index() {

        $this->registry->template->controller = $this;
        $this->registry->template->logourl = howlate_util::logoURL();
        $this->registry->template->show('signup_view');
    }

    public function create() {
        define("__DIAG", 1);

        echo "<div id='message1'> $company site is being created.  Please wait...</div>";

        $this->registry->template->controller = $this;

        if (!isset($_POST["company"]) or !isset($_POST["email"])) {
            echo "Invalid parameters.";
            return;
        }

        $company = $_POST["company"];
        $email = $_POST["email"];

        $this->registry->template->CompanyName = $company;
        $this->registry->template->Email = $email;

        $howlate_site = new howlate_site();
        howlate_util::diag("Domain is " . __DOMAIN);

        $subdomain = $howlate_site->create($company, $email);

        $url = "http://" . $subdomain . "." . __DOMAIN . "/login";

        echo "<a href='" . $url . "'>Click here to continue</a>";
    }

}
