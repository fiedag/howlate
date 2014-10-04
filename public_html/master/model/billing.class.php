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
    
    
    
}    