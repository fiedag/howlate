<?php

class invoicer {
    function __construct() {
        $this->conn = new mysqli('localhost', howlate_util::mysqlUser(), howlate_util::mysqlPassword(), howlate_util::mysqlBillingDb());
    }    
    
    public function createNewInvoice($OrgID, $LastBillingDay, $PeriodEnd, $BillingContact, $FreeClinics,$SmallClinics, $LargeClinics,$SuperClinics, $SMSesSent) {
        $InvoiceNum = $this->createHeader($OrgID, $LastBillingDay, $PeriodEnd, $BillingContact);
        logging::stdout("Header for invoice $InvoiceNum created");
        $TotalClinics = $FreeClinics + $SmallClinics + $LargeClinics + $SuperClinics;
        $pricing = $this->getPricing($OrgID, $TotalClinics);
        
        var_dump($pricing);
//        $this->createLine($InvoiceNum, 'Free Clinics', $pricing->FreeClinicPrice, $org->Discount, $FreeClinics);
//        $this->createLine($InvoiceNum, 'Small Clinics', $pricing->SmallClinicPrice, $org->Discount, $SmallClinics);
//        $this->createLine($InvoiceNum, 'Large Clinics', $pricing->LargeClinicPrice, $org->Discount, $LargeClinics);
//        $this->createLine($InvoiceNum, 'Superclinics', $pricing->SuperclinicPrice, $org->Discount, $Superclinics);
//        $this->createLine($InvoiceNum, 'SMS Messages Sent', $pricing->SMSPrice, 0, $SMSesSent);

    }
    
    
    private function createHeader($OrgID, $FromDate, $ToDate, $BillingContact) {
        logging::stdout("$OrgID, $FromDate, $ToDate, $BillingContact");
        $q = "INSERT INTO invchead (OrgID, PeriodStart, PeriodEnd, EmailAddress) VALUES (?,?,?,?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ssss',$OrgID, $FromDate,$ToDate, $BillingContact);
        if (!$stmt->execute()) throw new Exception("# Query Error ( $stmt->errno ) "  . $stmt->error);
        if ($stmt->affected_rows == 0) {
           throw new Exception("error= " . $stmt->error);
        }
        return $stmt->insert_id;
    }
    
    private function createLine($InvoiceNum, $ItemDesc, $ItemPrice, $ItemDiscount, $Qty) {
        
        
        
        $q = "INSERT INTO invcdtl (InvoiceNum, InvoiceLine, LineDesc, UnitPrice, DiscountPct, Qty, ExtPrice) " .
                "VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        
    }

    
    private function getPricing($OrgID, $TotalClinics) {
        $q = "SELECT getSmallClinicPrice($TotalClinics) As SmallClinicPrice, getLargeClinicPrice($TotalClinics) as LargeClinicPrice, getSuperclinicPrice($TotalClinics) As SuperclinicPrice, getSMSPrice($TotalClinics) As SMSPrice, PlanDiscountPct FROM orgdiscount WHERE OrgID = '$OrgID'";
        
        if ($result = $stmt->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
            return $myArray;
        }
        $result->close();  
    }
    
    
    private function getPricing3333($OrgID, $TotalClinics) {
        $q = "SELECT getSmallClinicPrice(?) As SmallClinicPrice, getLargeClinicPrice(?) as LargeClinicPrice, getSuperclinicPrice(?) As SuperclinicPrice, getSMSPrice(?) As SMSPrice, PlanDiscountPct FROM orgdiscount WHERE OrgID = ?";
        
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('iiiis',$TotalClinics, $TotalClinics, $TotalClinics, $TotalClinics, $OrgID);
        if (!$stmt->execute()) throw new Exception("getPricing error = $stmt->error");
        if ($stmt->affected_rows == 0) {
           throw new Exception("error=" . $this->conn->error , E_USER_ERROR);
        }
        $stmt->bind_result( $SmallClinicPrice, $LargeClinicPrice, $SuperclinicPrice, $SMSPrice, $PlanDiscountPct);
        $stmt->fetch();
        
    }
    
}
?>