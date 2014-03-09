<h1>How-late API returns mostly JSON </h1>
<?php
if (!function_exists('__autoload')) {
	function __autoload($classname) {
		$filename = "./lib/". $classname . ".php";
		include_once($filename);
	}
}

function unh_exception_handler($exception) {
  echo "Unhandled exception: " , $exception->getMessage(), "\n";
  
}
set_exception_handler('unh_exception_handler');

include_once("./lib/error_handler.php");


$debug = $_GET["debug"];
 
/* at the very least a Phone UDID must be supplied, a method name and a client app version */
$udid = $_GET["udid"];
$met = $_GET["met"];
$ver = $_GET["ver"];  
 
if (! $udid) {
	trigger_error('API Error: You must supply the \$udid parameter to uniquely identify your device.', E_USER_ERROR);
}
if (! $met) {
	trigger_error('API Error: You must supply the \$met parameter for the method you wish to call.', E_USER_ERROR);
}
if (! $ver) {
	trigger_error('API Error: You must supply the \$ver parameter to identify the version of the App.', E_USER_ERROR);
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
	case "unreg":
		unregisterpin();  // a device is deregistering for updates from a practitioner.
		break;
	case "upd":
		updatelateness();  // a device is updating the lateness for a single practitioner.  Needs a password.
		break;
	case "getclinics":
		getclinics();  // returns a list of clinics for this organisation
		break;
	case "place":		// places a practitioner in a clinic
		place();
		break;
	case "displace":	// displaces a practitioner from a clinic
		displace();
		break;
	default:
		trigger_error ('API Error: method "' . $met . '" is not known', E_USER_ERROR);  
};
return;

// FUNCTIONS FOLLOW.  THESE FUNCTIONS ARE TO VALIDATE THAT THE API CALL HAS ALL THE REQUIRED PARAMETERS

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
	echo '<b>TODO: $met</b> returns the html for application help' . "<br>"; 
}

function registerpin()
{
	global $udid, $met, $ver;
	$pin = $_GET["pin"];
	if (! $pin)	{
		trigger_error('API Error: <b>$met</b> - you must supply the $pin parameter <br>', E_USER_ERROR);
	}
	echo "<b>$met</b> registers this phone ($udid) for updates for the practitioner identified by the supplied PIN ($pin)<br>";
	
	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);
	$db = new howlate_db();
	$db->validatePin($org, $id);
	$db->register($udid,$org, $id);
	$db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'registered pin ' . $pin, $org, null, $id, $udid);
	echo "Successfully registered pin $pin<br>";
}

function unregisterpin()
{
	global $udid, $met, $ver;
	$pin = $_GET["pin"];
	if (! $pin)	{
		trigger_error('API Error: <b>$met</b> - you must supply the $pin parameter <br>', E_USER_ERROR);
	}
	
	echo "<b>$met</b> deregisters this phone ($udid) for updates for the practitioner identified by the supplied PIN ($pin)<br>";
	
	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);
	$db = new howlate_db();
	$db->validatePin($org, $id);
	$db->unregister($udid,$org, $id);
	$db->trlog(TranType::DEV_UNREG, 'Device ' . $udid . 'registered pin ' . $pin, $org, null, $id, $udid);
	
	echo "Successfully deregistered pin $pin<br>";
	
}

// Updates the lateness for a specific practitioner
// This is intended to be done from a future smartphone app.
// The website interface for doing this has no need of this API.
function updatelateness()
{
  global $udid, $met, $ver;
  required(array("pin","newlate"));
  $pin = $_GET["pin"];
  $newlate = $_GET["newlate"];
  
}

function getclinics()
{
	global $udid, $met, $ver;
	required(array("pin"));
	$pin = $_GET["pin"];  // identifies the Org and practitioner

	echo "<b>$met</b> uses the PIN ($pin) to decode the organisation and returns a json list of clinics for that org.<br>";

	$db = new howlate_db();
	$db->getClinics($pin);
}

function place() {
	global $udid, $met, $ver;
	required(array("clinic", "pin"));
	
	$clinic = $_GET["clinic"];
	$pin = $_GET["pin"];
	
	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);
	$db = new howlate_db();
	$db->validatePin($org, $id);
	$db->validateClinic($org, $clinic);
	$db->place($org, $id, $clinic);
	echo "Successfully placed practitioner $id in clinic $clinic in org $org<br>";
	
}

function displace() {
	global $udid, $met, $ver;
	required(array("clinic", "pin"));
	
	$clinic = $_GET["clinic"];
	$pin = $_GET["pin"];
	
	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);
	$db = new howlate_db();
	$db->validatePin($org, $id);
	$db->validateClinic($org, $clinic);
	$db->displace($org, $id, $clinic);
	echo "Successfully displaced practitioner $id from clinic $clinic in org $org<br>";
}


function required($arr) {
	global $udid, $met, $ver;

	foreach($arr as $value) {
		if (!$_GET[$value]) {
			trigger_error('API Error: Method ' . $met . ' requires the ' . $value . ' parameter.', E_USER_ERROR);
		}
	}
}



abstract class TranType {
	const CLIN_ADD   =  "CLIN_ADD";
	const CLIN_ARCH  =  "CLIN_ARCH";
	const CLIN_CHG   =  "CLIN_CHG";
	const CLIN_DEL   =  "CLIN_DEL";
	const DEV_REG    =  "DEV_REG";
	const DEV_UNREG  =  "DEV_UNREG";
	const LATE_GET   =  "LATE_GET";
	const LATE_RESET =  "LATE_RESET";
	const LATE_UPD   =  "LATE_UPD";
	const MISC_MISC  =  "MISC_MISC";
	const ORG_ADD    =  "ORG_ADD";
	const ORG_CHG    =  "ORG_CHG";
	const ORG_DEL    =  "ORG_DEL";
	const PRAC_ARCH  =  "PRAC_ARCH";
	const PRAC_CRE   =  "PRAC_CRE";
	const PRAC_DEL   =  "PRAC_DEL";
	const PRAC_PLACE =  "PRAC_PLACE";
	const USER_ADD   =  "USER_ADD";
	const USER_ARCH  =  "USER_ARCH";
	const USER_CHG   =  "USER_CHG";
	const USER_SUSP  =  "USER_SUSP";
}

?>


