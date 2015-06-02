<?php 

/* Practice Management Systems
 * 
 * 
 * 
 * 
 */
class PmSystem {
    
    public $ID;
    public $Name;
    public $Company;
    public $Website;
    public $SelectSessions;
    public $SelectLates;
    public $SelectToNotify;
    
    
    public static function getInstance($SystemID) {
        $q = "SELECT * FROM pmsystems WHERE ID = $SystemID";

        $sql = MainDb::getInstance();
        
        if ($result = $sql->query($q)->fetch_object()) {
            if (!self::$instance) {
                self::$instance = new self();
            }
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            return self::$instance;
        } else
            return null;
    }

    
    public static function getAllImplemented() {
        $q = "SELECT ID, Name FROM pmsystems WHERE Implemented = 1";
        $sql = MainDb::getInstance();
        $myArray = array();
        if ($result = $sql->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
            return $myArray;
        }
    }

}

?>
