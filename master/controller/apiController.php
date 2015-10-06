<?php
/*
 * This class should assume it is web-based and
 * be checking all get and post parameters
 * and converting them as required to pass to the
 * api model class for execution
 *
 * Every method should return JSON
 * of the form 
 * 
 * 
{
    "code": 202,
    "status": "OK",
    "message": "",
    "response": {
        "id": 3 // and this response should be the array returned
                // by the corresponding API class method
    }
}

 * 
 */

Class ApiController Extends baseController {
    

    /*
     * Used at the end of each of the API functions.
     * 
     */
    private function ok($response = false) {
        $this->registry->template->response = $response;
        $this->registry->template->show('api_index');
        exit;
    }

    
    public function upd() {
        $this->checkCredentials();
        $this->checkVersion();

        $Clinic = $this->needsOneOf(array('clinic','ClinicID'));
        $PractitionerName = $this->needsOneOf(array('Practitioner', 'Provider','Provider\0' ,'PRACTITIONER', 'PROVIDER'));
        $ConsultationTime = $this->needsOneOf(array('ConsultationTime', 'CONSULTATIONTIME'));
        $NewLate = $this->needsOneOf(array('NewLate', 'NEWLATE'));  // in units of minutes
        
        $res = Api::updateLateness($this->org->OrgID, $Clinic, $NewLate, $PractitionerName, $ConsultationTime);

        $res .= ",ConsultationTime = $ConsultationTime";
        
        $this->ok($res);
    }

    
    public function notify() {
        $this->checkCredentials();
        
        $PractitionerName = $this->needsOneOf(array('Practitioner', 'Provider','PRACTITIONER','PROVIDER','ProviderName','PROVIDERNAME'));
        $MobilePhone = $this->needsOneOf(array('MobilePhone','CellPhone','MOBILEPHONE','CELLPHONE'));
        $ClinicID = $this->needsOneOf(array('ClinicID','Clinic','CLINICID','clinic'));
        $result = Api::notify($this->org->OrgID, $ClinicID, $PractitionerName, $MobilePhone, $ClinicID);
        $this->ok($result);
    }
    
    public function notify_bulk() {
        $this->checkCredentials();
        $this->checkVersion();
        $ClinicID = $this->needsOneOf(array('ClinicID','Clinic','CLINICID','clinic'));
        $Provider = $this->needsOneOf(array('Provider','PROVIDER'));
        $AppointmentTime = $this->needsOneOf(array('AppointmentTime','APPOINTMENTTIME'));
        $ConsultationTime = $this->needsOneOf(array('ConsultationTime','CONSULTATIONTIME'));
        
        $AppointmentLength = $this->needsOneOf(array('AppointmentLength','APPOINTMENTLENGTH'));
        $notify_array = $_POST["notify_bulk"];
        
        $result = Api::notifybulk($this->org->OrgID, $ClinicID, $Provider, $AppointmentTime, $ConsultationTime, $AppointmentLength, $notify_array);
        $this->ok($result);
    }

    public function sess() {
        $this->checkCredentials();
        $this->checkVersion();
        
        $Clinic = $this->needsOneOf(array('clinic', 'ClinicID','CLINIC','CLINICID'));
        $PractitionerName = $this->needsOneOf(array('Practitioner', 'Provider','PRACTITIONER','PROVIDER'));

        $Day = $this->needsOneOf(array('Day','DAY'));
        $StartTime = $this->needsOneOf(array('StartTime','STARTTIME'));
        $EndTime = $this->needsOneOf(array('EndTime','ENDTIME'));
        
        $result = Api::updateSessions($this->org->OrgID, $Clinic, $PractitionerName, $Day, $StartTime, $EndTime);
        $this->ok($result);
    }

    public function appttype() {
        $this->checkCredentials();
        $this->checkVersion();

        $org = Organisation::getInstance(__SUBDOMAIN);

        $Clinic = $this->needsOneOf(array('ClinicID','clinic','CLINIC'));
        $TypeCode = $this->needsOneOf(array('TypeCode','TYPECODE'));
        $TypeDescr = $this->lookfor(array('TypeDescr','TYPEDESCR','TypeDesc','TYPEDESC'));

        $result = Api::updateApptTypes($org->OrgID, $Clinic, $TypeCode, $TypeDescr);
        $this->ok($result);
    }

    public function apptstatus() {
        $this->checkCredentials();
        $this->checkVersion();

        $Clinic = filter_input(INPUT_GET,"clinic");
        $StatusCode = $this->lookfor(array('StatusCode','STATUSCODE','STATUS','Status'));
        $StatusDescr = $this->lookfor(array('StatusDescr','STATUSDESC','StatusDesc','StatusDesc'));

        $result = Api::updateApptStatus($this->org->OrgID, $Clinic, $StatusCode, $StatusDescr);
        $this->ok($result);
    }
    
    
    /*
     * General Purpose method for anything an Agent
     * wants to report like start/stop/error events
     * or exceptions
     * 
     */
    public function agent() {
        $event = filter_input(INPUT_POST, "event");  // [start|stop|error|info]
        $message = filter_input(INPUT_POST,"message");
        $assemblyversion = filter_input(INPUT_POST,"assemblyversion");
        Logging::trlog(TranType::AGT_INFO, $message);
    }

    
    /*
     * New predictive method of updating lateness
     * 
     * 
     * 
     */
    public function appt() {
        $ClinicID = $this->lookfor(array('clinic','ClinicID','Clinic'));
        $TimeNow = $this->lookfor(array('TimeNow','time_now','timenow'));
        $this->checkCredentials();
        $this->checkVersion();
        
        Logging::trlog(TranType::LATE_UPD, 'Processing Appointments', $this->org->OrgID, $ClinicID);

        $appt = $_POST["appt"];  // entire appointment list
        
        $result = Api::processAppointments($this->org->OrgID, $ClinicID, $appt, $TimeNow);
        $this->ok($result);
    }

    
    // overrides the one in base controller classe
    public function handle_exception($exception) {
        
        Logging::trlog(TranType::AGT_ERROR, $exception->getMessage(), $this->org->OrgID);

        throw new APIException($message = $exception->getMessage(),$code=400,$status='Unhandled exception');
        
    }

    // since this is the fallback method for any action which 
    // does not have a method, report this as an error
    public function index() {
        $met = filter_input(INPUT_GET,'met');
        if(!$met) {
            throw new Exception("GET parameter met must be given");
        }
        $this->$met();
    }

    
    public function get_updater() {
        $file = "downloads/x64/HowLateAgentUpdater.exe";

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            trigger_error("File $file does not exist.", E_USER_ERROR);
        }
    } 
    
    public function get_exe() {
        $ClinicID = filter_input(INPUT_GET,"clin");
        if (!$ClinicID) {
            throw new Exception("clin GET parameter must be supplied");
        }

        Api::get_exe($this->org->OrgID, $ClinicID);
        
    }
    
    public function get_agent_displayname() {
        $ClinicID = filter_input(INPUT_GET,"clin");
        if (!$ClinicID) {
            throw new Exception("clin GET parameter must be supplied");
        }

        $clin = Clinic::getInstance($this->org->OrgID, $ClinicID);
        
        $result = $clin->getClinicIntegration();

        $result = "HowLateAgent for " . $result->Name;
        $this->ok($result); 
    }

    
    
    public function get_exe_config() {
        $org = Organisation::getInstance(__SUBDOMAIN);
        $ClinicID = filter_input(INPUT_GET,"clin");
        if (!$ClinicID) {
            throw new Exception("clin GET parameter must be supplied");
        }

        $result = agent::getInstance($this->org->OrgID, $ClinicID);
       
        $this->registry->template->record = $result;
        $this->registry->template->URL = "https://" . __FQDN . "/api";
        $this->registry->template->Credentials = $result->HLUserID . "." . $result->XPassword;
        // THis constructs the HowLateAgent.exe.config file used by the exe
        $this->registry->template->show('agent_config'); 
    }

    public function clin() {
        $OrgID = filter_input(INPUT_GET,'org');
        $ClinicID = filter_input(INPUT_GET,'clin');
        
        $this->registry->template->result = Clinic::getInstance($OrgID, $ClinicID);
        $this->registry->template->show('api_index');
    }

    
    /*
     * Provider List is used to augment the agent's SQL statement
     * 
     * 
     */
    public function providerlist() {
        //$this->checkCredentials();
        //$this->checkVersion();
        
        $ClinicID = $this->lookfor(array('ClinicID','Clinic'));
        $ClinicID = filter_input(INPUT_GET, 'clin');
        
        $result = Api::getInstance($this->org->OrgID, $ClinicID)->getProviderList();
        $this->ok($result);
        
    }
    
    
    /*
     * 
     * Function to look in post parameters for alternatives
     * each given in the array passed
     * thus lookfor(array('Provider','Practitioner'))
     */
    protected function lookfor($arr) {
        foreach($arr as $key => $val) {
            if (array_key_exists($val,$_POST))
                    return trim($_POST[$val]);
        }
        return null;
    }
    
    
    /*
     * 
     * Function to look in post parameters for alternatives
     * each given in the array passed
     * thus lookfor(array('Provider','Practitioner'))
     */
    protected function needsOneOf($arr) {
        foreach($arr as $key => $val) {
            if (array_key_exists($val,$_POST))
                    return trim($_POST[$val]);
        }
        throw new APIException('One of the following POST parameters is required',$code=400,$status="Bad request",$arr );
    }
    
    
    private function checkCredentials() {
        $credentials = $this->lookfor(array('credentials'));
        if(!$credentials) {
            throw new APIException("Credentials must be supplied.");
        }
        list($UserID, $PasswordHash) = explode(".", $credentials);
        if(!Api::areCredentialsValid($this->org->OrgID, $UserID, $PasswordHash)) {
            throw new APIException($this->org->OrgID . " Credentials $credentials are not valid.");
        }
    }
    
    private function checkVersion() {
        $AssemblyVersion = $this->needsOneOf(array('assemblyversion','assembly'));
        $ClinicID = $this->needsOneOf(array('clinic','ClinicID'));

        $AgentVersionTarget = agent::getInstance($this->org->OrgID, $ClinicID)->AgentVersionTarget;
        
        if ($AssemblyVersion != $AgentVersionTarget) {
            $msg = "Upgrade required (from " . $AssemblyVersion . " to " . $AgentVersionTarget . ")" ;
            $arr = array('Message' => $msg,'FromVersion' => $AssemblyVersion, 'ToVersion' => $AgentVersionTarget);
            // WARNING: Do not remove FromVersion or ToVersion from this $arr without checking HowLateAgent.VersionException.cs

            throw new APIException($msg, $code=400,$status='Upgrade required', $arr);
            
        }
        $this->ok();
        
    }    
    
    
}

?>
