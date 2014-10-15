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
        $billing = new billing();
        $this->registry->template->billing_day = $billing->getNextBillingDate($this->org->OrgID);
        $this->registry->template->show('support_pricing');
    }
    public function pricingbare() {
        $this->getOrg();
        $this->registry->template->controller = $this;

        $this->registry->template->show('support_pricingbare');
    }

    public function newfeatures() {
        $this->getOrg();
        $this->registry->template->controller = $this;

        $this->registry->template->show('support_newfeatures');
    }

    public function contact() {
        $this->getOrg();
        $this->registry->template->controller = $this;
        $this->registry->template->msg = "Please enter a note and hit submit.  Our administrator will take prompt action.";
        $this->registry->template->show('support_contact');
    }

    private function getOrg() {
        if (!isset($this->org)) {
            $this->org = organisation::getInstance(__SUBDOMAIN);
        }
    }

    public function getPricing() {
        include('includes/xcrud/xcrud.php');
        $xcrud2 = Xcrud::get_instance("Billing Database");
        $xcrud2->connection(howlate_util::mysqlUser(),howlate_util::mysqlPassword(),howlate_util::mysqlBillingDb(),'localhost','utf8');
        $xcrud2->table('pricing')->where("CountryCode = 'EN'");

        $xcrud2->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');

        $xcrud2->unset_add()->unset_edit()->unset_search();
        $xcrud2->unset_view()->unset_remove()->unset_sortable();
        $xcrud2->columns('CountryCode',true);
        
        $xcrud2->column_name('Description','$AUD per clinic per month');
        echo $xcrud2->render();
    }

    
    public function contactsubmit() {
        
        $note = filter_input(INPUT_POST,"Note");
        
        $this->getOrg();
        $this->registry->template->controller = $this;
        $administrator = "61403569377";
        $smstext = "User " .  $_SESSION["USER"] . " from " . $this->org->OrgName . " has sent you a note.  Check admin@how-late.com";
        howlate_sms::httpSend($this->org->OrgID, $administrator, $smstext);
        
        
        $mailer = new howlate_mailer();
        $mailer->send(howlate_util::admin_email(),'Administrator', 'A note has been received', $note, 'admin@how-late.com', 'Administrator');
          
        $this->registry->template->msg = "Thank you.  Your note is being actioned!";
        $this->registry->template->show('support_contact');

        
        
    }
    
}

?>
