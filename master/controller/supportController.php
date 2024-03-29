<?php

Class SupportController Extends baseController {

    public function index() {
        $this->registry->template->controller = $this;
     
        $this->registry->template->user = $_SESSION["USER"];
        $this->registry->template->msg = "Send us a note or a question. Get prompt action.";
        $this->registry->template->show('support_index');
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

    private function getOrg() {
        if (!isset($this->Organisation)) {
            $this->Organisation = Organisation::getInstance(__SUBDOMAIN);
        }
    }

    public function getPricing() {
        include('includes/xcrud/xcrud.php');
        $xcrud2 = Xcrud::get_instance("Billing Database");
        $xcrud2->connection(HowLate_Util::mysqlUser(),HowLate_Util::mysqlPassword(),HowLate_Util::mysqlBillingDb(),'localhost','utf8');
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
        $smstext = "User " .  $_SESSION["USER"] . " from " . $this->Organisation->OrgName . " has sent you a note.  Check admin@how-late.com";
        $sms = new HowLate_SMS();
        $sms->httpSend(howlate_util::admin_orgid(), $administrator, $smstext);
        
        $mailer = new Howlate_Mailer();
        $mailer->send(HowLate_Util::admin_email(),'Administrator', 'A note has been received', $note, 'admin@how-late.com', 'Administrator');
          
        $this->registry->template->msg = "Thank you.  Your note is being actioned!";
        $this->registry->template->show('support_index');

        
        
    }
    
 
}
