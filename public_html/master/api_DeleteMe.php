<?php


$site_path = realpath(dirname(__FILE__));
define ('__SITE_PATH', $site_path);
 
include_once('includes/init.php');

$debug = filter_input(INPUT_GET,"debug");
define ('__DEBUG', $debug);

// these two are required no matter what

$met = filter_input(INPUT_GET,"met");
$ver = filter_input(INPUT_GET,"ver");


//if (empty($udid)) { trigger_error("Parameter udid (unique device id) must be supplied", E_USER_ERROR);}
if (empty($met)) { trigger_error("Parameter met (method) must be supplied", E_USER_ERROR);}
if (empty($ver)) { trigger_error("Parameter ver (get/post) must be supplied", E_USER_ERROR);}
if ($ver != "get" and $ver != "post") { trigger_error("Parameter ver must be get or post", E_USER_ERROR);}


header('Content-type: application/json');
       
switch ($met)
{
        case "get":
		get();  // this is the most common api call from patients' mobiles apps (not html).  
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
	case "getorgs":
		getorgs();  // returns a list of countries which have already signed up
		break;
	case "getcountries":
		getcountries();  // returns a list of countries which have already signed up
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

function get()
{
	global $met, $ver;
        required(array("udid"));
        $udid = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"udid");

        $db = new howlate_db();
        
	$res = $db->getLatenessesByUDID($udid);
	
	$db->trlog(TranType::LATE_GET, 'Lateness got for device ' . $udid);
	echo json_encode($res);

}

function help()
{
	//TODO: Finish this.
	
}

function registerpin()
{
	global $met, $ver;
	$pin = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"pin");
        $udid = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"udid");        
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
	global $met, $ver;
        required(array("pin","udid"));
	$pin = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"pin");
        $udid = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"udid");

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

//// Updates the lateness for a specific practitioner
//// Called from the HowLate Agent
//function updatelateness()
//{
//	global $met, $ver;
//	required(array("credentials","Provider","AppointmentTime","ConsultationTime"));
//        // also relevant is the subdomain of the request
//        
// 	$org = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"org");
//        $credentials = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"credentials");
//        list($userid,$passwordhash) = explode(".",$credentials);
// 	$practitioner = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"practitioner");
//        $newlate = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"newlate");
//        $db = new howlate_db();
//        
//        if($db->isValidPassword($org, $userid, $passwordhash)) {
//            try {
//                $id = $db->getPractitionerID($org,$practitioner);
//                $db->updatelateness($org,$id,$newlate);
//                $db->trlog(TranType::LATE_UPD, 'Practitioner ' . $practitioner . ' is now ' . $newlate . ' minutes late', $org, null, $id, null);
//            }
//            catch(Exception $ex) {
//                $db->trlog(TranType::LATE_UPD, 'Practitioner ' . $practitioner . ' lateness update failed, exception =' . $ex, $org, null, $null, $null);
//            }
//        }
//        return json_encode("Practitioner $practitioner lateness updated to $newlate");
//}

function getclinics()
{
	global $met, $ver;
	required(array("pin"));
	$pin = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"pin");  // identifies the Org and practitioner

	$db = new howlate_db();
	$result = $db->getallclinics($pin);
	echo json_encode(get_object_vars($result));
}

function getcountries()
{
	global $met, $ver;

	$db = new howlate_db();
	$result = $db->getallcountries();
	echo '{"Countries":' . json_encode($result) . '}';
}

function getorgs() {
	global $met, $ver;
        required(array("country"));

        $country = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"country");
        
	$db = new howlate_db();
	$result = $db->getallorgs($country);
	echo '{"Orgs":' . json_encode($result) . '}';
}

function getPractitioner() {
	global $met, $ver;
	required(array("pin","udid"));

        $udid = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"udid");  // Unique Device ID
        $pin = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"pin");  // identifies the Org and practitioner

	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);

	$db = new howlate_db();
	$db->validatePin($org, $id);

	$result = $db->getPractitioner($org, $id);

        echo json_encode(get_object_vars($result));
}

//
//function addPractitioner() {
//	global $met, $ver;
//	required(array("org","clin","firstname","lastname","integrkey"));
//
//        $org = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"org");  // identifies the Org 
//        $clin = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"clin");  // identifies the Org 
//        $firstname = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"firstname");  // identifies the Org 
//        $lastname = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"lastname");  // identifies the Org 
//        $integrkey = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"integrkey");  // Key
//	
//	$db = new howlate_db();
//
//        $result = $db->create_practitioner($org,$clin,$firstname,$lastname,$integrkey);
//        header('Content-type: application/json');
//	echo json_encode(get_object_vars($result));
//}


function place() {
	global $udid, $met, $ver;
	required(array("clinic", "pin"));
	
	$clinic = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"clinic");
	$pin = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"pin");
	
	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);
	$db = new howlate_db();
	$db->validatePin($org, $id);
	$db->validateClinic($org, $clinic);
	$db->place($org, $id, $clinic);
	$db->trlog(TranType::PRAC_PLACE, 'Practitioner ' . $id . ' now placed at clinic ' . $clinic, $org, null, $id, null);

	echo "Successfully placed practitioner $id in clinic $clinic in org $org<br>";
	
}

function displace() {
	global $met, $ver;
	required(array("clinic", "pin"));
	
	$clinic = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"clinic");
	$pin = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"pin");
	
	howlate_util::validatePin($pin);
	
	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);
	$db = new howlate_db();
	$db->validatePin($org, $id);
	$db->validateClinic($org, $clinic);
	$db->displace($org, $id, $clinic);
	echo "Successfully displaced practitioner $id from clinic $clinic in org $org<br>";
	$db->trlog(TranType::PRAC_DISP, 'Practitioner ' . $id . ' now not placed at clinic ' . $clinic, $org, null, $id, null);

}

function sendInvitation() {
	global $met, $ver;
	required(array("udid","pin"));
	
	$udid = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"udid");
	$pin = filter_input(($ver=="get")?INPUT_GET:INPUT_POST,"pin");
	
	registerpin();  // uses the mobile number as the UDID.  registers it.

	$org = howlate_util::orgFromPin($pin);
	$id = howlate_util::idFromPin($pin);

	$db = new howlate_db();
	$prac = $db->getPractitioner($org, $id);
	
	$message = 'To receive lateness updates for ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
        $message .= ', click : ';
        $message .= "http://$prac->FQDN/late/view&udid=$udid";

        howlate_sms::httpSend($org, $udid, $message);

	
}

function required($arr) {
        global $met;
        global $ver;
        
	foreach($arr as $key => $value) {
                $val = filter_input(($ver == "get")?INPUT_GET:INPUT_POST,$value);
		if  ($val == null)
			$missing[] = $value;
		
	}
	if (!empty($missing)) {
		echo json_encode(trigger_error('API Error: Method <b>' . $met . '</b> the following mandatory parameters were not supplied: ' . implode($missing), E_USER_ERROR));
	}
}

?>

