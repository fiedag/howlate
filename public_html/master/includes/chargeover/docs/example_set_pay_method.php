<?php

/**
 * Example of setting the payment method for a billing package 
 *
 * 
 */

header('Content-Type: text/plain');

require '../ChargeOverAPI.php';

//This url should be specific to your ChargeOver instance
$url = 'http://dev.chargeover.com/api/v3';
//$url = 'https://YOUR-INSTANCE-NAME.chargeover.com/api/v3';

$authmode = ChargeOverAPI::AUTHMODE_HTTP_BASIC;
$username = 'JxI2LYptQkVDMbaFST8RNegzrqji9Wmh';
$password = 'k5mSN0rb7KFHl4PBVDpLv2JfoG6qEQiW';

$API = new ChargeOverAPI($url, $authmode, $username, $password);

// This is the customer/billing package we're updating
$customer_id = 62;
$package_id = 599;

// Create a new credit card object
$CreditCard = new ChargeOverAPI_Object_CreditCard();
$CreditCard->setNumber('4111 1111 1111 1111');
$CreditCard->setExpdateYear(date('Y') + 1);
$CreditCard->setExpdateMonth(12);
$CreditCard->setCustomerId($customer_id);

// Save credit card via API 
$resp = $API->create($CreditCard);

// Set the payment method 
if (!$API->isError($resp))
{
	$data = array(
		'paymethod' => ChargeOverAPI_Object_BillingPackage::PAYMETHOD_CREDITCARD, 
		'creditcard_id' => $resp->response->id, 
		);

	$resp = $API->action('package', $package_id, 'paymethod', $data);

	if (!$API->isError($resp))
	{
		print('Updated payment method!');

		/*
		print($API->lastRequest());
		print("\n\n\n");
		print($API->lastResponse());
		*/
	}
	else
	{
		print('ERROR: ' . $resp->message);
	}
}
else
{
	print('ERROR: ' . $resp->message);
}