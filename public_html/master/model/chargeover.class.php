<?php

abstract class ServicePlans {
    const BETA_PARTNERS = 1;
    const SOLE_PRACTITIONER = 4;
    const SINGLE_CLINIC = 6;
    const CORPORATE = 7;
}

class chargeover {
    
    private $API;
    function __construct() {
        require_once('/home/howlate/public_html/master/includes/chargeover/ChargeOverAPI.php');
        $this->API = new ChargeOverAPI(howlate_util::chargeoverApiUrl(), ChargeOverAPI::AUTHMODE_HTTP_BASIC, howlate_util::chargeoverUsername(), howlate_util::chargeoverPassword());
        
    }

    function __destruct() {
    }

    function createCustomer($OrgID, $OrgName, $Address1, $Address2, $Address3, $City, $State, $Postcode, $Country, $UserName, $UserID, $Email ) {
        $Customer = new ChargeOverAPI_Object_Customer(array(

	// Main customer data
	'company' => $OrgName,
	
	'bill_addr1' => $Address1,
	'bill_addr2' => $Address2,
	'bill_addr3' => $Address3,
	'bill_city' => $City,
	'bill_state' => $State,
	'bill_postcode' => $Postcode,
	'bill_country' => $Country,

	'external_key' => $OrgID, 		// The external key is used to reference objects in external applications

	// This is a short-cut to also creating a user at the same time
	'superuser_name' => $UserName, 
	'superuser_email' => $Email, 
	'superuser_username' => $UserID, 

	));

        $resp = $this->API->create($Customer);
        
        if ($this->API->isError($resp))
        {
            throw new Exception('Error creating Chargeover customer via API' . $this->API->lastResponse());
        }
    }

    function updateCustomer($OrgID, $OrgName, $Address1, $Address2, $Address3, $City, $State, $Postcode, $Country ) {
        $cust = $this->getCustomer($OrgID);
        $customer_id = $cust->customer_id;
        $Customer = new ChargeOverAPI_Object_Customer(array(

	// Main customer data
	'company' => $OrgName,
	
	'bill_addr1' => $Address1,
	'bill_addr2' => $Address2,
	'bill_addr3' => $Address3,
	'bill_city' => $City,
	'bill_state' => $State,
	'bill_postcode' => $Postcode,
	'bill_country' => $Country,

	'external_key' => $OrgID, 		// The external key is used to reference objects in external applications


	));

        $resp = $this->API->modify($customer_id, $Customer);
        
        if ($this->API->isError($resp))
        {
            throw new Exception('Error modifying Chargeover customer via API' . $this->API->lastResponse());
        }
    }
    
    
    
    function createPackage($customer_id, $ServicePlan, $Descrip) {
        $Package = new ChargeOverAPI_Object_Package();
        $Package->setCustomerId($customer_id);

        $LineItem = new ChargeOverAPI_Object_LineItem();
        $LineItem->setItemId($ServicePlan);
        $LineItem->setDescrip($Descrip);
            
        $Package->addLineItems($LineItem);

        $resp = $this->API->create($Package);
        
        if ($this->API->isError($resp))
        {
            throw new Exception('Error aadding line item via API' . $API->lastResponse());
        }
        
    }
    
    
    function createUsage($package_line_item_id, $units ) {
        $Usage = new ChargeOverAPI_Object_Usage();
	$Usage->setLineItemId($package_line_item_id);
        $Usage->setUsageValue($units);
        
        $resp = $this->API->create($Usage);
        
        if ($this->API->isError($resp))
        {
            throw new Exception('Error adding line item via API' . $this->API->lastResponse());
        }
    }

    
    function getCustomer($orgID) {
	$resp = $this->API->find('customer', array('external_key:EQUALS:' . $orgID));
        $Customer = $resp->response;
        if($Customer) {
            return $Customer[0];
        } else
            return null;
    }
    
    
    function getCurrentActivePackage($customer_id) {
        
	$resp = $this->API->find('package', array('customer_id:EQUALS:' . $customer_id, 
                                            'package_status_state:EQUALS:a'));
        
        echo "getCurrentActive Package for customer $customer_id, response= <br>";

	$Package = $resp->response;
        
        var_dump($Package);
        echo "<br>";
        return $Package[0];
    }
    
    function getPackageLineItems($package) {
	$resp = $this->API->findById('package', $package->package_id);
	$Package = $resp->response;
	return $Package->getLineItems();
    }
}

?>