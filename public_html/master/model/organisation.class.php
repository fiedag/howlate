<?php

/* Model Class for the Organisation 
 * 
 * This contains all the DML and is used by the orgController
 * and others
 * 
 */
class organisation {

    protected static $instance;
    
    public $OrgID;
    public $OrgName;
    public $OrgShortName;
    public $TaxID;
    public $Subdomain;
    public $FQDN;
    public $BillingRef;
    public $Address1;
    public $Address2;
    public $City;
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
        $sql = maindb::getInstance();
        if ($result = $sql->query($q)->fetch_object()) {
            if (!self::$instance) {
                self::$instance = new self();
            }
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            
            self::$instance->LogoURL = self::$instance->logoURL(self::$instance->Subdomain);
            
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
        $q = "SELECT ClinicID, OrgID, Timezone, ClinicName, Phone, Address1, Address2, City, Zip, Country FROM clinics WHERE OrgID = '" . $this->OrgID . "'";
        if ($result = maindb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $c = new clinic($row);
                $this->Clinics[] = $c;
            }
        }
        $result->close();
    }

    private function getActiveClinics() {
        $q = "SELECT ClinicID, OrgID, Timezone, ClinicName, Phone, Address1, Address2, City, Zip, Country FROM vwActiveClinics WHERE OrgID = '" . $this->OrgID . "'";
        if ($result = maindb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $c = new clinic($row);
                $this->ActiveClinics[] = $c;
            }
        }
        $result->close();
    }

    private function getAllPractitioners() {
        $q = "SELECT OrgID, ID FROM vwOrgAdmin WHERE OrgID = '" . $this->OrgID . "'";
        if ($result = maindb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $p = practitioner::getInstance($row->OrgID, $row->ID);
                $this->Practitioners[] = $p;
            }
        }
        $result->close();
    }
    
    private function getAllUsers() {
        $q = "SELECT * FROM vwOrgUsers WHERE OrgID = '" . $this->OrgID . "'";

        if ($result = maindb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $u = new orguser($row);
                $this->Users[] = $u;
            }
        }
    }

    public static function findUsers($FieldValue, $FieldName = 'OrgID') {
        $q = "SELECT * FROM vwOrgUsers WHERE $FieldName = '" . $FieldValue . "'";

        $myArray = array();
        if ($result = maindb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
            return $myArray;
        }
    }
    
    public static function isValidPassword($orgid, $userid, $passwordhash) {
        $q = "SELECT XPassword FROM orgusers WHERE OrgID = ? AND UserID = ?";
        $sql = maindb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('ss', $orgid, $userid);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            logging::trlog(TranType::USER_DNE, "User login failed.  User $userid for $orgid does not exist.");
            return false;   // user does not exist TODO: add logging
        }
        $stmt->bind_result($col1);
        $stmt->fetch();
        return ($col1 == $passwordhash);
    }

    
    public function getLatenesses($clinic) {
        $q = "SELECT ClinicID, ClinicName, ID, AbbrevName, FullName, MinutesLate, MinutesLateMsg, OrgID, Subdomain, Sticky, NotificationThreshold, LateToNearest, LatenessOffset FROM vwLateness WHERE OrgID = '" . $this->OrgID . "' AND ClinicID = '" . $clinic . "'";
        $practArray = array();
        $clinArray = array();
        if ($result = maindb::getInstance()->query($q)) {
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
        if ($result = maindb::getInstance()->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row->CodeDesc;
            }
            return $myArray;
        }
        $result->close();
    }


    public function update_org($values) {
        // $org is the organisation object
        // $values is the array of new values
        $q = "UPDATE orgs SET ";
        foreach ($values as $key => $val) {
            if ($key != "UpdIndic" and $key != "OrgID") {
                $q .= "$key = '" . $val . "',";
            }
        }
        $q .= "UpdIndic = UpdIndic + 1";
        $q .= " WHERE OrgID = ? AND UpdIndic = ?";
        $sql = maindb::getInstance();
        $stmt = $sql->prepare($q);
        $stmt->bind_param('si', $values["OrgID"], $values["UpdIndic"]);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The orgs record was not updated, error= " . $this->conn->error, E_USER_ERROR);
        }
    }

    
    public static function createOrg($orgid, $orgname, $shortname, $subdomain, $fqdn) {
        $q = "INSERT INTO orgs (OrgID, OrgName, OrgShortName, Subdomain, FQDN, UpdIndic) VALUES (?, ?, ?, ?, ?, 1)";
        $sql = maindb::getInstance();
        $stmt = $sql->query($q);
        $stmt = $this->conn->prepare($q);

        $stmt->bind_param('sssss', $orgid, $orgname, $shortname, $subdomain, $fqdn);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The orgs record was not created, error= " . $this->conn->error, E_USER_ERROR);
        }
        
        return self::getInstance($org,'OrgID');  // make fluid
    }
    
    
    public static function getNextOrgID() {
        $q = "SELECT IFNULL(MAX(OrgID),'AAAAA') As last FROM orgs";
        $sql = maindb::getInstance();
        if ($result = $sql->query($q)) {
            $row = $result->fetch_object();
            $orgid = $row->last;
            $canonical = substr($orgid, 0, 4);
            $as_number = howlate_util::tobase10($canonical);
            $as_number++;
            $new_high = howlate_util::tobase26($as_number);
            $checkdigit = howlate_util::checkdigit($new_high);
            return $new_high . $checkdigit;
        }
    }
    
    private function logoURL($subd) {
        if (file_exists("pri/$subd/logo.png")) {
            return "/pri/$subd/logo.png";
        } else {
            return "/images/logos/logo.png";
        }
    }
    
    // Pin is of form AAABB.A
    // udid is the unique device id
    public function register($orgid, $id, $udid) {
        $q = "REPLACE INTO devicereg (ID, OrgID, UDID, Expires) VALUES (?,?,?, CURDATE() + INTERVAL 6 MONTH )";
        $stmt = maindb::getInstance()->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sss', $id, $orgID, $udid);
        $stmt->execute();
        $db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'registered.', $org, null, $id, $udid);

        $prac = practitioner::getInstance($org, $id);

        $message = 'Click for lateness updates from ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
        $message .= ': ';
        $message .= "http://secure." . __DOMAIN . "/late/view&udid=$udid";
        howlate_sms::httpSend($org, $udid, $message);
    }

    public function unregister($orgid, $id, $udid) {
        $q = "DELETE FROM devicereg WHERE ID = ? AND OrgID = ? AND UDID = ?";
        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('sss', $id, $orgID, $udid);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error('The device was not registered for information from organisation = ' . $orgID . ' and ID = ' . $id, E_USER_WARNING);
        }
        $db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'unregistered ', $org, null, $id, $udid);

        $prac = practitioner::getInstance($org, $id);

        $message = 'You have chosen to unregister for lateness updates from ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
        howlate_sms::httpSend($org, $udid, $message);
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
        $from = $users[0]->EmailAddress;
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

        $mail = new howlate_mailer();
        $mail->send($email,$toName, $subject,$body, $from, $fromName);
    }
    
    
    public function checkToken($token) {
        $q = "SELECT UserID, DateCreated FROM resetrequests WHERE Token = '" . $token . "' AND OrgID = '" . $this->OrgID . "'";
        if ($result = maindb::getInstance()->query($q)) {
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
        if ($result = maindb::getInstance()->query($q)) {
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
        $stmt = maindb::getInstance()->prepare($q);
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

        $stmt = maindb::getInstance()->prepare($q);
        $stmt->bind_param('ssss', $token, $email, $user, $org);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The Reset Request was not inserted into the database, token= $token , email = $email ,user = $user", E_USER_ERROR);
        }
        return $token;
    }

    
}

?>
