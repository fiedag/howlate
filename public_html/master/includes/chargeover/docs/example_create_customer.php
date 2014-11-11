<?php

header('Content-Type: text/plain');

require '../ChargeOverAPI.php';

//This url should be specific to your ChargeOver instance
$url = 'https://how-late.chargeover.com/api/v3';
//$url = 'https://YOUR-INSTANCE-NAME.chargeover.com/api/v3';

$authmode = ChargeOverAPI::AUTHMODE_HTTP_BASIC;
$username = 'IfCopeybjKkJOwBsgdqHSRat8lh5X6zv';
$password = 'rNjiGRbW6EfA7LC5mgo218MdHSwz4yP3';

$API = new ChargeOverAPI($url, $authmode, $username, $password);

$Customer = new ChargeOverAPI_Object_Customer(array(
	'company' => 'Margate Clinic',
	
	'bill_addr1' => 'Margate',
	'bill_addr2' => '',
	'bill_city' => 'Margate',
	'bill_state' => 'TAS',
	'bill_postcode' => '7777',
	'bill_country' => 'AUSTRALIA',

	'external_key' => 'AAADD' 		// The external key is used to reference objects in external applications
	));

$resp = $API->create($Customer);

if (!$API->isError($resp))
{
	$customer_id = $resp->response->id;
	print('SUCCESS! Customer # is: ' . $customer_id);


}
else
{
	print('error saving customer via API');

	print("\n\n\n\n");
	print($API->lastRequest());
	print("\n\n\n\n");
	print($API->lastResponse());
	print("\n\n\n\n");
}

