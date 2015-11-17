<?php

/* Model Class for the Organisation 
 * 
 * This contains all the DML and is used by the orgController
 * and others
 * 
 */
class Organisation {

    protected static $instance;
    
    public $OrgID;
    public $OrgName;
    public $OrgShortName;
    public $TaxID;
    public $Subdomain;
    public $FQDN;
    public $Address1;
    public $Address2;
    public $City;
    public $State;
    public $Zip;
    public $Country;
    public $Timezone;
    
    
    // Constructed complex attributes
    public $Clinics;  // array of Clinic objects
    public $ActiveClinics;  // array of Active Clinic objects having placements
    public $Practitioners;  // array of Practitioner objects
    public $Users;
    public $LogoURL;  // relative to master 
    public $UpdIndic;
    
    
    public static function getInstance($FieldValue, $FieldName = 'Subdomain') {
        $q = "SELECT * FROM orgs WHERE $FieldName = '$FieldValue'";
        $sql = MainDb::getInstance();
        if ($result = $sql->query($q)->fetch_object()) {
            if (!self::$instance) {
                self::$instance = new self();
            }
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            
            self::$instance->LogoURL = HowLate_Util::logoURL(self::$instance->Subdomain);
            
            return self::$instance;
        } else
            return null;
    }
        
    public function getRelated() {
        $this->getAllClinics();
        $this->getActiveClinics();
        $this->getAllPractitioners();
        $this->getAllUsers();
    }
    
    private function getAllClinics() {
        $q = "SELECT ClinicID FROM clinics WHERE OrgID = '" . $this->OrgID . "'";
        if ($result = MainDb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $c = Clinic::getInstance($this->OrgID, $row->ClinicID);
                $this->Clinics[] = $c;
            }
        }
        $result->close();
    }

    private function getActiveClinics() {
        $this->ActiveClinics=array();
        $q = "SELECT ClinicID FROM vwActiveClinics WHERE OrgID = '" . $this->OrgID . "'";
        
        if ($result = MainDb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $c = Clinic::getInstance($this->OrgID,$row->ClinicID);
                $this->ActiveClinics[] = $c;
            }
        }
        $result->close();
    }

    private function getAllPractitioners() {
        $q = "SELECT OrgID, ID FROM vwOrgAdmin WHERE OrgID = '" . $this->OrgID . "'";
        if ($result = MainDb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $p = Practitioner::getInstance($row->OrgID, $row->ID);
                $this->Practitioners[] = $p;
            }
        }
        $result->close();
    }
    
    private function getAllUsers() {
        $q = "SELECT * FROM vwOrgUsers WHERE OrgID = '" . $this->OrgID . "'";

        if ($result = MainDb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $u = new OrgUser($row);
                $this->Users[] = $u;
            }
        }
    }

    public static function findUsers($FieldValue, $FieldName = 'OrgID') {
        $q = "SELECT * FROM vwOrgUsers WHERE $FieldName = '" . $FieldValue . "'";

        $myArray = array();
        if ($result = MainDb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
            return $myArray;
        }
    }
    
    public function isValidPassword($userid, $passwordhash) {
        $q = "SELECT XPassword FROM orgusers WHERE OrgID = ? AND UserID = ?";
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ss', $this->OrgID, $userid);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            Logging::trlog(TranType::USER_DNE, "User login failed.  User $userid for $orgid does not exist.");
            return false;   // user does not exist TODO: add logging
        }
        $stmt->bind_result($col1);
        $stmt->fetch();
        return ($col1 == $passwordhash);
    }

    
    public function getLatenesses($clinic) {
        $q = "SELECT ClinicID, ClinicName, ID, AbbrevName, FullName, MinutesLate, MinutesLateMsg, OrgID, Subdomain, Override, NotificationThreshold, LateToNearest, LatenessOffset, LatenessCeiling FROM vwLateness WHERE OrgID = '" . $this->OrgID . "' AND ClinicID = '" . $clinic . "'";
        $practArray = array();
        $clinArray = array();
        if ($result = MainDb::getInstance()->query($q)) {
            $tempArray = array();
            while ($row = $result->fetch_object()) {
                $tempArray[] = $row;
                if (array_key_exists($row->ClinicName, $clinArray)) {
                    $clinArray[$row->ClinicName] = $tempArray;
                } else {
                    unset($tempArray);
                    $tempArray = array();
                    $tempArray[] = $row;
                    $clinArray[$row->ClinicName] = $tempArray;
                }
            }
            return $clinArray;
        }
    }

    public function getTimezones() {
        $q = "SELECT CodeDesc FROM timezones WHERE CodeVal like '" . $this->Country . "%'";
        $myArray = array();
        if ($result = MainDb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row->CodeDesc;
            }
            return $myArray;
        }
        $result->close();
    }

    public function getCountries() {
        $q = "SELECT Name, MobilePrefix FROM country";
        $myArray = array();
        if ($result = MainDb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row->Name;
            }
            return $myArray;
        }
        $result->close();
    }
    
    
    public function update_org($values) {
        // $org is the organisation object
        // $values is the array of new values
        
        
        $q = "UPDATE orgs SET ";
        $i=0;
        foreach ($values as $key => &$val) {
            if ($key != "OrgID") {
                if($i>0) {
                    $q .= ", ";
                }
                $q .= "$key = :$key";
                
                $i++;
            }
        }
        $q .= " WHERE OrgID = :OrgID";
        
        $stmt = db::getInstance()->prepare($q);
        foreach ($values as $key => &$val) {  // $val must be by reference.  who knew???
            $stmt->bindParam(":" . $key, $val);
        }
        
        $stmt->execute();
           
    }


    // The userid is used to create a default user for this billing system customer portal
    public function update_billing($default_user) {
        $chargeover = new Chargeover();
        $cust = $chargeover->getCustomer($this->OrgID);
        
        if (!$cust) {
            $chargeover->createCustomer($this->OrgID, $this->OrgName, $this->Address1, 
                    $this->Address2, '', 
                    $this->City, $this->State, $this->Zip, $this->Country, $default_user->FullName, 
                    $default_user->UserID, $default_user->EmailAddress);
        }
        else {
            $chargeover->updateCustomer($this->OrgID, $this->OrgName, $this->Address1, 
                    $this->Address2, '', 
                    $this->City, $this->State, $this->Zip, $this->Country);
        }
    }
    
    public static function createOrg($orgid, $orgname, $shortname, $subdomain, $billingcontact, $fqdn) {
        $q = "INSERT INTO orgs (OrgID, OrgName, OrgShortName, Subdomain, BillingContact, FQDN) VALUES (?, ?, ?, ?, ?, ?)";
        $sql = MainDb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ssssss', $orgid, $orgname, $shortname, $subdomain, $billingcontact, $fqdn);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The orgs record was not created, error= " . $this->conn->error, E_USER_ERROR);
        }
        
        return self::getInstance($orgid,'OrgID');  // make fluid
    }
    

    public static function getNextOrgID() {
        $q = "SELECT IFNULL(MAX(OrgID),'AAAAA') As last FROM orgs";
        $sql = MainDb::getInstance();
        if ($result = $sql->query($q)) {
            $row = $result->fetch_object();
            $orgid = $row->last;
            $canonical = substr($orgid, 0, 4);
            $as_number = HowLate_Util::tobase10($canonical);
            $as_number++;
            $new_high = HowLate_Util::tobase26($as_number);
            $checkdigit = HowLate_Util::checkdigit($new_high);
            return $new_high . $checkdigit;
        }
    }

        
    // Pin is of form AAABB.A
    // udid is the unique device id
//    public function register($orgid, $id, $udid) {
//        $q = "REPLACE INTO devicereg (ID, OrgID, UDID, Expires) VALUES (?,?,?, CURDATE() + INTERVAL 6 MONTH )";
//        
//        echo $q;
//        $stmt = maindb::getInstance()->query($q);
//        $stmt = $this->conn->prepare($q);
//        $stmt->bind_param('sss', $id, $orgID, $udid);
//        $stmt->execute();
//        echo "after execute...";
//        $db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'registered.', $org, null, $id, $udid);

//        $prac = practitioner::getInstance($org, $id);
//
//        $message = 'Click for lateness updates from ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
//        $message .= ': ';
//        $message .= "http://m." . __DOMAIN . "/late/view&udid=$udid";
//        howlate_sms::httpSend($org, $udid, $message);
//    }

    public function unregister($orgid, $id, $udid) {
        $q = "DELETE FROM devicereg WHERE ID = ? AND OrgID = ? AND UDID = ?";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $id, $orgID, $udid);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error('The device was not registered for information from organisation = ' . $orgID . ' and ID = ' . $id, E_USER_WARNING);
        }
        $db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'unregistered ', $org, null, $id, $udid);

        $prac = Practitioner::getInstance($org, $id);

        $message = 'You have chosen to unregister for lateness updates from ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
        HowLate_SMS::httpSend($org, $udid, $message);
    }
    
    public function sendResetEmails($email) {
        $users = self::findUsers($email, 'EmailAddress');
        if (count($users) == 0) {
            return 0;
        }
        $subject = "Trouble logging in? Your username and password for " . $this->OrgName;

        $body = "";
        if (count($users) > 1) {
            $body = "It looks like you have " . count($users) . " different logins for " . $this->OrgName . "'s secure online services.\r\n\r\n";
            $body .= "-------- User Accounts ---------\r\n\r\n";
        }
        
        $toName = $users[0]->FullName;
        //$from = $users[0]->EmailAddress;
        $from = "noreply@" . __DOMAIN;
        $fromName = $this->OrgName;
        
        foreach ($users as $user) {
            $body .= "Username: " . $user->UserID . "\r\n";
            $body .= "If you have forgotten your password, you can reset it by following this link:\r\n";
            $token = self::saveResetToken($user->OrgID, $user->UserID, $email);
            $link = "http://" . $user->FQDN . "/reset?token=$token" . "\r\n";
            $body .= $link . "\r\n";
        }

        $body .= "Afterwards, you can securely access your account by going to your login page:\r\n\r\n";
        $body .= "http://" . __SUBDOMAIN . "." . __DOMAIN . "\r\n\r\n";
        $body .= "If you did not send this request, you can safely ignore this email.\r\n";

        $headers = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\n";
        $headers .= "From: $from";

        $mail = new Howlate_Mailer();
        $mail->send($email,$toName, $subject,$body, $from, $fromName);
    }
    
    
    public function checkToken($token) {
        $q = "SELECT UserID, DateCreated FROM resetrequests WHERE Token = '" . $token . "' AND OrgID = '" . $this->OrgID . "'";
        if ($result = MainDb::getInstance()->query($q)) {
            $row = $result->fetch_object();
        }
        if (count($row) != 1) {
            return array("The password reset link is invalid.");
        }

        $elapsed = $row->DateCreated - time();
        if ($elapsed > 3600) {
            return array("The password reset link has elapsed.");
        }
        return array("OK", $row->UserID);

    }
    
    
    public function check_token($token, $org) {

        $q = "SELECT UserID, DateCreated FROM resetrequests WHERE Token = '" . $token . "' AND OrgID = '" . $org . "'";
        if ($result = MainDb::getInstance()->query($q)) {
            $row = $result->fetch_object();
        }

        if (count($row) != 1) {
            return array("The password reset link is invalid.");
        }

        $elapsed = $row->DateCreated - time();
        if ($elapsed > 3600) {
            return array("The password reset link has elapsed.");
        }
        return array("OK", $row->UserID);
    }

    public static function deleteLateByKey($key) {
        $q = "DELETE FROM lates WHERE UKey = ?";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('i', $key);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The lates record was not deleted, error= " . $this->conn->error, E_USER_ERROR);
        }
    }
    
    public static function saveResetToken($org, $user, $email) {
        $key = uniqid(mt_rand(), true);

        $token = md5($email . $key);

        $q = "INSERT INTO resetrequests (Token, EmailAddress, UserID, OrgID, DateCreated) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";

        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->bind_param('ssss', $token, $email, $user, $org);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The Reset Request was not inserted into the database, token= $token , email = $email ,user = $user", E_USER_ERROR);
        }
        return $token;
    }

    
}

?>
