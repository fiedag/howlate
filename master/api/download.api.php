<?php

class DownloadApi {

    protected $Organisation;
    protected $Clinic;

    function __construct(Organisation $Organisation, Clinic $Clinic) {
        $this->Organisation = $Organisation;
        $this->Clinic = $Clinic;
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
    
    public function HowLateAgentExeConfig() {
        $record = Agent::getInstance($this->Organisation->OrgID, $this->Clinic->ClinicID);
        $URL = "https://" . __FQDN . "/api";
        $Credentials = $record->HLUserID . "," . $record->XPassword;
        include(__SITE_PATH . '/views/agent_config.php');
        exit;  // necessary to prevent a standard JSON return code being presented to the caller
    }
        
}

