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
    
    public function upd() {
        $this->checkCredentials();

        $PractitionerName = $this->lookfor(array('Practitioner', 'Provider'));
        $AppointmentTime = $this->lookfor(array('AppointmentTime'));
        $ArrivalTime = $this->lookfor(array('ArrivalTime'));
        $ConsultationTime = $this->lookfor(array('ConsultationTime'));
        $NewLate = $this->lookfor(array('NewLate'));
        if (!$NewLate) {
            $NewLate = round(($ConsultationTime - $AppointmentTime) / 60,0,PHP_ROUND_HALF_UP);
        }
        $this->registry->template->result = api::updateLateness($this->org->OrgID, $NewLate, $PractitionerName);
        $this->registry->template->show('api_index');
    }

    public function notify() {
        //$this->checkCredentials();
        $PractitionerName = $this->lookfor(array('Practitioner', 'Provider'));
        $MobilePhone = $this->lookfor(array('MobilePhone','CellPhone'));
        
        $result = api::notify($this->org->OrgID, $PractitionerName, $MobilePhone);
        
        $this->registry->template->result = $result;
        $this->registry->template->show('api_index');
    }
    
    public function notify_bulk() {
        $this->checkCredentials();

        $notif_array = $_POST["notify_bulk"];
        foreach($notif_array as $key=>$val) {
            api::notify($this->org->OrgID, $val['Provider'], $val['MobilePhone'], __DOMAIN);
        }
    }

    public function sess() {
        $this->checkCredentials();
        $PractitionerName = $this->lookfor(array('Practitioner', 'Provider'));
        
        $Day = $this->lookfor(array('Day'));
        $StartTime = $this->lookfor(array('StartTime'));
        $EndTime = $this->lookfor(array('EndTime'));
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

    // overrides the one in base controller classe
    public function handle_exception($exception) {
        $this->registry->template->result = "Exception: " . $exception->getMessage();
        $this->registry->template->show('api_index');
    }

    // since this is the fallback method for any action which 
    // does not have a method, report this as an error
    public function index() {
        $met = filter_input(INPUT_GET,'met');
        
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
