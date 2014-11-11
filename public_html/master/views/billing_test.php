<?php



$billing = new billing();

$customer = $billing->getCustomer('AAADD');
echo $customer->company;

$package = $billing->getCurrentActivePackage($customer->customer_id);

echo "Package ID: $package->package_id, Next Invoice Date = $package->next_invoice_datetime <br>";

$lines = $billing->getLineItems($package);

$from_date = $billing->getLastBilled('AAADD');

foreach($lines as $line) {
    echo "$line->item_name , $line->descrip , $line->item_units <br>";
    $from_date = '2014-01-01';
    $to_date = date('m/d/Y h:i:s a');
    
    //$billing->updateUsage($line->line_item_id, 38, $from_date, $to_date);
}


?>