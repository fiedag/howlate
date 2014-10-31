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

    public function prepareAllDueBills() {

        $RightNow = new DateTime();

        $orgs = $this->getDueOrgs();

        foreach ($orgs as $key => $val) {
            logging::stdout("$val->OrgName ($val->OrgID) is due for billing.  Period start: $val->LastBillingDay to now:" . $RightNow->format('Y-m-d H:i:sP'));
            $clin_count = $this->getOrgClinicCount($val->OrgID);
            logging::stdout("Free Clinics:" . $clin_count->FreeClinics);
            logging::stdout("Small Clinics:" . $clin_count->SmallClinics);
            logging::stdout("Large Clinics:" . $clin_count->LargeClinics);
            logging::stdout("Superclinics:" . $clin_count->SuperClinics);

            $num_sms_sent = $this->getOrgSMSCount($val->OrgID, $val->LastBillingDay, $RightNow->format('Y-m-d H:i:sP'));
            logging::stdout("Organisation $val->OrgID sent $num_sms_sent SMSs in that billing period");

            $invoicer = new invoicer();
            try {
                
                $invoicer->createNewInvoice($val->OrgID, $val->LastBillingDay, $RightNow->format('Y-m-d H:i:sP'), $val->BillingContact,$clin_count->FreeClinics, $clin_count->SmallClinics, $clin_count->LargeClinics, $clin_count->SuperClinics, $num_sms_sent);
            } catch (Exception $ex) {
                logging::stdout("Unable to create invoice, error = " . $ex->getMessage() . ",program=" . $ex->getFile() . " (" . $ex->getLine() . ") " . $ex->getTraceAsString());
            }
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
        $q = "SELECT OrgID, OrgName, BillingContact, NextBillingDay, LastBillingDay FROM vwOrgBillDue WHERE BillingContact <> ''";
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
        if (!$stmt->execute()) throw new Exception("getOrgSMSCount error = $stmt->error");
        if ($stmt->affected_rows == 0) {
           throw new Exception("error= " . $this->conn->error , E_USER_ERROR);
        }
        $stmt->bind_result($num_sms_sent);
        $stmt->fetch();
        return $num_sms_sent;
    }
    
}    