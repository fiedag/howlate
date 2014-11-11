<?php

Class billingController Extends baseController {

    public function index() {
        
        $billing = new billing();
        
        $this->registry->template->controller = $this;
        $chargeover = new chargeover();
        $customer = $chargeover->getCustomer($this->org->OrgID);
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
        
        $unbilled_sms = $billing->getUnbilledSMS($this->org->OrgID);
        $this->registry->template->unbilled_sms = $unbilled_sms;
        $this->registry->template->show('billing_index');
    }
    
}
