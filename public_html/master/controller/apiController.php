<?php

Class apiController Extends baseController {

    private $met;
    private $ver;
    
    function __construct($registry) {
        parent::__construct($registry);

        $this->met = filter_input(INPUT_GET, "met");
        $this->ver = filter_input(INPUT_GET, "ver");

        if (empty($this->met)) {
            trigger_error("Parameter met (method) must be supplied", E_USER_ERROR);
        }
        if (empty($this->ver)) {
            trigger_error("Parameter ver (get/post) must be supplied", E_USER_ERROR);
        }
        if ($this->ver != "get" and $this->ver != "post") {
            trigger_error("Parameter ver must be get or post", E_USER_ERROR);
        }
    }

    public function index() {

        switch ($this->met) {
//            case "get":
//                $this->registry->template->result = json_encode(get());  // this is the most common api call from patients' mobiles apps (not html).  
//                break;
//            case "help":        // what happens when help is requested.  Returns html not json.  A container then displays it.
//                $this->registry->template->result = json_encode(help());
//                break;
            case "reg":         // a device is registering for updates from a practitioner.  Needs no password.
                $this->registry->template->result = json_encode(howlate_api::registerpin($this->met, $this->ver));
                break;
//            case "unreg":
//                $this->registry->template->result = json_encode(unregisterpin());  // a device is deregistering for updates from a practitioner.
//                break;
            case "upd":
                $this->registry->template->result = json_encode(howlate_api::updatelateness());  // a device is updating the lateness for a single practitioner.  Needs a password.
                break;
            case "sess":
                $this->registry->template->result = json_encode(howlate_api::updatesessions());  // a device is updating the sessions from an org
                break;
            case "notif":
                $this->registry->template->result = json_encode(howlate_api::notify());
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
                $this->registry->template->result = json_encode(trigger_error('API Error: method "' . $this->met . '" is not known', E_USER_ERROR));
        }

        $this->registry->template->show('api_index');
    }   
    
}

?>
