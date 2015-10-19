<?php

Class ApiController Extends baseController {

    protected $api;

    public function index() {
        throw new APIException("This is not implemented");
    }

    public function agent() {
        $rt = filter_input(INPUT_GET, 'rt');
        if (empty($rt)) {
            throw new APIException("Something has gone wrong with Apache .htaccess");
        }
        $parts = explode('/', $rt);
        if ((count($parts)) < 5) {
            throw new APIException("A well formed URL would be https://dev.howlate.com/api/agent/clin/assemblyversion/verb&param=value");
        }
        $clin = $parts[2];
        $AssemblyVersion = $parts[3];
        $verb = $parts[4];

        $Organisation = Organisation::getInstance(__SUBDOMAIN, 'Subdomain');
        $Clinic = Clinic::getInstance($Organisation->OrgID, $clin);

        $AgentExe = Agent::getInstance($this->Organisation->OrgID, $Clinic->ClinicID);
        if ($AssemblyVersion != $AgentExe->AgentVersionTarget) {
            $arr = array('FromVersion' => $AssemblyVersion, 'ToVersion' => $AgentExe->AgentVersionTarget);
            throw new APIException("Upgrade of Agent exe from $AssemblyVersion to $AgentExe->AgentVersionTarget is required.", $code = 400, $status = 'Upgrade required', $arr);
        }

        include_once(__SITE_PATH . '/api/' . 'agent.api.php');
        $this->api = new AgentApi($Organisation, $Clinic);
        APIReturn::ok($this->api->$verb());
    }

    public function practitioner() {
        $rt = filter_input(INPUT_GET, 'rt');
        if (empty($rt)) {
            throw new APIException("Something has gone wrong with Apache .htaccess");
        }
        $parts = explode('/', $rt);
        if ((count($parts)) < 2) {
            throw new APIException("A well formed URL would be https://dev.howlate.com/api/practitioner?where=clause&order=clause&offset=50");
        }
        $val = $parts[1];

        $Organisation = Organisation::getInstance(__SUBDOMAIN, 'Subdomain');

        include_once(__SITE_PATH . '/api/' . 'practitioner.api.php');
        $this->api = new PractitionerAPI($Organisation);

        $method = $_SERVER['REQUEST_METHOD'];
        $ret = $this->api->$method($val);
        
        APIReturn::ok($ret);
        
    }
    
    
    // overrides the one in base controller classe
    public function handle_exception($exception) {
        //Logging::trlog(TranType::AGT_ERROR, $exception->getMessage(), $this->org->OrgID);
        if ($exception instanceof APIException) {
            throw $exception;
        } else {
            throw new APIException($message = $exception->getMessage(), $code = 400, $status = 'Unhandled exception');
        }
    }

}
