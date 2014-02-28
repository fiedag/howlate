<h1>How-late API returns mostly JSON </h1>
<?php
  /* at the very least a Phone UDID must be supplied, a method name and a client app version */
  $id = $_GET["id"];
  $met = $_GET["met"];
  $ver = $_GET["ver"];  
  
  if (! $id) 
  {
    die('API Error: You must supply the \$id parameter to uniquely identify your device.');
    }
  if (! $met)
  {
    die('API Error: You must supply the \$met parameter for the method you wish to call.');
	}
  if (! $ver)
  {
    die('API Error: You must supply the \$ver parameter to identify the version of the App.');
	}
  
  $json = array();
  $json[]= array(
   'id' => $id,
   'met' => $met,
   'ver' => $ver
  );

  $jsonstring = json_encode($json);
  echo "mandatory parameters :" . "<br>";
  echo $jsonstring;  
  echo "<br><br>";
  
  switch ($met)
  {
    case "get":
      getlatenesses();  // this is the most common api call from patients' mobiles.
  	break;
    case "help":        // what happens when help is requested.  Returns html not json.  A container then displays it.
      help();
  	break;
    case "reg":         // a device is registering for updates from a practitioner.
      registerpin();
  	break;
    case "dereg":
      deregisterpin();  // a device is deregistering for updates from a practitioner
  	break;
    case "upd":
      updatelateness();  // a device is updating the lateness for a single practitioner
  	break;
    case "assign":
	  assign();
	  break;
    case "getclinics":
      getclinics();  // returns a list of clinics for this organisation
  	break;
	default:
	  die ('API Error: method "' . $met . '" is not known');  
  };

function getlatenesses()
{
  global $id, $met, $ver;
  echo '<b>get</b> returns the json array of the current latenesses for all the practitioners the patient has registered for' . "<br>"; 
}

function registerpin()
{
  global $id, $met, $ver;
  $pin = $_GET["pin"];
  if (! $pin)
  {
    die('API Error: <b>$met</b> - you must supply the $pin parameter <br>');
	}

  echo "<b>$met</b> registers this phone ($id) for updates for the practitioner identified by the supplied PIN ($pin)<br>";
	
// $db = new howlate_db_object;
// $db->validatepin($pin);
// $db->add($id,$pin);
  
}

function deregisterpin()
{
  global $id, $met, $ver;
  $pin = $_GET["pin"];
  if (! $pin)
  {
    die('API Error: <b>$met</b> - you must supply the $pin parameter <br>');
	}

  echo "<b>$met</b> deregisters this phone ($id) for updates for the practitioner identified by the supplied PIN ($pin)<br>";
	
// $db = new howlate_db_object;
// $db->validatepin($pin);
// $db->delete($id,$pin);
  
}

// Updates the lateness for a specific practitioner
// This is intended to be done from a future smartphone app.
// The website interface for doing this has no need of this API.
function updatelateness()
{
  global $id, $met, $ver;
  $pin = $_GET["pin"];
  if (! $pin)
  {
    die('API Error: <b>$met</b> - you must supply the $pin parameter <br>');  // PIN identifies the org and practitioner
	}

// $db->validatePin($pin);
// $db->update($pin,$newlate); 
}

// Assigns or reassigns a practitioner to a different clinic
// This is intended to be done from a future smartphone app.
// The website interface for doing this has no need of this API.
function assign()
{
  global $id, $met, $ver;
  $pin = $_GET["pin"];  // identifies the practitioner and Org
  if (! $pin)
  {
    die('API Error: <b>$met</b> method - you must supply the $pin parameter <br>');
	}
  $clinic = $_GET["clin"];  // identifies the clinic of the organisation
  if (! $clinic)
  {
    die('API Error: <b>$met</b> method - you must supply the $clin parameter <br>');
	}
	
  echo "<b>$met</b> assigns the practitioner identified by pin ($pin) , to a clinic ($clinic)" . "<br>";
  // $db->validatePin($pin);
  // $db->update($pin,$newlate); 
}

function getclinics()
{
  global $id, $met, $ver;
  $pin = $_GET["pin"];  // identifies the practitioner and Org
  if (! $pin)
  {
    die('API Error: <b>$met</b> method - you must supply the $pin parameter <br>');
	}

  echo "<b>$met</b> uses the PIN ($pin) to decode the organisation and returns a json list of clinics for that org.<br>";

  $db = new howlate_db();
  $db->getClinics($pin);
  
}



class howlate_db {
  public $queryResult;
  protected $dbHostname = "localhost";
  protected $dbUsername = "howlate_super";
  protected $dbPassw = "bdU,[}B}k@7n";
  protected $dbName = "howlate_main";
  private $mysqli;
  function __construct() {
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_INIT_CMD, "SET AUTOCOMMIT=1");
	$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    $mysqli->real_connect($dbHostname, $dbUsername, $dbPassw, $dbName);
    if (mysqli_connect_errno) {
	  die('Failed to connect to database: ' . mysqli_connect_error());
	}
  }
  function getClinics($orgID) {

    $q = "SELECT ClinicID, OrgID, ClinicName FROM clinics WHERE OrdID = '" . $orgID . "'";

    $myArray = array();
    if ($result = $mysqli->query($q) {
      $tempArray = array();
      while($row = $result->fetch_object()) {
        $tempArray = $row;
        array_push($myArray, $tempArray);
      }
      echo json_encode($myArray);
    }
	$result->close();
  }

  function __destruct() {
    $mysqli->close();
  
  }
}



?>


