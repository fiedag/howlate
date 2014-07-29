<?php

function unh_exception_handler($exception) {
  echo "Unhandled exception: " , $exception->getMessage(), "\n";
}


set_exception_handler('unh_exception_handler');

$site_path = realpath(dirname(__FILE__));
define ('__SITE_PATH', $site_path);
 
include_once('includes/init.php');

$debug = filter_input(INPUT_GET,"debug");
define ('__DEBUG', $debug);

// these three are required no matter what



$udid = filter_input(INPUT_GET,"udid");
$met = filter_input(INPUT_GET,"met");
$ver = filter_input(INPUT_GET,"ver");

if (empty($udid)) { trigger_error("Parameter udid must be supplied", E_USER_ERROR);}
if (empty($met)) { trigger_error("Parameter met must be supplied", E_USER_ERROR);}
if (empty($ver)) { trigger_error("Parameter ver must be supplied", E_USER_ERROR);}


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
	case "addpract":	// gets practitioner information and returns json
		addPractitioner();
		break;
	default:
		trigger_error ('API Error: method "' . $met . '" is not known', E_USER_ERROR);  
}


// FUNCTIONS FOLLOW.  THESE FUNCTIONS ARE TO VALIDATE THAT THE API CALL HAS ALL THE REQUIRED PARAMETERS

function getlatenesses()
{
	global $udid, $ver;
	echo '<b>get</b> returns the json array of the current latenesses for all the practitioners the patient has registered for with device $udid' . "<br>"; 
        
        $db = new howlate_db();
        
	$res = $db->getLatenessesByUDID($udid);
	
	$db->trlog(TranType::LATE_GET, 'Lateness got for device ' . $udid);
	echo json_encode($res);

}

function help()
{
	//global $udid, $met, $ver;
	echo '<b>TODO: $met</b> returns the html for application help' . "<br>"; 
}

function registerpin()
{
	global $udid, $met, $ver;
	$pin = filter_input(INPUT_GET,"pin");
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
	$pin = filter_input(INPUT_GET,"pin");
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
 	$pin = filter_input(INPUT_GET,"pin");
	$newlate = filter_input(INPUT_GET,"newlate");
	
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
	$pin = filter_input(INPUT_GET,"pin");  // identifies the Org and practitioner

	echo "<b>$met</b> uses the PIN ($pin) to decode the organisation and returns a json list of clinics for that org.<br>";

	$db = new howlate_db();
	$result = $db->getallclinics($pin);
	echo json_encode(get_object_vars($result));
}

function getPractitioner() {
	global $udid, $met, $ver;
	required(array("pin"));

        $pin = filter_input(INPUT_GET,"pin");  // identifies the Org and practitioner

	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);

	$db = new howlate_db();
	$db->validatePin($org, $id);

	$result = $db->getPractitioner($org, $id);
        header('Content-type: application/json');
	echo json_encode(get_object_vars($result));
}


function addPractitioner() {
	global $udid, $met, $ver;
	required(array("org","clin","firstname","lastname","integrkey"));

        $org = filter_input(INPUT_GET,"org");  // identifies the Org 
        $clin = filter_input(INPUT_GET,"clin");  // identifies the Org 
        $firstname = filter_input(INPUT_GET,"firstname");  // identifies the Org 
        $lastname = filter_input(INPUT_GET,"lastname");  // identifies the Org 
        $integrkey = filter_input(INPUT_GET,"integrkey");  // Key
	
	$db = new howlate_db();


	$result = $db->create_practitioner($org,$clin,$firstname,$lastname,$integrkey);
        header('Content-type: application/json');
	echo json_encode(get_object_vars($result));
}





function place() {
	global $udid, $met, $ver;
	required(array("clinic", "pin"));
	
	$clinic = filter_input(INPUT_GET,"clinic");
	$pin = filter_input(INPUT_GET,"pin");
	
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
	
	$clinic = filter_input(INPUT_GET,"clinic");
	$pin = filter_input(INPUT_GET,"pin");
	
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
	
	$pin = filter_input(INPUT_GET,"pin");
	
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

        global $met;
        
	foreach($arr as $key => $value) {
		if (!filter_input(INPUT_GET,$value)) {
			$missing[] = $value;
		}
	}
	if (!empty($missing)) {
		trigger_error('API Error: Method <b>' . $met . '</b> the following mandatory parameters were not supplied: ' . implode($missing), E_USER_ERROR);
	}
}

?>

