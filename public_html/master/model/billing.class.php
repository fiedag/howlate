<?php

class billing {
    
    function __construct() {
        $this->conn = new mysqli('localhost', howlate_util::mysqlUser(), howlate_util::mysqlPassword(), howlate_util::mysqlDb());
    }

    function __destruct() {
        if (is_resource($this->conn) && get_resource_type($this->conn) == 'mysql link') {
            $this->conn->close();
        }
    }
    
    function mylog($msg) {
        echo date("Y-m-d H:i:s ", time()) . ":" . $msg . "<br>";
    }

    
    public function recordOrgUsage($OrgID) {
        $chargeover = new chargeover();
        try {
            $this->recordUsage($chargeover, $OrgID);
        } catch (Exception $ex) {
            echo $ex->getMessage() . " TRACE: " . $ex->getTraceAsString();
        }
    }

    public function recordAllUsage() {
        echo "Recording all useage for all organisations\r\n";
        $q = "SELECT OrgID, OrgName FROM vwCustomers";
        if ($result = $this->conn->query($q)) {
            $chargeover = new chargeover();
            while ($row = $result->fetch_object()) {
                echo "Processing any Usage for $row->OrgName \r\n";
                try {
                    $this->recordUsage($chargeover, $row->OrgID);
                } catch(Exception $ex) {
                    echo $ex->getMessage() . " TRACE: " . $ex->getTraceAsString();
                }
            }
        }
    }

    public function getHowLateDetails($OrgID) {
        $q = "SELECT OrgID, OrgName, ClinicID, ClinicName, NumPractitioners, IsIntegrated FROM vwBillingClinPract WHERE OrgID = '$OrgID'";
        $sql = maindb::getInstance();
        if ($result = $sql->query($q)) {
            $tempArray = array();
            while ($row = $result->fetch_object()) {
                $tempArray[] = $row;
            }
            return $tempArray;
        }
        return null;
    }
    
    public function getChargeoverDetails($OrgID) {
        $chargeover = new chargeover();
        $co_cust = $chargeover->getCustomer($OrgID);
        if(!$co_cust) {
            throw new Exception("Customer $OrgID is not set up in Chargeover!");
        }
        $active_package = $chargeover->getCurrentActivePackage($co_cust->customer_id);
        if(!$active_package) {
            echo("No Active package exists for Chargeover customer!");
            return null;
        }
        return $chargeover->getPackage($active_package->package_id);
        
    }
    
    function createPackageLine($package_id, $item_id, $qty) {
        $chargeover = new chargeover();
        $data = array('line_items' => array( 0 => array(
			'item_id' => $item_id, 
			'line_quantity' => $qty,
			) ));
        return $chargeover->action('package', $package_id, 'upgrade', $data);   
    }
    

    function upgradePackageLine($package_id, $line_item_id, $line_desc, $external_key, $qty = null) {

        $arr = array('line_item_id' => $line_item_id, 'external_key' => $external_key, 'descrip' => $line_desc);
        if (!is_null($qty)) {
            $arr['line_quantity'] = $qty; 
        }
        
        $chargeover = new chargeover();
        $data = array('line_items' => array( 0 => $arr ));
        
        return $chargeover->action('package', $package_id, 'upgrade', $data);   
    }
    
    
    public function createPackage($organisation) {
        $chargeover = new chargeover();
        $co_cust = $chargeover->getCustomer($organisation->OrgID);
        if(!$co_cust) {
            throw new Exception("Customer $organisation->OrgName is not set up in Chargeover!");
        }
        $active_package = $chargeover->getCurrentActivePackage($co_cust->customer_id);
        if($active_package) {
            throw new Exception("Active package already exists for Chargeover customer!");
        }
        
        $Subscription = CO_Product::CO_SC_PRACT;
        $SMS = CO_Product::CO_SC_SMS;
        $Descrip = "Created by How-Late program";
        $chargeover->createPackage($co_cust->customer_id, $Subscription, $SMS);
        
    }
    
    public function adjustPackage($organisation) {
        $clin = $this->getHowLateDetails($organisation->OrgID);
        $package = $this->getChargeoverDetails($organisation->OrgID);
        foreach($clin as $key => $val) {
            $line_item_id = array_search($val->ClinicID, array_column($package, 'external_key'));
            if(!$line_item_id) {
                
            }
        }
        
    }
    
    
    public function recordUsage($chargeover, $OrgID) {
        $last_billed = $this->getLastBilledSMS($OrgID);
        $last_unbilled = $this->getLastUnbilledSMS($OrgID);
        
        if ($last_unbilled <= 0) {
            echo "$OrgID no new SMS messages, Last Billed = $last_billed, Last unbilled = $last_unbilled\r\n";
            return;  // no new SMS messages!
        }
        
        $usage = $this->getUnbilledSMSUsage($OrgID, $last_billed, $last_unbilled);
        
        if($usage <= 0) {
            throw new Exception("$OrgID usage returned $usage, not recording...");
            return;
        }
        echo "$OrgID Last billed = $last_billed, Last unbilled = $last_unbilled, recording usage = $usage\r\n";

        
        $cust = $chargeover->getCustomer($OrgID);
        if(!$cust) {
            throw new Exception("Chargeover customer does not exist.");
        }

        echo "getCustomer returned customer ID = $cust->customer_id <br>";
        $package = $chargeover->getCurrentActivePackage($cust->customer_id);
        if(!$package) {
            throw new Exception("Package does not exist.<br>");
        }
        echo "Current Active Package Exists! <br>";
   
        
        $line_items = $chargeover->getUsageLineItems($package);
        if (count($line_items) <= 0) {
            throw new Exception("Package Line item does not exist.<br>");
        }
        echo "We have a usage line item with units = text message<br>\r\n";

        
        $item = current($line_items);

        // all good now look up usage

        
        $chargeover->createUsage($item->line_item_id, $usage);
        $this->updateSnapshot($OrgID, $last_unbilled);
        echo "$OrgID updated chargeover usage\r\n";
        
    }
    
    // the ID of the last sentsms table record used to update
    // usage in the billing system
    function getLastBilledSMS($OrgID) {
        $q = "SELECT getLastBilledSMS(?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('s', $OrgID);
        if (!$stmt->execute()) throw new Exception("getLastBilledSMS error = $stmt->error");
        if ($stmt->affected_rows == 0) {
           throw new Exception("error= " . $this->conn->error , E_USER_ERROR);
        }
        $stmt->bind_result($id);
        $stmt->fetch();
        return $id;
    }

    // the ID of the latest sentsms record for this organisation
    function getLastUnbilledSMS($OrgID) {
        $q = "SELECT getLastUnbilledSMS(?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('s', $OrgID);
        if (!$stmt->execute()) throw new Exception("getLastUnbilledSMS error = $stmt->error");
        if ($stmt->affected_rows == 0) {
           throw new Exception("error= " . $this->conn->error , E_USER_ERROR);
        }
        $stmt->bind_result($id);
        $stmt->fetch();
        return $id;
    }
    
    function getUnbilledSMSUsage($OrgID, $LastBilledID, $LastUnbilledID) {
        $q = "SELECT getUnbilledSMSUsage(?,?,?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sii', $OrgID, $LastBilledID, $LastUnbilledID);
        if (!$stmt->execute()) throw new Exception("getUnbilledSMSUsage error = $stmt->error");
        if ($stmt->affected_rows == 0) {
           throw new Exception("error= " . $this->conn->error , E_USER_ERROR);
        }
        $stmt->bind_result($num);
        $stmt->fetch();
        return $num;
    }
    
    function updateSnapshot($OrgID, $LastBilledID) {
        $q = "REPLACE INTO snapshot (OrgID, LastBilledSMS) VALUES (?,?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('si', $OrgID, $LastBilledID);
        if (!$stmt->execute()) throw new Exception("updateSnapshot error = $stmt->error");
        if ($stmt->affected_rows == 0) {
           throw new Exception("error= " . $this->conn->error , E_USER_ERROR);
        }
    } 

    
    // all SMS messages sent since the last usage was transferred to billing system
    function getUnrecordedSMS($orgID) {
        $q = "SELECT getUnbilledSMSUsage2(?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('s', $orgID);
        if (!$stmt->execute()) throw new Exception("getUnbilledSMS error = $stmt->error");
        if ($stmt->affected_rows == 0) {
           throw new Exception("error= " . $this->conn->error , E_USER_ERROR);
        }
        $stmt->bind_result($num_sms);
        $stmt->fetch();
        return $num_sms;
    }
    
    
}    

abstract class CO_Product {
	const CO_SC_PRACT  = 18; //"Subscription (Single Clinic)";	
        const CO_NA_PRACT  = 8;  //"Subscription (No agent integration)";	
        const CO_MC_PRACT  = 19; //"Subscription (Multiple Clinics)";
        const CO_SC_SMS    = 16; //"SMS (Single Clinic)";	
        const CO_NA_SMS    = 12; //"SMS (No agent integration)";	
        const CO_MC_SMS    = 17; //"SMS (Multiple Clinics)";	
        const CO_BP_SMS    = 1;  //"Beta Partners";	
}


?>