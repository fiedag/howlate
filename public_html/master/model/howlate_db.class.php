<?php

class howlate_db {

    protected $conn;

    function __construct() {
        $this->conn = new mysqli('localhost', 'howlate_super', 'bdU,[}B}k@7n', 'howlate_main');
    }

    function __destruct() {
        if (is_resource($this->conn) && get_resource_type($this->conn) == 'mysql link') {
            $this->conn->close();
        }
    }

    function getallclinics($orgID) {
        $q = "SELECT ClinicID, OrgID, Timezone, ClinicName, Phone, Address1, Address2, City, Zip, Country, Location FROM clinics WHERE OrgID = '" . $orgID . "'";

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
        $q = "SELECT * FROM orgusers WHERE $fieldname = '" . $keyval . "'";

        $myArray = array();
        if ($result = $this->conn->query($q)) {
            while ($row = $result->fetch_object()) {
                $myArray[] = $row;
            }
            return $myArray;
        }
        $result->close();
    }

    function getPractitioner($org, $id) {
        $q = "SELECT OrgID, PractitionerID, Pin, PractitionerName, ClinicName, OrgName, FQDN FROM vwPractitioners WHERE OrgID = ? AND PractitionerID = ?";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ss', $org, $id);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
        $p = new practitioner();
        $stmt->bind_result($p->OrgID, $p->PractitionerID, $p->Pin, $p->PractitionerName, $p->ClinicName, $p->OrgName, $p->FQDN);
        $stmt->fetch();
        return $p;
    }

    // returns an array with a key of clinic names, and the value is an array of practitioners the $udid
    // is registered for.
    function getlatenessesByUDID($udid) {

        $q = "SELECT ClinicID, ClinicName, AbbrevName, MinutesLate, MinutesLateMsg, OrgID, Subdomain FROM vwMyLates WHERE UDID = '" . $udid . "'";
        $practArray = array();
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

    function getlatenessesByClinic($orgID, $clinicID) {

        $q = "SELECT ClinicID, ClinicName, ID, AbbrevName, FullName, MinutesLate, OrgID, Subdomain FROM vwLateness WHERE OrgID = '" . $orgID . "' AND ClinicID = '" . $clinicID . "'";
        $practArray = array();
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
        //$result->close();
    }

    // returns an array with a key of clinic names, and the value is an array of practitioners
    function getallpractitioners($value, $field = 'OrgID') {
        $q = "SELECT * FROM vwOrgAdmin WHERE $field = '" . $value . "'";

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

    function updatelateness($org, $id, $newlate) {
        $q = "REPLACE INTO lates (OrgID, ID, Updated, Minutes) VALUES (?, ?, curdate(), ?)";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('sss', $org, $id, $newlate);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
        $this->trlog(TranType::LATE_UPD, "Lateness updated to $newlate", $org, null, $id);
        
    }

    function validatePin($org, $id) {
        $q = "SELECT OrgName FROM orgs WHERE OrgID = '" . $org . "'";
        if ($result = $this->conn->query($q)) {
            $row = $result->fetch_object();
            if ($row == "") {
                trigger_Error('Data Error: Organisation with ID' . $org . ' does not exist.', E_USER_ERROR);
            }
        }
        $result->close();

        $q = "SELECT ID FROM practitioners WHERE OrgID = '" . $org . "' AND ID = '" . $id . "'";
        if ($result = $this->conn->query($q)) {
            $row = $result->fetch_object();
            if ($row == "") {
                trigger_error('Data Error: Practitioner with ID ' . $id . ' does not exist for organisation' . $org, E_USER_ERROR);
            }
        }
        $result->close();
    }

    function validateClinic($org, $clinic) {
        $q = "SELECT ClinicName FROM clinics WHERE OrgID = '" . $org . "' AND ClinicID = " . $clinic;
        if ($result = $this->conn->query($q)) {
            $row = $result->fetch_object();
            if ($row == "") {
                trigger_error('Data Error: Clinic $clinic is not valid for Org' . $org, E_USER_ERROR);
            }
        }
        $result->close();
    }

    function register($udid, $orgID, $id) {
        $q = "REPLACE INTO devicereg (ID, OrgID, UDID) VALUES (?,?,?)";
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
        $q = "INSERT INTO errorlog (ErrLevel, ErrType, File, Line, ErrMessage, IPv4) VALUES (?,?,?,?,?,?)";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ssssss', $errno, $errtype, $errfile, $errline, $errstr, $ipaddress);
        $stmt->execute() or die('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error);  // no point going in circles
    }

    function trlog($trantype, $details, $org = null, $clinic = null, $practitioner = null, $udid = null) {
        $ipaddress = $_SERVER["REMOTE_ADDR"];
        $q = "INSERT INTO transactionlog (TZ, TransType, OrgID, ClinicID, PractitionerID, Details, UDID, IPv4) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $tz = date_default_timezone_get();
        $stmt->bind_param('sssissss', $tz, $trantype, $org, $clinic, $practitioner, $details, $udid, $ipaddress);
        $stmt->execute() or die('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error);  // no point going in circles
    }

    function get_user_data($userid, $orgid) {
        $q = "SELECT UserID, DateCreated, EmailAddress, FullName, XPassword, OrgID , SecretQuestion1, SecretAnswer1 FROM orgusers WHERE OrgID = '" . $orgID . "' AND UserID = '" . $userid . "'";
        if ($result = $this->conn->query($q)) {
            $user = $result->fetch_object('howlate_user');
            if (!isset($user)) {
                trigger_Error('Data Error: User ' . $userid . ' does not exist for org ' . $org, E_USER_ERROR);
            }
        }

        $result->close();
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
    
    
    public function grantall() {
        $userid = "howlate@'localhost'";
        $q = "GRANT SUPER ON *.* TO ? IDENTIFIED BY '3134-5Q^hP$1'";
        $stmt = $this->conn->query($q);
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('s', $userid);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
        
    }
    
    
}

?>