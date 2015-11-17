<?php

/* 
 * It is assumed that the $_POST and $_GET parameters are available.
 * And that these are used using filter_input, by name
 * 
 * Normal Execution: The methods here return APIReturn::function.
 * Or throw an Exception which has inside it an 
 *  */

class AgentApi {

    protected $Organisation;
    protected $Clinic;
    protected $AssemblyVersion;

    function __construct(Organisation $Organisation, Clinic $Clinic, $AssemblyVersion) {
        $this->Organisation = $Organisation;
        $this->Clinic = $Clinic;
        $this->AssemblyVersion = $AssemblyVersion;
    }


    public function throwex() {
        APIReturn::error("Here we test throwing an exception");
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
        $ret = $Practitioner->updateLateness($NewLate, $Override = 0, $Manual = 1);
        return APIReturn::ok("Practitioner lateness updated ok", $ret);
        
    }

    public function sess() {
        $this->checkCredentials();

        $Day = $this->needsOneOf(array('Day','DAY'));
        $StartTime = $this->needsOneOf(array('StartTime','STARTTIME'));
        $EndTime = $this->needsOneOf(array('EndTime','ENDTIME'));
        $PractitionerName = $this->needsOneOf(array('Practitioner', 'Provider','PRACTITIONER','PROVIDER'));
        $Practitioner = Practitioner::getOrCreateInstance($this->Organisation->OrgID, $this->Clinic->ClinicID, $PractitionerName, 'FullName');
        $ret = $Practitioner->updateSessions($Day, $StartTime, $EndTime);
        return APIReturn::ok("Session record updated ok", $ret);
    }

    public function appttype() {
        $this->checkCredentials();

        $TypeCode = $this->needsOneOf(array('TypeCode','TYPECODE'));
        $TypeDescr = $this->needsOneOf(array('TypeDescr','TYPEDESCR','TypeDesc','TYPEDESC'));
       
        $ret = $this->Clinic->updateApptTypes($TypeCode, $TypeDescr);
        return APIReturn::ok("Appointment type updated ok", $ret);
    }

    public function apptstatus() {
        $this->checkCredentials();

        $StatusCode = $this->needsOneOf(array('StatusCode','STATUSCODE','STATUS','Status'));
        $StatusDescr = $this->needsOneOf(array('StatusDescr','STATUSDESC','StatusDesc','StatusDesc'));

        $ret = $this->Clinic->updateApptStatus($StatusCode, $StatusDescr);
        return APIReturn::ok("Appt status updated ok", $ret);
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
        Logging::trlog(TranType::AGT_APPT, "API call from agent " . __METHOD__, $this->Organisation->OrgID, $this->Clinic->ClinicID);

        $AppointmentBookExaminer = new AppointmentBookExaminer($this->Clinic, $time_now, $appt_list);
        $AppointmentBookExaminer->examineAll();

        $notifier = new Notifier($this->Clinic, $time_now, $AppointmentBookExaminer->Appointments);
        $ret = $notifier->processNotifications();
        return APIReturn::ok("Appointments processed correctly",$ret);
    }
    
    
    public function agent_start() {
        Logging::trlog(TranType::AGT_START, 'Agent process started', $this->Organisation->OrgID, $this->Clinic->ClinicID,null,null,0,$this->AssemblyVersion);
        return APIReturn::ok("Agent start has been logged");
    }
    public function agent_stop() {
        Logging::trlog(TranType::AGT_STOP, 'Agent process stopped', $this->Organisation->OrgID, $this->Clinic->ClinicID,null,null,0,$this->AssemblyVersion);
        return APIReturn::ok("Agent stop has been logged");
        
    }
    
    /*
     * Provider List is used to augment the agent's SQL statement
     * 
     * 
     */
    public function providerlist() {
        //$this->checkCredentials();
        $pract = $this->Clinic->getPlacedPractitioners();
        $list = implode(",",$pract);
        $result = "(" . $list . ")";
        Logging::trlog(TranType::AGT_INFO, 'ProviderList', $this->Organisation->OrgID, $this->Clinic->ClinicID);
        return APIReturn::ok("IN list of Providers in response", $result);
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
        throw new APIException(APIReturn::notfound('One of the following POST parameters is missing: ' . implode(',',$arr)));
    }
    
    
    protected function checkCredentials() {
        
        $credentials = $this->needsOneOf(array('credentials'));
        if(!$credentials) {
            return APIReturn::unauthorized("Credentials not supplied");
        }
        
        list($UserID, $PasswordHash) = explode(",", $credentials);
        
        if(!$this->Organisation->isValidPassword($UserID, $PasswordHash)) {
            return APIReturn::unauthorized(APIReturn::unauthorized($this->Organisation->OrgID . " Credentials $credentials are not valid."));
        }
    }
    
    
}