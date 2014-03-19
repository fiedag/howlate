/* http API - 
* everything here is called using a http GET
* mostly everything returns a JSON string 
* try to make controllers do all the work.  
* TODO : remove direct calls to db class
*
*
*/
<!DOCTYPE html>
<?php

function unh_exception_handler($exception) {
  echo "Unhandled exception: " , $exception->getMessage(), "\n";
}
set_exception_handler('unh_exception_handler');

$site_path = realpath(dirname(__FILE__));
define ('__SITE_PATH', $site_path);
 
include_once('includes/init.php');
include_once('lib/stdinclude.php');

$debug = $_GET["debug"];
define ('__DEBUG', $debug);

required(array("udid", "met", "ver"));

$udid = $_GET["udid"];
$met = $_GET["met"];
$ver = $_GET["ver"];
 
switch ($met)
{
	case "get":
		getlatenesses();  // this is the most common api call from patients' mobiles apps (not html).  
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
	case "invite":	// sends an SMS invitation which registers a device for a PIN
		sendInvitation();
		break;
	case "getpract":	// gets practitioner information and returns json
		getPractitioner();
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
        $late = new latenesses($udid);
        
        $db = new howlate_db();
	$res = $db->getLatenesses($udid, 'UDID');
	
	$db->trlog(TranType::LATE_GET, 'Lateness got for device ' . $udid);
	echo json_encode($res);

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
	$db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'registered pin ' . $pin);
	echo "Successfully registered pin $pin<br>";
}

function unregisterpin()
{
	global $udid, $met, $ver;
	$pin = $_GET["pin"];
	if (! $pin)	{
		trigger_error('API Error: <b>$met</b> - you must supply the $pin parameter <br>', E_USER_ERROR);
	}
	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);
	$db = new howlate_db();
	$db->validatePin($org, $id);
	$db->unregister($udid,$org, $id);
	$db->trlog(TranType::DEV_UNREG, 'Device ' . $udid . ' unregistered pin ' . $pin);
	
	echo "pin $pin is unregistered.<br>";
	
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
	
	howlate_util::validatePin($pin);
		
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);
	$db = new howlate_db();
	$db->updatelateness($org,$id,$newlate);
	$db->trlog(TranType::LATE_UPD, 'Practitioner ' . $pin . ' is now ' . $newlate . ' minutes late', $org, null, $id, $udid);
}

function getclinics()
{
	global $udid, $met, $ver;
	required(array("pin"));
	$pin = $_GET["pin"];  // identifies the Org and practitioner

	echo "<b>$met</b> uses the PIN ($pin) to decode the organisation and returns a json list of clinics for that org.<br>";

	$db = new howlate_db();
	$result = $db->getallclinics($pin);
	echo json_encode(get_object_vars($result));
}

function getPractitioner() {
	global $udid, $met, $ver;
	required(array("pin"));

  $pin = $_GET["pin"];  // identifies the Org and practitioner

	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);

	$db = new howlate_db();
	$db->validatePin($org, $id);

	$result = $db->getPractitioner($org, $id);
	echo json_encode(get_object_vars($result));
	echo '<br>';
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
	$db->trlog(TranType::PRAC_PLACE, 'Practitioner ' . $id . ' now placed at clinic ' . $clinic, $org, null, $id, $udid);

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
	$db->trlog(TranType::PRAC_DISP, 'Practitioner ' . $id . ' now not placed at clinic ' . $clinic, $org, null, $id, $udid);

}

function sendInvitation() {
	global $udid, $met, $ver;
	required(array("pin"));
	
	$pin = $_GET["pin"];
	
	registerpin();  // uses the mobile number as the UDID.  registers it.

	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);

	$db = new howlate_db();
	$prac = $db->getPractitioner($org, $id);
	
	$clickatell = new clickatell();

	$message = 'To receive lateness updates for ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
  $message .= ', click : ';
  $message .= "http://$prac->FQDN/late/view&udid=$udid";

	$clickatell->httpSend(null, $udid, $message);
	
}

function required($arr) {
	global $udid, $met, $ver;
	foreach($arr as $value) {
		if (!$_GET[$value]) {
			trigger_error('API Error: Method <b>' . $met . '</b> : The following mandatory parameter was not supplied: <b>' . $value . '</b>', E_USER_ERROR);
		}
	}

	if (!empty($missing)) {
		trigger_error('API Error: Method ' . $met . ' the following mandatory parameters were not supplied: ' . print_r($missing), E_USER_ERROR);
	}
	
}
/*
abstract class TranType {
	const CLIN_ADD   =  "CLIN_ADD";	const CLIN_ARCH  =  "CLIN_ARCH";	const CLIN_CHG   =  "CLIN_CHG";	const CLIN_DEL   =  "CLIN_DEL";
	const DEV_REG    =  "DEV_REG";	const DEV_UNREG  =  "DEV_UNREG";
	const LATE_GET   =  "LATE_GET";	const LATE_RESET =  "LATE_RESET";	const LATE_UPD   =  "LATE_UPD";
	const MISC_MISC  =  "MISC_MISC";
	const ORG_ADD    =  "ORG_ADD";	const ORG_CHG    =  "ORG_CHG";	const ORG_DEL    =  "ORG_DEL";
	const PRAC_ARCH  =  "PRAC_ARCH";const PRAC_CRE   =  "PRAC_CRE";	const PRAC_DEL   =  "PRAC_DEL";	const PRAC_DISP  =  "PRAC_DISP"; const PRAC_PLACE =  "PRAC_PLACE";
	const USER_ADD   =  "USER_ADD";	const USER_ARCH  =  "USER_ARCH";const USER_CHG   =  "USER_CHG";	const USER_SUSP  =  "USER_SUSP";
}
*/
?>


