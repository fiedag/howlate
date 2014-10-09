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
    
    function getNextBillingDate($orgID) {
        $q = "SELECT getNextBillingDate('" . $orgID . "') AS NextBillingDate";
        if ($result = $this->conn->query($q)) {
            $row = $result->fetch_object();
        }
        if (count($row) != 1) {
            throw new Exception("Error returning next billing date");
        }

        return $row->NextBillingDate;        
    
    }
    
    /// return all orgs where next billingDate is <= today
    function getDueOrgs() {
        $q = "SELECT OrgID, NextBillingDay, LastBillingDay FROM vwOrgBillDue";
        $myArray = array();
        if ($result = $this->conn->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
            return $myArray;
        }
        $result->close();
    }
    
    function getOrgClinicCount($orgID) {
        $q = "SELECT FreeClinics, SmallClinics, LargeClinics, SuperClinics FROM vwOrgClinicCount WHERE OrgID = '$orgID'";
        if ($result = $this->conn->query($q)) {
            return $result->fetch_object();
        }
    }
    
    function getOrgSMSCount($orgID, $lastbill,$nextbill) {
        $q = "SELECT COUNT(Id) AS NumSMSSent FROM vwSentSMS WHERE OrgID = ? AND Timestamp > ? AND Timestamp <= ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sss',$orgID, $lastbill,$nextbill);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
        if ($stmt->affected_rows == 0) {
           trigger_error("error= " . $this->conn->error , E_USER_ERROR);
        }
        $stmt->bind_result($num_sms_sent);
        $stmt->fetch();
        return $num_sms_sent;
    }
    
}    