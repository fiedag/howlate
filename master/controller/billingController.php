<?php

Class BillingController Extends baseController {

    // used in array_filter call below
    private function usage_line($element) {
        return ($element->item_units == 'text message');
    }
    
    
    public function test() {

        $co = new Chargeover();

        $customer = $co->getCustomer($this->Organisation->OrgID);
        $package = $co->getCurrentActivePackage($customer->customer_id);
        $lines = $co->getPackageLineItems($package);
        $this->registry->template->lines = $lines;
        
        $this->registry->template->just_sms = array_filter($lines,  array($this, 'usage_line'));
        
        $this->registry->template->show('billing_test');
    }
    
    public function test2() {
        //echo howlate_util::to_udid(howlate_util::to_xudid('0403569377')) . "<br";
        //echo howlate_util::to_udid(howlate_util::to_xudid('0405149704')) . "<br";
        //echo howlate_util::to_xudid('61405149704') . "<br";
        $m = '61405149705';
        $x = HowLate_Util::to_xudid($m);
        $m2 = HowLate_Util::to_udid($x);
        echo " $m converts to $x  and back to $m2<br>";
    }

    
    public function index() {
        
        $billing = new Billing();
        
        $this->registry->template->controller = $this;
        $chargeover = new Chargeover();
        $customer = $chargeover->getCustomer($this->Organisation->OrgID);
        $package = $chargeover->getCurrentActivePackage($customer->customer_id);
        
        $dt = $package->next_invoice_datetime;
        $this->registry->template->billing_day = $dt;
        $this->registry->template->package_id = $package->package_id;
        $this->registry->template->paymethod = $package->paymethod;

        $items = $chargeover->getPackageLineItems($package);
        if (count($items) > 0) {
           $item_name = $items[0]->item_name;
           $item_descrip = $items[0]->descrip;
           $item_id = $items[0]->line_item_id;
        }
        else {
           $item_name = '';
           $item_descrip = '';
           $item_id = '';
        }
        
        $this->registry->template->package_name = $item_name;
        $this->registry->template->item_descrip = $item_descrip;
        $this->registry->template->item_id = $item_id;
        
        $unbilled_sms = $billing->getUnbilledSMS($this->Organisation->OrgID);
        $this->registry->template->unbilled_sms = $unbilled_sms;
        $this->registry->template->show('billing_index');
    }
    
}
