<?php 

class orguser extends howlate_basetable {
    protected static $instance;
    
    public $UserID;
    public $DateCreated;
    public $EmailAddress;
    public $FullName;
    public $OrgID;
    public $XPassword;

    public static function getInstance($OrgID, $FieldValue, $FieldName = 'UserID') {
        $q = "SELECT UserID, DateCreated, EmailAddress, FullName, XPassword, OrgID , SecretQuestion1, SecretAnswer1 FROM orgusers WHERE OrgID = '$OrgID' AND $FieldName = '$FieldValue'";
        
        $sql = maindb::getInstance();
        if ($result = $sql->query($q)->fetch_object()) {
            if (!self::$instance) {
                self::$instance = new self();
            }
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            
            return self::$instance;
        } else {
            return null;
        }
    }
    
    public function changePassword($Password) {
        $q = "UPDATE orgusers SET XPassword = ? WHERE OrgID = ? AND UserID = ?";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $Password, $this->OrgID, $this->UserID);
        $stmt->execute();
        if ($stmt->affected_rows != 1) {
            throw new Exception("The Password change request was not successful [$this->OrgID,$this->UserID]. affected rows = $stmt->affected_rows", E_USER_ERROR);
        }
    }
    
    
    public function change_password($userid, $password, $orgID) {
        $q = "UPDATE orgusers SET XPassword = ? WHERE UserID = ? AND OrgID = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sss', $password, $userid, $orgID);
        $stmt->execute();
        if ($stmt->affected_rows > 1) {
            trigger_error("The Password change request was not successful. affected rows = $stmt->affected_rows", E_USER_ERROR);
            return false;
        }
        return true;
    }
    
    public static function createUser($orgid, $userid, $emailaddress) {
        $q = "INSERT INTO orgusers (OrgID, UserID, EmailAddress) VALUES (?, ?, ?)";
        $stmt = maindb::getInstance()->prepare($q);

        $stmt->bind_param('sss', $orgid, $userid, $emailaddress);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The user record was not created, error= " . $this->conn->error, E_USER_ERROR);
        }
        return $userid;
    }

}

?>
