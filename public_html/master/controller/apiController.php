<?php
/*
 * This class should assume it is web-based and
 * be checking all get and post parameters
 * and converting them as required to pass to the
 * api model class for execution
 *
 *
 */

Class ApiController Extends baseController {
    
    public function __construct($registry) {
        parent::__construct($registry);
    }
 
    public function upd() {
        $this->checkCredentials();
        $this->checkVersion();

        $Clinic = filter_input(INPUT_GET,"clinic");

        $PractitionerName = $this->lookfor(array('Practitioner', 'Provider','Provider\0' ,'PRACTITIONER', 'PROVIDER'));
        $AppointmentTime = $this->lookfor(array('AppointmentTime', 'APPOINTMENTTIME'));
        $ArrivalTime = $this->lookfor(array('ArrivalTime', 'ARRIVALTIME'));
        $ConsultationTime = $this->lookfor(array('ConsultationTime', 'CONSULTATIONTIME'));
        $ConsultationTimeUTC = $this->lookfor(array('ConsultationTimeUTC','CONSULTATIONTIMEUTC'));  // seconds since midnight
        $NewLate = $this->lookfor(array('NewLate', 'NEWLATE'));  // in units of minutes

        if (!$ConsultationTime) {
            $clin = Clinic::getInstance($this->org->OrgID, $Clinic);
            $ConsultationTime = $clin->toLocalTime($ConsultationTimeUTC);
        }

        if (!$NewLate) {
            $NewLate = round(($ConsultationTime - $AppointmentTime) / 60, 0, PHP_ROUND_HALF_UP);
        }
        
        $res = Api::updateLateness($this->org->OrgID, $Clinic, $NewLate, $PractitionerName, $ConsultationTime);

        $res .= ",ConsultationTime = $ConsultationTime";
        $this->registry->template->result = $res;
        $this->registry->template->show('api_index');
    }

    public function notify() {
        $this->checkCredentials();
        $PractitionerName = $this->lookfor(array('Practitioner', 'Provider','PRACTITIONER','PROVIDER','ProviderName','PROVIDERNAME'));
        $MobilePhone = $this->lookfor(array('MobilePhone','CellPhone','MOBILEPHONE','CELLPHONE'));
        $ClinicID = $this->lookfor(array('ClinicID','Clinic','CLINICID','clinic'));
        $result = Api::notify($this->org->OrgID, $ClinicID, $PractitionerName, $MobilePhone, $ClinicID);
        $this->registry->template->result = $result;
        $this->registry->template->show('api_index');
    }
    
    public function notify_bulk() {
        $this->checkCredentials();
        $this->checkVersion();
        $ClinicID = $this->lookfor(array('ClinicID','Clinic','CLINICID','clinic'));
        $Provider = $this->lookfor(array('Provider','PROVIDER'));
        $AppointmentTime = $this->lookfor(array('AppointmentTime','APPOINTMENTTIME'));
        $ConsultationTime = $this->lookfor(array('ConsultationTime','CONSULTATIONTIME'));
        $ConsultationTimeUTC = $this->lookfor(array('ConsultationTimeUTC','CONSULTATIONTIMEUTC'));
        if(isset($ConsultationTimeUTC) && !isset($ConsultationTime)) {
            $ConsultationTime = Clinic::getInstance($this->org->OrgID,$ClinicID)->toLocalTime($ConsultationTimeUTC);
        }
        $AppointmentLength = $this->lookfor(array('AppointmentLength','APPOINTMENTLENGTH'));
        $notify_array = $_POST["notify_bulk"];
        
        Api::notifybulk($this->org->OrgID, $ClinicID, $Provider, $AppointmentTime, $ConsultationTime, $AppointmentLength, $notify_array);
    }

    public function sess() {
        $this->checkCredentials();
        $this->checkVersion();
        
        $Clinic = filter_input(INPUT_GET,"clinic");

        $PractitionerName = $this->lookfor(array('Practitioner', 'Provider','PRACTITIONER','PROVIDER'));
        if (!$PractitionerName) {
            throw new Exception("Practitioner or Provider not given.");
        }
        $Day = $this->lookfor(array('Day','DAY'));
        $StartTime = $this->lookfor(array('StartTime','STARTTIME'));
        $EndTime = $this->lookfor(array('EndTime','ENDTIME'));
        $org = Organisation::getInstance(__SUBDOMAIN);
        $this->registry->template->result = Api::updateSessions($org->OrgID, $Clinic, $PractitionerName, $Day, $StartTime, $EndTime);
    }

    public function appttype() {
        $this->checkCredentials();
        $this->checkVersion();

        $org = Organisation::getInstance(__SUBDOMAIN);

        $Clinic = filter_input(INPUT_GET,"clinic");
        $TypeCode = $this->lookfor(array('TypeCode','TYPECODE'));
        $TypeDescr = $this->lookfor(array('TypeDescr','TYPEDESCR','TypeDesc','TYPEDESC'));

        $this->registry->template->result = Api::updateApptTypes($org->OrgID, $Clinic, $TypeCode, $TypeDescr);
        
    }
    public function apptstatus() {
        $this->checkCredentials();
        $this->checkVersion();

        $org = Organisation::getInstance(__SUBDOMAIN);

        $Clinic = filter_input(INPUT_GET,"clinic");
        $StatusCode = $this->lookfor(array('StatusCode','STATUSCODE','STATUS','Status'));
        $StatusDescr = $this->lookfor(array('StatusDescr','STATUSDESC','StatusDesc','StatusDesc'));

        $this->registry->template->result = Api::updateApptStatus($org->OrgID, $Clinic, $StatusCode, $StatusDescr);
        
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
     * 
     * Reporting an Agent Start 
     * 
     * 
     */
    public function agent_start() {
        $this->checkCredentials();
        $this->checkVersion();
        
        $OrgID = $this->lookfor(array('OrgID'));
        $ClinicID = $this->lookfor(array('ClinicID','Clinic'));
        $event = 'start';
        $assemblyversion = filter_input(INPUT_POST,"assemblyversion");
        $message = 'agent_start:' . $assemblyversion;
        Logging::trlog(TranType::AGT_INFO, $message, $OrgID, $ClinicID);
        echo "Agent Start logged on server.";
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
        $this->registry->template->result = $result;
        $this->registry->template->show('api_index');
        
    }
    
    
    public function agent_stop() {
        $this->checkCredentials();
        $OrgID = $this->lookfor(array('OrgID'));
        $ClinicID = $this->lookfor(array('ClinicID','Clinic'));
        $event = 'stop';
        $assemblyversion = filter_input(INPUT_POST,"assemblyversion");
        $message = 'agent_stop:' . $assemblyversion;
        Logging::trlog(TranType::AGT_INFO, $message, $OrgID, $ClinicID);
        echo "Agent Stop logged on server.";
    }
    
    // overrides the one in base controller classe
    public function handle_exception($exception) {
        $this->registry->template->exception = $exception;
        $this->registry->template->result = "Exception: " . $exception->getMessage();
        
        Logging::trlog(TranType::AGT_ERROR, $exception->getMessage(), $this->org->OrgID);

        $this->registry->template->show('api_index');
    }

    // since this is the fallback method for any action which 
    // does not have a method, report this as an error
    public function index() {
        $met = filter_input(INPUT_GET,'met');
        if(!$met) {
            throw new Exception("GET parameter met must be given");
        }
        $this->$met();
        
        //$rt = filter_input(INPUT_GET,'rt');
        //$action = explode('/',$rt)[1];
        //throw new Exception("Method $action is not known.");
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
     
    public function get_exe_deleteme() {
        $org = Organisation::getInstance(__SUBDOMAIN);
        $ClinicID = filter_input(INPUT_GET,"clin");
        $Version = filter_input(INPUT_GET,"version");
       
        if (!$ClinicID) {
            throw new Exception("clin GET parameter must be supplied");
        }
        Api::get_exe2($org->OrgID, $ClinicID, $Version );
    }    
    
    public function get_exe() {
        $ClinicID = filter_input(INPUT_GET,"clin");
        if (!$ClinicID) {
            throw new Exception("clin GET parameter must be supplied");
        }

        Api::get_exe($this->org->OrgID, $ClinicID);
        
    }
    
    public function get_agent_displayname() {
        $org = Organisation::getInstance(__SUBDOMAIN);
        $ClinicID = filter_input(INPUT_GET,"clin");
        if (!$ClinicID) {
            throw new Exception("clin GET parameter must be supplied");
        }

        $clin = Clinic::getInstance($org->OrgID, $ClinicID);
        // this will initiate a download of HowLateAgent.exe.config
        $result = $clin->getClinicIntegration();

        $this->registry->template->result = "HowLateAgent for " . $result->Name;
        $this->registry->template->show('api_index'); 
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
        
        $this->registry->template->show('agent_config'); 
    }

    public function clin() {
        $OrgID = filter_input(INPUT_GET,'org');
        $ClinicID = filter_input(INPUT_GET,'clin');
        
        $this->registry->template->result = Clinic::getInstance($OrgID, $ClinicID);
        $this->registry->template->show('api_index');
    }

    
    public function providerlist() {
        $this->checkCredentials();
        $this->checkVersion();
        
        $ClinicID = $this->lookfor(array('ClinicID','Clinic'));
        
        $result = Api::providerList($this->org->OrgID, $ClinicID);
        $this->registry->template->result = $result;
        $this->registry->template->show('api_index');
        
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
    
    private function checkCredentials() {
        $credentials = $this->lookfor(array('credentials'));
        if(!$credentials) {
            throw new Exception("Credentials must be supplied.");
        }
        list($UserID, $PasswordHash) = explode(".", $credentials);
        if(!Api::areCredentialsValid($this->org->OrgID, $UserID, $PasswordHash)) {
            throw new Exception($this->org->OrgID . " Credentials $credentials are not valid.");
        }
    }
    
    private function checkVersion() {
        $AssemblyVersion = $this->lookfor(array('assemblyversion','assembly'));
        $ClinicID = $this->lookfor(array('clinic','ClinicID'));
        if(!$AssemblyVersion) {
            throw new Exception("Must supply an assemblyversion POST parameter");
        }
        if(!$ClinicID) {
            throw new Exception("Must supply a Clinic ID POST parameter");
        }
        
        $AgentVersionTarget = agent::getInstance($this->org->OrgID, $ClinicID)->AgentVersionTarget;
        
        if ($AssemblyVersion != $AgentVersionTarget) {
            $msg = "Upgrade required (from " . $AssemblyVersion . " to " . $AgentVersionTarget . ")" ;
            $arr = array('Message' => $msg,'FromVersion' => $AssemblyVersion, 'ToVersion' => $AgentVersionTarget);
            // WARNING: Do not remove FromVersion or ToVersion from this $arr without checking HowLateAgent.VersionException.cs

            throw new APIException($arr);
            
        }
    }    
    
    
}

?>
