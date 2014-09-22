<?php

Class apiController Extends baseController {

    private $met;
    private $ver;
    
    function __construct($registry) {
        parent::__construct($registry);

        $this->met = filter_input(INPUT_GET, "met");
        $this->ver = filter_input(INPUT_GET, "ver");

        if (empty($this->met)) {
            throw new Exception("Parameter met (method) must be supplied");
        }
        if (empty($this->ver)) {
            throw new Exception("Parameter ver (get/post) must be supplied");
        }
        if ($this->ver != "get" and $this->ver != "post") {
            throw new Exception("Parameter ver must be get or post");
        }
    }

    
    public function unh_exception($exception) {
        $db = new howlate_db();
        $db->write_error(0, 1, $exception->getMessage(), $exception->getFile(), $exception->getLine());
        
        $this->registry->template->result = json_encode("Exception: " . $exception->getMessage());
        $this->registry->template->show('api_index');
        
        
    }
    
    public function index() {

        $result="Unknown API Error";
        switch ($this->met) {
//            case "get":
//                $this->registry->template->result = json_encode(get());  // this is the most common api call from patients' mobiles apps (not html).  
//                break;
//            case "help":        // what happens when help is requested.  Returns html not json.  A container then displays it.
//                $this->registry->template->result = json_encode(help());
//                break;
            case "reg":         // a device is registering for updates from a practitioner.  Needs no password.
                $result = howlate_api::registerpin($this->met, $this->ver);
                break;
//            case "unreg":
//                $this->registry->template->result = json_encode(unregisterpin());  // a device is deregistering for updates from a practitioner.
//                break;
            case "upd":
                $result = howlate_api::updatelateness();  // a device is updating the lateness for a single practitioner.  Needs a password.
                break;
            case "sess":
                $result = howlate_api::updatesessions();  // a device is updating the sessions from an org
                break;
            case "notif":
                $result = howlate_api::notify();
                break;
//            case "getclinics":
//                $this->registry->template->result = json_encode(getclinics());  // returns a list of clinics for this organisation
//                break;
//            case "getorgs":
//                $this->registry->template->result = json_encode(getorgs());  // returns a list of countries which have already signed up
//                break;
//            case "getcountries":
//                $this->registry->template->result = json_encode(getcountries());  // returns a list of countries which have already signed up
//                break;
//            case "place":  // places a practitioner in a clinic
//                $this->registry->template->result = json_encode(place());
//                break;
//            case "displace": // displaces a practitioner from a clinic
//                $this->registry->template->result = json_encode(displace());
//                break;
//            case "invite": // sends an SMS invitation which registers a device for a PIN
//                $this->registry->template->result = json_encode(sendInvitation());
//                break;
//            case "getpract": // gets practitioner information and returns json
//                $this->registry->template->result = json_encode(getPractitioner());
//                break;
//            case "addpract": // gets practitioner information and returns json
//                $this->registry->template->result = json_encode(addPractitioner());
//                break;

            default:
                throw new Exception('API Error: method "' . $this->met . '" is not known');
        }
        
        $this->registry->template->result = json_encode($result);

        $this->registry->template->show('api_index');
    }   
    
}

?>
