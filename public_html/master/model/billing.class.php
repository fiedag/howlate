<?php

class billing {
    function __construct() {
        $this->conn = new mysqli('localhost', howlate_util::mysqlUser(), howlate_util::mysqlPassword(), howlate_util::mysqlBillingDb());
    }

    function __destruct() {
        if (is_resource($this->conn) && get_resource_type($this->conn) == 'mysql link') {
            $this->conn->close();
        }
    }
    
    function mylog($msg) {
        echo date("Y-m-d H:i:s ", time()) . ":" . $msg . "<br>";
    }

    public function recordAllUsage() {
        echo "Recording all useage for all organisations\r\n";
        $q = "SELECT OrgID, OrgName FROM vwCustomers";
        if ($result = $this->conn->query($q)) {
            $chargeover = new chargeover();
            while ($row = $result->fetch_object()) {
                echo "Recording Usage for $row->OrgName \r\n";
                try {
                    $this->recordUsage($chargeover, $row->OrgID);
                } catch(Exception $ex) {
                    echo $ex->getMessage() . " TRACE: " . $ex->getTraceAsString();
                }
            }
        }
    }
    
    public function recordUsage($chargeover, $OrgID) {
        $cust = $chargeover->getCustomer($OrgID);
        if(!$cust) {
            throw new Exception("Chargeover customer does not exist.");
        }
        $package = $chargeover->getCurrentActivePackage($cust->customer_id);
        if(!$package) {
            throw new Exception("Package does not exist.");
        }
        $line_items = $chargeover->getPackageLineItems($package);
        if (count($line_items) <= 0) {
            throw new Exception("Package Line item does not exist.");
        }
        $item = $line_items[0];
        // all good now look up usage
        $last_billed = $this->getLastBilledSMS($OrgID);
        $last_unbilled = $this->getLastUnbilledSMS($OrgID);
        if ($last_unbilled <= 0) {
            return;  // no new SMS messages!
        }
        $usage = $this->getUnbilledSMSUsage($OrgID, $last_billed, $last_unbilled);
        if($usage <= 0) {
            return;
        }
        $chargeover->createUsage($item->line_item_id, $usage);
        $this->updateSnapshot($OrgID, $last_unbilled);
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