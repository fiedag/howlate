<?php

/*
 * Billing is the interface between How-Late and the 
 * ChargeOver API.
 * 
 * 
 * 
 */
class Billing {

    /*
     * The constructor creates a database connection.
     * This is necessary because this billing class is called from the 
     * cron job which will not have a MVC context
     */
    function __construct() {
        $this->conn = new mysqli('localhost', HowLate_Util::mysqlUser(), HowLate_Util::mysqlPassword(), HowLate_Util::mysqlDb());
    }

    function __destruct() {
        if (is_resource($this->conn) && get_resource_type($this->conn) == 'mysql link') {
            $this->conn->close();
        }
    }

    
    /*
     * Write to std out but with a timestamp
     */
    function mylog($msg) {
        echo date("Y-m-d H:i:s ", time()) . ":" . $msg . "\r\n";
    }

    /*
     * Record the usage for a single Organisation
     * 
     */
    public function recordOrgUsage($OrgID) {
        $chargeover = new Chargeover();
        try {
            $this->recordUsage($chargeover, $OrgID, $OrgID);
        } catch (Exception $ex) {
            $this->mylog($ex->getMessage() . " TRACE: " . $ex->getTraceAsString());
        }
    }

    /* Record usage
     * for all organisations
     */
    public function recordAllUsage() {
        $q = "SELECT OrgID, OrgName FROM orgs";
        if ($result = $this->conn->query($q)) {
            $chargeover = new Chargeover();
            while ($row = $result->fetch_object()) {
                $this->mylog("Processing any Usage for $row->OrgName");
                try {
                    $this->recordUsage($chargeover, $row->OrgID, $row->OrgName);
                } catch (Exception $ex) {
                    $this->mylog($ex->getMessage() . " TRACE: " . $ex->getTraceAsString());
                }
            }
        }
    }

    /*
     * For an OrgID this will return a list of clinics
     * and the number of practitioners and whether the clinic is integrated
     * 
     * 
     */
    public function getHowLateDetails($OrgID) {
        $q = "SELECT OrgID, OrgName, ClinicID, ClinicName, NumPractitioners, IsIntegrated FROM vwBillingClinPract WHERE OrgID = '$OrgID'";
        $sql = MainDb::getInstance();
        if ($result = $sql->query($q)) {
            $tempArray = array();
            while ($row = $result->fetch_object()) {
                $tempArray[] = $row;
            }
            return $tempArray;
        }
        return null;
    }

    /*
     * Calls the Chargeover API passing the external Key = OrgID
     * and returns the package object
     */
    public function getChargeoverPackage($OrgID) {
        $chargeover = new Chargeover();
        $co_cust = $chargeover->getCustomer($OrgID);
        if (!$co_cust) {
            throw new Exception("Customer $OrgID is not set up in Chargeover!");
        }
        $active_package = $chargeover->getCurrentActivePackage($co_cust->customer_id);
        if (!$active_package) {
            $this->mylog("No Active package exists for Chargeover customer!");
            return null;
        }
        return $chargeover->getPackage($active_package->package_id);
    }

    /*
     * Upgrades or creates a package line for a known packag ID
     * external key is usually the clinicID to which this applies
     */
    function upgradePackageLine($package_id, $item_id, $line_desc, $external_key, $qty = null) {

        $arr = array('line_item_id' => $item_id, 'external_key' => $external_key, 'descrip' => $line_desc);
        if (!is_null($qty)) {
            $arr['line_quantity'] = $qty;
        }

        $chargeover = new Chargeover();
        $data = array('line_items' => array(0 => $arr));

        return $chargeover->action('package', $package_id, 'upgrade', $data);
    }

    public function createPackage($organisation) {
        $chargeover = new Chargeover();
        $co_cust = $chargeover->getCustomer($organisation->OrgID);
        if (!$co_cust) {
            throw new Exception("Customer $organisation->OrgName is not set up in Chargeover!");
        }
        $active_package = $chargeover->getCurrentActivePackage($co_cust->customer_id);
        if ($active_package) {
            throw new Exception("Active package already exists for Chargeover customer!");
        }

        // single clinic
        // and single clinic SMS
        $Subscription = CO_Product::CO_SC_PRACT;
        $SMS = CO_Product::CO_SC_SMS;
        $Descrip = "Created by How-Late program";
        $chargeover->createPackage($co_cust->customer_id, $Subscription, $SMS);
    }

    /*
     * 
     * 
     * NOT FINISHED.  Needs to update the quantity for the given Clinic
     * and presumed to run nightly reflecting any change to the number
     * of assigned practitioners in every clinic
     */
    public function adjustPackage($organisation) {
        $clin = $this->getHowLateDetails($organisation->OrgID);
        $package = $this->getChargeoverPackage($organisation->OrgID);
        foreach ($clin as $key => $val) {
            $line_item_id = array_search($val->ClinicID, array_column($package, 'external_key'));
            if (!$line_item_id) {
                
            }
        }
    }

    
    private function recordUsage($chargeover, $OrgID, $OrgName = 'Org Name not given') {
        $last_billed = $this->getLastBilledSMS($OrgID);
        $last_unbilled = $this->getLastUnbilledSMS($OrgID);

        if ($last_unbilled <= 0) {
            $this->mylog("$OrgName no new SMS messages, Last Billed = $last_billed, Last unbilled = $last_unbilled");
            return;  // no new SMS messages!
        }

        $usage = $this->getUnbilledSMSUsage($OrgID, $last_billed, $last_unbilled);

        if ($usage <= 0) {
            $this->mylog("$OrgID usage returned $usage, not recording...");
            return;
        }
        $this->mylog("$OrgName Last billed sentsms.ID = $last_billed, Last unbilled = $last_unbilled, recording usage = $usage");

        $cust = $chargeover->getCustomer($OrgID);
        if (!$cust) {
            $this->mylog("Chargeover customer $OrgID does not exist.");
            return;
        }

        $this->mylog("Chargeover->getCustomer returned customer ID = $cust->customer_id");
        $package = $chargeover->getCurrentActivePackage($cust->customer_id);
        if (!$package) {
            $this->mylog("Package does not exist.<br>");
            return;
        }
        $line_items = $chargeover->getUsageLineItems($package);
        if (count($line_items) <= 0) {
            $this->mylog("Usage Line item does not exist.");
            return;
        }
        
        $this->mylog("$OrgName has usage line item ");

        $item = current($line_items);

        // all good now look up usage

        $chargeover->createUsage($item->line_item_id, $usage);
        $this->updateSnapshot($OrgID, $last_unbilled);
        $this->mylog("Updated chargeover usage for $OrgName");
    }

    // the ID of the last sentsms table record used to update
    // usage in the billing system
    private function getLastBilledSMS($OrgID) {
        $q = "SELECT getLastBilledSMS(?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('s', $OrgID);
        if (!$stmt->execute())
            throw new Exception("getLastBilledSMS error = $stmt->error");
        if ($stmt->affected_rows == 0) {
            throw new Exception("error= " . $this->conn->error, E_USER_ERROR);
        }
        $stmt->bind_result($id);
        $stmt->fetch();
        return $id;
    }

    // the ID of the latest sentsms record for this organisation
    private function getLastUnbilledSMS($OrgID) {
        $q = "SELECT getLastUnbilledSMS(?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('s', $OrgID);
        if (!$stmt->execute())
            throw new Exception("getLastUnbilledSMS error = $stmt->error");
        if ($stmt->affected_rows == 0) {
            throw new Exception("error= " . $this->conn->error, E_USER_ERROR);
        }
        $stmt->bind_result($id);
        $stmt->fetch();
        return $id;
    }

    private function getUnbilledSMSUsage($OrgID, $LastBilledID, $LastUnbilledID) {
        $q = "SELECT getUnbilledSMSUsage(?,?,?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sii', $OrgID, $LastBilledID, $LastUnbilledID);
        if (!$stmt->execute())
            throw new Exception("getUnbilledSMSUsage error = $stmt->error");
        if ($stmt->affected_rows == 0) {
            throw new Exception("error= " . $this->conn->error, E_USER_ERROR);
        }
        $stmt->bind_result($num);
        $stmt->fetch();
        return $num;
    }

    private function updateSnapshot($OrgID, $LastBilledID) {
        $q = "REPLACE INTO snapshot (OrgID, LastBilledSMS) VALUES (?,?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('si', $OrgID, $LastBilledID);
        if (!$stmt->execute())
            throw new Exception("updateSnapshot error = $stmt->error");
        if ($stmt->affected_rows == 0) {
            throw new Exception("error= " . $this->conn->error, E_USER_ERROR);
        }
    }

}

/*
 * 
 * ChargeOver Product IDs
 * 
 */
abstract class CO_Product {

    const CO_SC_PRACT = 18; //"Subscription (Single Clinic)";	
    const CO_NA_PRACT = 8;  //"Subscription (No agent integration)";	
    const CO_MC_PRACT = 19; //"Subscription (Multiple Clinics)";
    const CO_SC_SMS = 16; //"SMS (Single Clinic)";	
    const CO_NA_SMS = 12; //"SMS (No agent integration)";	
    const CO_MC_SMS = 17; //"SMS (Multiple Clinics)";	
    const CO_BP_SMS = 1;  //"Beta Partners";	

}

?>