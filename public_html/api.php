<h1>How-late API returns mostly JSON </h1>
<?php
  function __autoload($classname) {
	$filename = "./". $classname . $ver . ".php";
	include_once($filename);
  }
	
  $debug = $_GET["debug"];
  
  /* at the very least a Phone UDID must be supplied, a method name and a client app version */
  $udid = $_GET["udid"];
  $met = $_GET["met"];
  $ver = $_GET["ver"];  
  
  if (! $udid) 
  {
    die('API Error: You must supply the \$udid parameter to uniquely identify your device.');
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
   'udid' => $udid,
   'met' => $met,
   'ver' => $ver
  );

  $jsonstring = json_encode($json);
  echo "parameters :" . "<br>";
  echo $jsonstring;  
  echo "<br><br>";
  
  //echo "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "<br><br>";
  //$browser = get_browser(null, true);
  //print_r($browser);

  switch ($met)
  {
    case "get":
      getlatenesses();  // this is the most common api call from patients' mobiles.
  	break;
    case "help":        // what happens when help is requested.  Returns html not json.  A container then displays it.
      help();
  	break;
    case "reg":         // a device is registering for updates from a practitioner.  Needs no password.
      registerpin();
  	break;
    case "dereg":
      deregisterpin();  // a device is deregistering for updates from a practitioner.
  	break;
    case "upd":
      updatelateness();  // a device is updating the lateness for a single practitioner.  Needs a password.
  	break;
    case "assign":  // assign a practitioner to a clinic
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
  global $udid, $met, $ver;
  echo '<b>get</b> returns the json array of the current latenesses for all the practitioners the patient has registered for with device $udid' . "<br>"; 
  $db = new howlate_db();
  
  $db->getLatenesses($udid);
  
  }

function help()
{
  global $udid, $met, $ver;
  echo '<b>$met</b> returns the html for application help' . "<br>"; 
}

function registerpin()
{
  global $udid, $met, $ver;
  $pin = $_GET["pin"];
  if (! $pin)
  {
    die('API Error: <b>$met</b> - you must supply the $pin parameter <br>');
	}

  echo "<b>$met</b> registers this phone ($udid) for updates for the practitioner identified by the supplied PIN ($pin)<br>";

  if (!(strlen($pin) == 6 || strlen($pin) == 7)) {
    die('API Error: The PIN entered is not valid. Should be six or seven characters long.'); 
  }
  $org = substr($pin,0,5);
  $id = substr($pin,5,2);
  $db = new howlate_db();
  $db->validatePin($org, $id);
  $db->register($udid,$org, $id);
  echo "Successfully registered pin<br>";
}

function deregisterpin()
{
  global $udid, $met, $ver;
  $pin = $_GET["pin"];
  if (! $pin)
  {
    die('API Error: <b>$met</b> - you must supply the $pin parameter <br>');
	}

  echo "<b>$met</b> deregisters this phone ($udid) for updates for the practitioner identified by the supplied PIN ($pin)<br>";
	
  if (!(strlen($pin) == 6 || strlen($pin) == 7)) {
    die('API Error: The PIN entered is not valid. Should be six or seven characters long.'); 
  }
  $org = substr($pin,0,5);
  $id = substr($pin,5,2);
  $db = new howlate_db();
  $db->validatePin($org, $id);
  $db->deregister($udid,$org, $id);

  echo "Successfully deregistered pin<br>";
	
}

// Updates the lateness for a specific practitioner
// This is intended to be done from a future smartphone app.
// The website interface for doing this has no need of this API.
function updatelateness()
{
  global $udid, $met, $ver;
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
function assign()
{
  global $udid, $met, $ver;
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
  global $udid, $met, $ver;
  $pin = $_GET["pin"];  // identifies the Org and practitioner
  if (! $pin)
  {
    die('API Error: <b>$met</b> method - you must supply the $pin parameter <br>');
	}

  echo "<b>$met</b> uses the PIN ($pin) to decode the organisation and returns a json list of clinics for that org.<br>";

  $db = new howlate_db();
  $db->getClinics($pin);
  
}



?>


