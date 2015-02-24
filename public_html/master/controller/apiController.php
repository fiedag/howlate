<?php
/*
 * This class should assume it is web-based and
 * be checking all get and post parameters
 * and converting them as required to pass to the
 * api model class for execution
 *
 *
 */

Class apiController Extends baseController {
    
    public function __construct($registry) {
        parent::__construct($registry);
    }
    
//    public function test2() {
//        $PractitionerName = 'Dr Anthony Alvano';
//        $Day = 'Friday';
//        $StartTime = 4000;
//        $EndTime = 5000;
//        $org = organisation::getInstance(__SUBDOMAIN);
//        $this->registry->template->result = api::updateSessions($org->OrgID, $PractitionerName, $Day, $StartTime, $EndTime);
//        $this->registry->template->show('api_index');
//    }
    
    public function upd() {
        $this->checkCredentials();

        $PractitionerName = $this->lookfor(array('Practitioner', 'Provider','PRACTITIONER','PROVIDER'));
        $AppointmentTime = $this->lookfor(array('AppointmentTime','APPOINTMENTTIME'));
        $ArrivalTime = $this->lookfor(array('ArrivalTime','ARRIVALTIME'));
        $ConsultationTime = $this->lookfor(array('ConsultationTime','CONSULTATIONTIME'));
        $NewLate = $this->lookfor(array('NewLate','NEWLATE'));  // in units of minutes

        if (!$NewLate) {
            $NewLate = round(($ConsultationTime - $AppointmentTime) / 60,0,PHP_ROUND_HALF_UP);
        }
        
        $res = api::updateLateness($this->org->OrgID, $NewLate, $PractitionerName, $ConsultationTime);
        
        $this->registry->template->result = $res;
        $this->registry->template->show('api_index');
    }

    public function notify() {
        $this->checkCredentials();
        $PractitionerName = $this->lookfor(array('Practitioner', 'Provider','PRACTITIONER','PROVIDER'));
        $MobilePhone = $this->lookfor(array('MobilePhone','CellPhone','MOBILEPHONE','CELLPHONE'));
        $ClinicID = $this->lookfor(array('ClinicID','Clinic','CLINICID'));
        $result = api::notify($this->org->OrgID, $PractitionerName, $MobilePhone, $ClinicID);
        $this->registry->template->result = $result;
        $this->registry->template->show('api_index');
    }
    
    public function notify_bulk() {
        $this->checkCredentials();
        $notif_array = $_POST["notify_bulk"];
        foreach($notif_array as $key=>$val) {
            api::notify($this->org->OrgID, $val['Provider'], $val['MobilePhone'], $val['ClinicID'], __DOMAIN);
        }
    }

    public function sess() {
        $this->checkCredentials();
        $PractitionerName = $this->lookfor(array('Practitioner', 'Provider','PRACTITIONER','PROVIDER'));
        if (!$PractitionerName) {
            throw new Exception("Practitioner or Provider not given.");
        }
        $Day = $this->lookfor(array('Day','DAY'));
        $StartTime = $this->lookfor(array('StartTime','STARTTIME'));
        $EndTime = $this->lookfor(array('EndTime','ENDTIME'));
        $org = organisation::getInstance(__SUBDOMAIN);
        $this->registry->template->result = api::updateSessions($org->OrgID, $PractitionerName, $Day, $StartTime, $EndTime);
    }

    ///
    /// General Purpose method for anything an Agent
    /// wants to report like start/stop/error events
    /// or exceptions
    public function agent() {
        $event = filter_input(INPUT_POST, "event");  // [start|stop|error|info]
        $message = filter_input(INPUT_POST,"message");
        $assemblyversion = filter_input(INPUT_POST,"assemblyversion");
        logging::trlog(TranType::AGT_INFO, $message);
    }

    public function agent_start() {
        $this->checkCredentials();
        $OrgID = $this->lookfor(array('OrgID'));
        $ClinicID = $this->lookfor(array('ClinicID','Clinic'));
        $event = 'start';
        $assemblyversion = filter_input(INPUT_POST,"assemblyversion");
        $message = 'agent_start:' . $assemblyversion;
        logging::trlog(TranType::AGT_INFO, $message, $OrgID, $ClinicID);
        echo "Agent Start logged on server.";
    }
    public function agent_stop() {
        $this->checkCredentials();
        $OrgID = $this->lookfor(array('OrgID'));
        $ClinicID = $this->lookfor(array('ClinicID','Clinic'));
        $event = 'stop';
        $assemblyversion = filter_input(INPUT_POST,"assemblyversion");
        $message = 'agent_stop:' . $assemblyversion;
        logging::trlog(TranType::AGT_INFO, $message, $OrgID, $ClinicID);
        echo "Agent Stop logged on server.";
    }
    
    // overrides the one in base controller classe
    public function handle_exception($exception) {
        $this->registry->template->result = "Exception: " . $exception->getMessage();
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

    
    /*
     * 
     * Function to look in post parameters for alternatives
     * each given in the array passed
     * thus lookfor(array('Provider','Practitioner'))
     */
    private function lookfor($arr) {
        foreach($arr as $key => $val) {
            if (array_key_exists($val,$_POST))
                    return $_POST[$val];
        }
        return null;
    }
    
    private function checkCredentials() {
        $credentials = $this->lookfor(array('credentials'));
        if(!$credentials) {
            throw new Exception("Credentials must be supplied.");
        }
        list($UserID, $PasswordHash) = explode(".", $credentials);
        if(!api::areCredentialsValid($this->org->OrgID, $UserID, $PasswordHash)) {
            throw new Exception($this->org->OrgID . " Credentials $credentials are not valid.");
        }
    }
    
    
    
    
    
}

?>
