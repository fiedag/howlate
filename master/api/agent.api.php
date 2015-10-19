<?php

/* 
 * It is assumed that the $_POST and $_GET parameters are available.
 * And that these are used using filter_input, by name
 * 
 * Normal Execution: The methods here return strings or arrays.
 * The APIController must wrap these into JSON or whatever is next
 * Exceptions: The methods here are expected to throw Exception() only
 * The API Controller will wrap these into a valid APIException()
 */

class AgentApi {

    protected $Organisation;
    protected $Clinic;

    function __construct(Organisation $Organisation, Clinic $Clinic) {
        $this->Organisation = $Organisation;
        $this->Clinic = $Clinic;
    }


    public function throwex() {
        throw new Exception('THis is a normal exception');
    }
    
    public function divzero() {
        $zero = 0;
        return 567 / $zero;
        //throw new Exception('Exception test');
    }
    
    /*
     * Old versions of the agent updated the practitioner's
     * lateness manually.  Now it is done predictively at the
     * Appointment book level, or via the UI.
     */
    public function upd() {
        $this->checkCredentials();

        $PractitionerName = $this->needsOneOf(array('Practitioner', 'Provider','Provider\0' ,'PRACTITIONER', 'PROVIDER'));
        //$ConsultationTime = $this->needsOneOf(array('ConsultationTime', 'CONSULTATIONTIME'));
        $NewLate = $this->needsOneOf(array('NewLate', 'NEWLATE'));  // in units of minutes

        $Practitioner = Practitioner::getOrCreateInstance($this->Organisation->OrgID, $this->Clinic->ClinicID, $PractitionerName, 'FullName');
        return $Practitioner->updateLateness($NewLate, $Sticky = 0, $Manual = 1);
        
    }

    public function sess() {
        $this->checkCredentials();

        $Day = $this->needsOneOf(array('Day','DAY'));
        $StartTime = $this->needsOneOf(array('StartTime','STARTTIME'));
        $EndTime = $this->needsOneOf(array('EndTime','ENDTIME'));
        $PractitionerName = $this->needsOneOf(array('Practitioner', 'Provider','PRACTITIONER','PROVIDER'));
        $Practitioner = Practitioner::getOrCreateInstance($this->Organisation->OrgID, $this->Clinic->ClinicID, $PractitionerName, 'FullName');
        return $Practitioner->updateSessions($Day, $StartTime, $EndTime);
        
        
    }

    public function appttype() {
        $this->checkCredentials();

        $TypeCode = $this->needsOneOf(array('TypeCode','TYPECODE'));
        $TypeDescr = $this->needsOneOf(array('TypeDescr','TYPEDESCR','TypeDesc','TYPEDESC'));
       
        return $this->Clinic->updateApptTypes($TypeCode, $TypeDescr);
    }

    public function apptstatus() {
        $this->checkCredentials();

        $StatusCode = $this->needsOneOf(array('StatusCode','STATUSCODE','STATUS','Status'));
        $StatusDescr = $this->needsOneOf(array('StatusDescr','STATUSDESC','StatusDesc','StatusDesc'));

        return $this->Clinic->updateApptStatus($StatusCode, $StatusDescr);
    }
    
    
    /*
     * New predictive method of updating lateness
     * 
     * 
     * 
     */
    public function appt() {
        $this->checkCredentials();
        $time_now = $this->needsOneOf(array('TimeNow','time_now','timenow'));
        $appt_list = $_POST["appt"];  // entire appointment list, huge array
        
        $AppointmentBookExaminer = new AppointmentBookExaminer($this->Clinic, $time_now, $appt_list);
        
        $AppointmentBookExaminer->examineAll();

        $notifier = new Notifier($this->Clinic, $time_now, $AppointmentBookExaminer->Appointments);
        return $notifier->processNotifications();
    }
    
    public function HowLateAgentUpdaterExe() {
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
        exit;  // necessary to prevent a standard JSON return code being presented to the caller
    } 
    
    public function HowLateAgentExe() {
        $AgentExe = Agent::getInstance($this->Organisation->OrgID, $this->Clinic->ClinicID);
        $AgentExe->get_exe();
        exit;  // necessary to prevent a standard JSON return code being presented to the caller
    }
    
//    public function get_agent_displayname() {
//        $ClinicID = filter_input(INPUT_GET,"clin");
//        if (!$ClinicID) {
//            throw new Exception("clin GET parameter must be supplied");
//        }
//
//        $clin = Clinic::getInstance($this->Organisation->OrgID, $ClinicID);
//        
//        $result = $clin->getClinicIntegration();
//
//        $result = "HowLateAgent for " . $result->Name;
//        $this->ok($result); 
//    }

    
    
    public function HowLateAgentExeConfig() {
        $record = Agent::getInstance($this->Organisation->OrgID, $this->Clinic->ClinicID);
        $URL = "https://" . __FQDN . "/api";
        $Credentials = $record->HLUserID . "." . $record->XPassword;
        include(__SITE_PATH . '/views/agent_config.php');
        exit;  // necessary to prevent a standard JSON return code being presented to the caller
    }

    
    
//    public function clin() {
//        $OrgID = filter_input(INPUT_GET,'org');
//        $ClinicID = filter_input(INPUT_GET,'clin');
//        
//        $this->registry->template->result = Clinic::getInstance($OrgID, $ClinicID);
//        $this->registry->template->show('api_index');
//    }

    
    /*
     * Provider List is used to augment the agent's SQL statement
     * 
     * 
     */
    public function providerlist() {
        //$this->checkCredentials();

        $ClinicID = $this->needsOneOf(array('ClinicID','Clinic'));
        $ClinicID = filter_input(INPUT_GET, 'clin');
        
        $result = Api::getInstance($this->Organisation->OrgID, $ClinicID)->getProviderList();
        $this->ok($result);
        
    }
    
    /*
     * 
     * Function to look in post parameters for alternatives
     * each given in the array passed
     * thus lookfor(array('Provider','Practitioner'))
     */
    protected function lookfor_DeleteMe($arr) {
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
        throw new Exception('One of the following POST parameters is required: ' . implode(',',$arr));
    }
    
    
    protected function checkCredentials() {
        $credentials = $this->needsOneOf(array('credentials'));
        if(!$credentials) {
            throw new Exception("Credentials must be supplied.");
        }
        list($UserID, $PasswordHash) = explode(".", $credentials);
        
        if(!$this->Organisation->isValidPassword($UserID, $PasswordHash)) {
            throw new Exception($this->Organisation->OrgID . " Credentials $credentials are not valid.");
        }
    }
    
    
}