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
		echo $q . "<br>";
	
		//$mysqli = new mysqli('localhost','howlate_super','bdU,[}B}k@7n','howlate_main');
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
		$this->conn->close();
	}
	function getlatenesses($udid) {
		$q = "SELECT ClinicName, AbbrevName FROM vwMyLates WHERE UDID = '" . $udid . "'";
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
		$this->conn->close();
	}
	
	function validatePin($org, $id) {
		$q = "SELECT OrgName FROM orgs WHERE OrgID = '" . $org . "'";
		if ($result = $this->conn->query($q)) {
			$row = $result->fetch_object();
		    if ($row == "") {
			  die('Data Error: Organisation ID <b>$org</b> is not valid.');
			}
        }
		$result->close();

		$q = "SELECT ID FROM practitioners WHERE OrgID = '" . $org . "' AND ID = '" . $id . "'";
		if ($result = $this->conn->query($q)) {
			$row = $result->fetch_object();
		    if ($row == "") {
			  die('Data Error: Practitioner ID <b>$id</b> not valid for organisation <b>$org</b>.');
			}
        }
		$result->close();
		//$this->conn->close();
	}
	function register($udid, $orgID, $id) {
		$q = "REPLACE INTO devicereg (ID, OrgID, UDID) VALUES (?,?,?)";
		$stmt = $this->conn->query($q);
		$stmt = $this->conn->prepare($q);
		$stmt->bind_param('sss',$id, $orgID, $udid);
		$stmt->execute() or user_error('# Query Error (' . $this->conn->errno . ') '.  $this->conn->error);
		
		//$this->conn->close();
	
	}

	function deregister($udid, $orgID, $id) {
		$q = "DELETE FROM devicereg WHERE ID = ? AND OrgID = ? AND UDID = ?";
		$stmt = $this->conn->query($q);
		$stmt = $this->conn->prepare($q);
		$stmt->bind_param('sss',$id, $orgID, $udid);
		$stmt->execute() or user_error('# Query Error (' . $this->conn->errno . ') '.  $this->conn->error);
	
	}
		
	
}
?>