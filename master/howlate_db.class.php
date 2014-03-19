<?php

class howlate_db {

	protected $conn;
  
	function __construct() {
		$this->conn = new mysqli('localhost','howlate_super','bdU,[}B}k@7n','howlate_main');
	}
	function __destruct() {
		if (is_resource($this->conn) && get_resource_type($this->conn) == 'mysql link' ) {
			$this->conn->close();
		}
	}
	function getClinics($orgID) {
		$q = "SELECT ClinicID, OrgID, ClinicName FROM clinics WHERE OrgID = '" . $orgID . "'";
	
		$myArray = array();
		if ($result = $this->conn->query($q)) {
			$tempArray = array();
			while($row = $result->fetch_object()) {
                $tempArray = $row;
                array_push($myArray, $tempArray);
            }
			echo json_encode($myArray);
		}
		$result->close();
	}
	
	function getPractitioner($org, $id) {
		$q = "SELECT OrgID, PractitionerID, Pin, PractitionerName, ClinicName, OrgName, FQDN FROM vwPractitioners WHERE OrgID = ? AND PractitionerID = ?";
		$stmt = $this->conn->query($q);
		$stmt = $this->conn->prepare($q);
		$stmt->bind_param('ss', $org, $id);
		$stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') '.  $this->conn->error, E_USER_ERROR);
		$p = new practitioner();
		$stmt->bind_result($p->OrgID, $p->PractitionerID, $p->Pin, $p->PractitionerName, $p->ClinicName, $p->OrgName, $p->FQDN);
		$stmt->fetch();
		return $p;
	}

	function getlatenesses($udid) {
		$q = "SELECT ClinicName, AbbrevName, MinutesLate FROM vwMyLates WHERE UDID = '" . $udid . "'";
		$practArray = array();
		$clinArray = array();
		if ($result = $this->conn->query($q)) {
			$tempArray = array();
			while($row = $result->fetch_object()) {
				print('Clinic = ' . $row->ClinicName) . ',Practitioner = ' . $row->AbbrevName . '<br>';
				array_push($tempArray, $row);
				if (in_array($row->ClinicName, $clinArray)) {
					echo 'Clinic ' . $row->ClinicName . ' is in array, so assigning tempArray with ' . count($tempArray) . ' elements <br> '; 
					$clinArray[$row->ClinicName] = $tempArray;
				}
				else {
					unset($tempArray);
					$tempArray = array();
					array_push($tempArray, $row);
					$clinArray[] = array($row->ClinicName => $tempArray);
				}
      }
			print_r($clinArray);
			return $clinArray;				
		}
		$result->close();
	}
	
	function updatelateness($org, $id, $newlate) {
		$q = "REPLACE INTO lates (OrgID, ID, Updated, Minutes) VALUES (?, ?, curdate(), ?)";
		$stmt = $this->conn->query($q);
		$stmt = $this->conn->prepare($q);
		$stmt->bind_param('sss',$org, $id, $newlate);
		$stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') '.  $this->conn->error, E_USER_ERROR);
	
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
			  trigger_error('Data Error: Practitioner with ID ' . $id . ' does not exist for organisation' . $org , E_USER_ERROR);
			}
        }
		$result->close();
	}
	
	function validateClinic($org, $clinic) {
		$q = "SELECT ClinicName FROM clinics WHERE OrgID = '" . $org . "' AND ClinicID = " . $clinic ;
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
		$stmt->bind_param('sss',$id, $orgID, $udid);
		$stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') '.  $this->conn->error, E_USER_ERROR);
	}

	function unregister($udid, $orgID, $id) {
		$q = "DELETE FROM devicereg WHERE ID = ? AND OrgID = ? AND UDID = ?";
		$stmt = $this->conn->query($q);
		$stmt = $this->conn->prepare($q);
		$stmt->bind_param('sss',$id, $orgID, $udid);
		$stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') '. $this->conn->error, E_USER_ERROR);
		if ($stmt->affected_rows == 0) {
			trigger_error('The device was not registered for information from organisation = ' . $orgID . ' and ID = ' . $id, E_USER_WARNING);
		}
	}
	
	function place($org, $id, $clinic) {
		$q = "REPLACE INTO placements (OrgID, ID, ClinicID) VALUES (?,?,?)";
		$stmt = $this->conn->query($q);
		$stmt = $this->conn->prepare($q);
		$stmt->bind_param('sss',$org, $id, $clinic);
		$stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') '.  $this->conn->error, E_USER_ERROR);
	}

	function displace($org, $id, $clinic) {
		$q = "DELETE FROM placements WHERE OrgID = ? AND ID = ? AND ClinicID = ?";
		$stmt = $this->conn->query($q);
		$stmt = $this->conn->prepare($q);
		$stmt->bind_param('sss',$org, $id, $clinic);
		
		$stmt->execute() or user_error('# Query Error (' . $this->conn->errno . ') '.  $this->conn->error);
		if ($stmt->affected_rows == 0) {
			trigger_error('The practitioner was not placed at clinic ' . $clinic . ' in organisation ' . $orgID, E_USER_WARNING);
		}
	}

	function write_error($errno, $errtype, $errstr, $errfile, $errline) {
		$ipaddress = $_SERVER["REMOTE_ADDR"];
		$q  = "INSERT INTO errorlog (ErrLevel, ErrType, File, Line, ErrMessage, IPv4) VALUES (?,?,?,?,?,?)";
		$stmt = $this->conn->query($q);
		$stmt = $this->conn->prepare($q);
		$stmt->bind_param('ssssss',$errno, $errtype, $errfile, $errline, $errstr, $ipaddress);
		$stmt->execute() or die('# Query Error (' . $this->conn->errno . ') '.  $this->conn->error);  // no point going in circles
	}
	
	function trlog($trantype, $details, $org = null, $clinic = null, $practitioner = null, $udid = null) {
		$q = "INSERT INTO transactionlog (TZ, TransType, OrgID, ClinicID, PractitionerID, Details, UDID) VALUES (?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->conn->query($q);
		$stmt = $this->conn->prepare($q);
		$tz = date_default_timezone_get();
		$stmt->bind_param('sssisss', $tz, $trantype, $org, $clinic, $practitioner, $details, $udid);
		$stmt->execute() or die('# Query Error (' . $this->conn->errno . ') '.  $this->conn->error);  // no point going in circles
	}
	
	function get_user_data($userid, $orgid) {
		$q = "SELECT UserID, DateCreated, EmailAddress, FullName, XPassword, OrgID , SecretQuestion1, SecretAnswer1 FROM orgusers WHERE OrgID = '" . $orgID . "' AND UserID = '" . $userid . "'";
		if ($result = $this->conn->query($q)) {
			$user = $result->fetch_object('howlate_user');
	    if (!isset($user)) {
			  trigger_Error('Data Error: User ' . $userid . ' does not exist for org ' . $org  , E_USER_ERROR);
			}
    }
		
		$result->close();
		
	}
}
?>