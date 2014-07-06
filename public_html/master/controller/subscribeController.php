<?php

Class subscribeController Extends baseController {

    public $org;

    public function index() {

        $this->registry->template->controller = $this;

        $company = $_POST["company"];
        $email = $_POST["email"];

        $this->registry->template->CompanyName = $company;
        $this->registry->template->Email = $email;

        $this->registry->template->show('subscribe_view');
        $this->createSubdomain($company, $email);
    }

    private function createSubdomain($company, $email) {

        echo "<div id='message1'> Subdomain is being created.  Please wait...</div>";
        $howlate_site = new howlate_site();
        $message = $howlate_site->create($company, $email);
        echo $message;
        
    }


    
    
}
