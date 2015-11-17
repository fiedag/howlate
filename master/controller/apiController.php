<?php

Class ApiController Extends baseController {

    protected $api;
    
    // overrides the one in base controller classe
    public function handle_exception($exception) {
        
        echo APIReturn::error($exception->getMessage(),false);
        exit;
    }    
    
    public function index() {
        $this->registry->template->show('api_index');
    }
    
    public function agent() {
        $rt = filter_input(INPUT_GET, 'rt');
        if (empty($rt)) {
            echo APIReturn::error("Something has gone wrong with Apache .htaccess");
            exit;
        }
        
        $parts = explode('/', $rt);
        if ((count($parts)) < 5) {
            echo APIReturn::error('A well formed URL would be https://dev.howlate.com/api/agent/clin/assemblyversion/verb&param=value');
            exit;
        }
        $clin = $parts[2];
        $AssemblyVersion = $parts[3];
        $verb = $parts[4];
        
        $Organisation = Organisation::getInstance(__SUBDOMAIN, 'Subdomain');
        $Clinic = Clinic::getInstance($Organisation->OrgID, $clin);
        
        $AgentExe = Agent::getInstance($this->Organisation->OrgID, $Clinic->ClinicID);
        
        if ($AssemblyVersion != $AgentExe->AgentVersionTarget) {
            $arr = array('FromVersion' => $AssemblyVersion, 'ToVersion' => $AgentExe->AgentVersionTarget);
            echo APIReturn::error("Upgrade of Agent exe from $AssemblyVersion to $AgentExe->AgentVersionTarget is required.", $arr);
            exit;
        }

        include_once(__SITE_PATH . '/api/' . 'agent.api.php');
        $this->api = new AgentApi($Organisation, $Clinic, $AssemblyVersion);

        try {
            if(method_exists($this->api, $verb)) {
                echo $this->api->$verb();  // handles the return of data or errors then exits
                exit;
            }
            else {
                echo APIReturn::error("Agent method $verb does not exist.",true);
                exit;
           }
        }
        catch(APIException $apiex) {
            echo $apiex->Content;
            exit;
        }
        catch(Exception $ex) {  // in case of unhandled exception
            echo APIReturn::error($ex->Message, $ex->getTraceAsString());
            exit;
        }
    }

    public function download() {
        $rt = filter_input(INPUT_GET, 'rt');
        if (empty($rt)) {
            echo APIReturn::error("Something has gone wrong with Apache .htaccess");
            exit;
        }
        
        $parts = explode('/', $rt);
        if ((count($parts)) < 4) {
            echo APIReturn::error('A well formed URL would be https://dev.howlate.com/api/download/clin/verb&param=value');
            exit;
        }
        $clin = $parts[2];
        $verb = $parts[3];
        
        $Organisation = Organisation::getInstance(__SUBDOMAIN, 'Subdomain');
        $Clinic = Clinic::getInstance($Organisation->OrgID, $clin);
        

        include_once(__SITE_PATH . '/api/' . 'download.api.php');
        $this->api = new DownloadApi($Organisation, $Clinic);

        try {
            if(method_exists($this->api, $verb)) {
                $this->api->$verb();  // handles the return of data or errors then exits
                exit;
            }
            else {
                echo APIReturn::error("Download method $verb does not exist.",true);
                exit;
           }
        }
        catch(APIException $apiex) {
            echo $apiex->Content;
            exit;
        }
        catch(Exception $ex) {  // in case of unhandled exception
            echo APIReturn::error($ex->Message, $ex->getTraceAsString());
            exit;
        }
    }

    
    

    
    public function practitioner() {
        $rt = filter_input(INPUT_GET, 'rt');
        if (empty($rt)) {
            APIReturn::error("Something has gone wrong with Apache .htaccess");
        }
        $parts = explode('/', $rt);
        if ((count($parts)) < 2) {
            APIReturn::error("A well formed URL would be https://dev.howlate.com/api/practitioner?where=clause&order=clause&offset=50");
        }
        $val = $parts[1];

        $Organisation = Organisation::getInstance(__SUBDOMAIN, 'Subdomain');

        include_once(__SITE_PATH . '/api/' . 'practitioner.api.php');
        $this->api = new PractitionerAPI($Organisation);

        $method = $_SERVER['REQUEST_METHOD'];
        $ret = $this->api->$method($val);
        
        APIReturn::ok($ret);
    }
    
    public function clin() {
        $OrgID = filter_input(INPUT_GET,'org');
        $ClinicID = filter_input(INPUT_GET,'clin');
        
        $Clinic = Clinic::getInstance($OrgID, $ClinicID);
        echo json_encode($Clinic);
    }    
    
    


}
