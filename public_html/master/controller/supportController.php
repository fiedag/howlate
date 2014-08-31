<?php

Class supportController Extends baseController {

    public $org;
    public $controller;

    public function index() {
        $this->getOrg();
        $this->registry->template->controller = $this;
        $this->registry->template->show('support_upgrade');
    }

    public function pricing() {
        $this->getOrg();
        $this->registry->template->controller = $this;

        $this->registry->template->show('support_pricing');
    }

    public function newfeatures() {
        $this->getOrg();
        $this->registry->template->controller = $this;

        $this->registry->template->show('support_newfeatures');
    }

    public function contact() {
        $this->getOrg();
        $this->registry->template->controller = $this;
        $this->registry->template->show('support_contact');
    }

    private function getOrg() {
        if (!isset($this->org)) {
            $this->org = new organisation();
            $this->org->getby(__SUBDOMAIN, "Subdomain");
            $this->registry->template->companyname = $this->org->OrgName;
            $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);
        }
    }

    public function getPricing() {
        include('includes/xcrud/xcrud.php');
        $xcrud2 = Xcrud::get_instance("Billing Database");
        $xcrud2->connection('howlate_super','NuNbHG4NQn','howlate_billing','localhost','utf8');
        $xcrud2->table('pricing')->where("CountryCode = 'EN'");

        $xcrud2->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');

        $xcrud2->unset_add()->unset_edit()->unset_search();
        $xcrud2->unset_view()->unset_remove()->unset_sortable();
        $xcrud2->columns('CountryCode',true);
        
        $xcrud2->column_name('Description','$AUD per clinic per month');
        echo $xcrud2->render();
    }

    
    public function contactsubmit() {

        
        $this->getOrg();
        $this->registry->template->controller = $this;
        $this->registry->template->show('support_contact');

        
        
    }
    
}

?>
