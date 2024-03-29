<?php

class howlate_db {

    protected $conn;

    function __construct() {
        $this->conn = new mysqli('localhost', howlate_util::mysqlUser(), howlate_util::mysqlPassword(), howlate_util::mysqlDb());
    }

    function __destruct() {
        if (is_resource($this->conn) && get_resource_type($this->conn) == 'mysql link') {
            $this->conn->close();
        }
    }

    function getallclinics($orgID) {
        $q = "SELECT ClinicID, OrgID, Timezone, ClinicName, Phone, Address1, Address2, City, Zip, Country FROM clinics WHERE OrgID = '" . $orgID . "'";

        $myArray = array();
        if ($result = $this->conn->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
//
            return $myArray;
        }
        $result->close();
    }

    function getactiveclinics($orgID) {
        $q = "SELECT ClinicID, OrgID, Timezone, ClinicName, Phone, Address1, Address2, City, Zip, Country FROM vwActiveClinics WHERE OrgID = '" . $orgID . "'";

        $myArray = array();
        if ($result = $this->conn->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }

            return $myArray;
        }
        $result->close();
    }

    function getallusers($keyval, $fieldname = 'OrgID') {
        $q = "SELECT * FROM vwOrgUsers WHERE $fieldname = '" . $keyval . "'";

        $myArray = array();
        if ($result = $this->conn->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
            return $myArray;
        }
        $result->close();
    }

    function getPractitioner($org, $id, $fieldname = 'PractitionerID') {
        $q = "SELECT OrgID, PractitionerID, Pin, PractitionerName, ClinicName, ClinicID, OrgName, FQDN, NotificationThreshold, LateToNearest, LatenessOffset FROM vwPractitioners WHERE OrgID = ? AND $fieldname = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ss', $org, $id);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
        $p = new practitioner();
        $stmt->bind_result($p->OrgID, $p->PractitionerID, $p->Pin, $p->PractitionerName, $p->ClinicName, $p->ClinicID, $p->OrgName, $p->FQDN, $p->NotificationThreshold, $p->LateToNearest, $p->LatenessOffset);
        $stmt->fetch();
        return $p;
    }

    function getPractitionerID($org, $value, $fieldname = 'FullName') {
        $q = "SELECT ID from practitioners WHERE OrgID = ? AND $fieldname = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ss', $org, $value);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);

        $stmt->bind_result($ID);
        $stmt->fetch();
        return $ID;
    }

    // returns an array with a key of clinic names, and the value is an array of practitioners the $udid
    // is registered for.
//    function getlatenessesByUDID($udid) {
//
//        $q = "SELECT ClinicID, ClinicName, AbbrevName, MinutesLate, MinutesLateMsg, OrgID, Subdomain FROM vwMyLates WHERE UDID = '" . $udid . "'";
//        $practArray = array();
//        $clinArray = array();
//        if ($result = $this->conn->query($q)) {
//            $tempArray = array();
//            while ($row = $result->fetch_object()) {
//                $tempArray[] = $row;
//                if (array_key_exists($row->ClinicName, $clinArray)) {
//                    $clinArray[$row->ClinicName] = $tempArray;
//                } else {
//                    unset($tempArray);
//                    $tempArray = array();
//                    $tempArray[] = $row;
//                    $clinArray[$row->ClinicName] = $tempArray;
//                }
//            }
//            return $clinArray;
//        }
//        $result->close();
//    }

    function getlatenessesByClinic($orgID, $clinicID) {

        $q = "SELECT ClinicID, ClinicName, ID, AbbrevName, FullName, MinutesLate, MinutesLateMsg, OrgID, Subdomain, Override, NotificationThreshold, LateToNearest, LatenessOffset FROM vwLateness WHERE OrgID = '" . $orgID . "' AND ClinicID = '" . $clinicID . "'";
        $clinArray = array();
        if ($result = $this->conn->query($q)) {
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

        $result->close();
    }

    function getOrganisation($val, $field = 'Subdomain') {

        $q = "SELECT * FROM orgs WHERE $field = '$val'";
        if ($result = $this->conn->query($q)) {
            return $result->fetch_object();
        }
        return array();
    }


    function register($udid, $orgID, $id) {
        $q = "REPLACE INTO devicereg (ID, OrgID, UDID, Expires) VALUES (?,?,?, CURDATE() + INTERVAL 6 MONTH )";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sss', $id, $orgID, $udid);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
    }

    function unregister($udid, $orgID, $id) {
        $q = "DELETE FROM devicereg WHERE ID = ? AND OrgID = ? AND UDID = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sss', $id, $orgID, $udid);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
        if ($stmt->affected_rows == 0) {
            trigger_error('The device was not registered for information from organisation = ' . $orgID . ' and ID = ' . $id, E_USER_WARNING);
        }
    }

    function place($org, $id, $clinic) {
        $q = "REPLACE INTO placements (OrgID, ID, ClinicID) VALUES (?,?,?)";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sss', $org, $id, $clinic);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
    }

    function place2($org, $surrogkey, $clinic) {
        $q = "REPLACE INTO placements (OrgID, ID, ClinicID) SELECT OrgID, ID, '$clinic' FROM practitioners WHERE OrgID = ? AND SurrogKey = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ss', $org, $surrogkey);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
    }

    function displace($org, $id, $clinic) {
        $q = "DELETE FROM placements WHERE OrgID = ? AND ID = ? AND ClinicID = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sss', $org, $id, $clinic);

        $stmt->execute() or user_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error);
        if ($stmt->affected_rows == 0) {
            trigger_error('The practitioner was not placed at clinic ' . $clinic . ' in organisation ' . $orgID, E_USER_WARNING);
        }
    }

    function write_error($errno, $errtype, $errstr, $errfile, $errline) {
        $ipaddress = $_SERVER["REMOTE_ADDR"];
        if (is_null($ipaddress) or $ipaddress == "") {
            $ipaddress = "localhost";
        }
        $q = "INSERT INTO errorlog (ErrLevel, ErrType, File, Line, ErrMessage, IPv4) VALUES (?,?,?,?,?,?)";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ssssss', $errno, $errtype, $errfile, $errline, $errstr, $ipaddress);
        $stmt->execute() or die('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error);  // no point going in circles
    }

    function trlog($trantype, $details, $org = null, $clinic = null, $practitioner = null, $udid = null) {
        $ipaddress = $_SERVER["REMOTE_ADDR"];
        if (is_null($ipaddress) or $ipaddress == "") {
            $ipaddress = "localhost";
        }
        $q = "INSERT INTO transactionlog (TransType, OrgID, ClinicID, PractitionerID, Details, UDID, IPv4) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $tz = date_default_timezone_get();
        $stmt->bind_param('ssissss', $trantype, $org, $clinic, $practitioner, $details, $udid, $ipaddress);
        $stmt->execute() or die('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error);  // no point going in circles
    }

    function get_user_data($userid, $orgid) {
        $q = "SELECT UserID, DateCreated, EmailAddress, FullName, XPassword, OrgID , SecretQuestion1, SecretAnswer1 FROM orgusers WHERE OrgID = '" . $orgid . "' AND UserID = '" . $userid . "'";

        if ($result = $this->conn->query($q)) {
            return $result->fetch_object();
        } else {
            $result->close();
            trigger_error("User $userid not found for $orgid", E_USER_ERROR);
        }
    }

    // Checks that the hashed password matches what is in the database
    function isValidPassword($orgid, $userid, $passwordhash) {
        $q = "SELECT XPassword FROM orgusers WHERE OrgID = ? AND UserID = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ss', $orgid, $userid);
        $stmt->execute() or die('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error);
        if ($stmt->affected_rows == 0) {
            $this->trlog(TranType::USER_DNE, "User login failed.  User $userid for $orgid does not exist.");
            return false;   // user does not exist TODO: add logging
        }
        $stmt->bind_result($col1);
        $stmt->fetch();
        return ($col1 == $passwordhash);
    }

    public function gettimezones($country = 'Australia') {
        $q = "SELECT CodeDesc FROM timezones WHERE CodeVal like '" . $country . "%'";
        $myArray = array();
        if ($result = $this->conn->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row->CodeDesc;
            }
            return $myArray;
        }
        $result->close();
    }

    public function save_reset_token($user, $email, $org) {
        $key = uniqid(mt_rand(), true);

        $token = md5($email . $key);

        $q = "INSERT INTO resetrequests (Token, EmailAddress, UserID, OrgID, DateCreated) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";

        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ssss', $token, $email, $user, $org);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
        if ($stmt->affected_rows == 0) {
            trigger_error("The Reset Request was not inserted into the database, token= $token , email = $email ,user = $user", E_USER_ERROR);
        }
        return $token;
    }

    public function check_token($token, $org) {

        $q = "SELECT UserID, DateCreated FROM resetrequests WHERE Token = '" . $token . "' AND OrgID = '" . $org . "'";
        if ($result = $this->conn->query($q)) {
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

        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('si', $values["OrgID"], $values["UpdIndic"]);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The orgs record was not updated, error= " . $this->conn->error, E_USER_ERROR);
        }
    }

    public function create_org($orgid, $orgname, $shortname, $subdomain, $fqdn) {
        $q = "INSERT INTO orgs (OrgID, OrgName, OrgShortName, Subdomain, FQDN, UpdIndic) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);

        $stmt->bind_param('sssss', $orgid, $orgname, $shortname, $subdomain, $fqdn);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The orgs record was not created, error= " . $this->conn->error, E_USER_ERROR);
        }
    }

    public function create_user($orgid, $userid, $emailaddress) {
        $q = "INSERT INTO orgusers (OrgID, UserID, EmailAddress) VALUES (?, ?, ?)";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);

        $stmt->bind_param('sss', $orgid, $userid, $emailaddress);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The user record was not created, error= " . $this->conn->error, E_USER_ERROR);
        }
    }

    public function create_default_clinic($orgid) {
        $q = "INSERT INTO clinics (OrgID, ClinicName) SELECT OrgID, OrgName FROM orgs WHERE OrgID = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);

        $stmt->bind_param('s', $orgid);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The default clinic record was not created, error= " . $this->conn->error, E_USER_ERROR);
        }
    }

    public function create_default_practitioner($orgid, $name) {
        $q = "CALL sp_CreatePractitioner ('$orgid','$name')";
        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The default practitioner was not created, error= " . $this->conn->error, E_USER_ERROR);
        }
    }

    public function getNextOrgID() {
        $q = "SELECT IFNULL(MAX(OrgID),'AAAAA') As last FROM orgs";
        if ($result = $this->conn->query($q)) {
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

    public function getNextPractID($orgID) {
        $q = "SELECT getNextPractitionerID2('$orgID') AS ID";
        if ($result = $this->conn->query($q)) {
            $row = $result->fetch_object();
        }
        if (count($row) != 1) {
            return ("Error returning next practitioner ID.");
        }

        return ($row->ID);
    }

    //
    // Get all latenesses which may have expired 
    //
    public function getAllLatenesses() {
        $q = "SELECT * FROM vwLateness";
        $myArray = array();
        if ($result = $this->conn->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
            return $myArray;
        }
        $result->close();
    }

    public function deleteLate($orgid, $id) {
        $q = "DELETE FROM lates WHERE OrgID = ? AND ID = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ss', $orgid, $id);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The lates record was not deleted, error= " . $this->conn->error, E_USER_ERROR);
        }
    }

    public function deleteLateByKey($key) {
        $q = "DELETE FROM lates WHERE UKey = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $key);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            trigger_error("The lates record was not deleted, error= " . $this->conn->error, E_USER_ERROR);
        }
    }

    // returns all countries in the database
    // useful for the setup wizard, in order to find organisations
    public function getallcountries() {
        $q = "SELECT DISTINCT Country FROM orgs WHERE Country <> ''";

        $myArray = array();
        if ($result = $this->conn->query($q)) {
            $tempArray = array();
            while ($row = $result->fetch_object()) {
                $tempArray = $row;
                array_push($myArray, $tempArray);
            }
            return $myArray;
        }
        $result->close();
    }

    // returns all countries in the database
    // useful for the setup wizard, in order to find organisations
    public function getallorgs($country) {
        $q = "SELECT OrgName, OrgID FROM orgs WHERE Country = '$country'";

        $myArray = array();
        if ($result = $this->conn->query($q)) {
            $tempArray = array();
            while ($row = $result->fetch_object()) {
                $tempArray = $row;
                array_push($myArray, $tempArray);
            }
            return $myArray;
        }
        $result->close();
    }

    public function updatesessions($org, $id, $day, $start, $end) {
        $q = "REPLACE INTO sessions (OrgID, ID, Day, StartTime, EndTime) VALUES (?,?,?,?,?)";

        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sssii', $org, $id, $day, $start, $end);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
    }


    public function deleteOldLates() {
        $q = "DELETE FROM lates WHERE Updated < ADDTIME(NOW(),'-08:00:00')";
        $stmt = $this->conn->prepare($q);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
    }


}
