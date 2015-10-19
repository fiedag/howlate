<?php


/**
 * Description of agent
 * This class is a helper class for downloading and using the HowLateAgent.exe program
 * @author Alex
 */
class agent {
     protected static $instance;
     protected static $BestDefaultVersion = "2.5.6.5";
     public $Platform;
             
     public static function getInstance($OrgID, $ClinicID) {
        $q = "SELECT * FROM vwClinicIntegration WHERE OrgID = '$OrgID' AND ClinicID = $ClinicID";
        $sql = MainDb::getInstance();

        if ($result = $sql->query($q)->fetch_object()) {
            if (!self::$instance) {
                self::$instance = new self();
            }
            foreach ($result as $key => $val) {
                self::$instance->$key = $val;
            }
            if(self::$instance->Agent32Bit) {
                self::$instance->Platform = "x86";
            }
            else {
                self::$instance->Platform = "x64";
            }
            
            if(self::$instance->AgentVersionTarget=="") {
                self::$instance->AgentVersionTarget = self::$BestDefaultVersion;
            }
            return self::$instance;
        } else
            throw new Exception("Cannot be found");
    }
    /*
     * return the contents of the HowLateAgent.exe file
     * and is called by the HowLateAgentUpdater.exe
     * and interactively when settng up Integration
     */
    public function get_exe() {
        
        $file = __SITE_PATH . "/downloads/$this->Platform/$this->AgentVersionTarget/HowLateAgent.exe";
        if (file_exists($file)) {
            if(!headers_sent()) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($file));
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
            }
            readfile($file);
            exit;
        } else {
            trigger_error("File $file does not exist.", E_USER_ERROR);
        }
    }

    /*
     * returns an array of valid version numbers
     * ('3.2.1.1','2.5.6.5') etc.
     */
    public function valid_versions() {
        
        $path = __SITE_PATH . '/downloads/' . $this->Platform;
        $results = scandir($path);
        foreach ($results as $result) {
            if ($result === '.' or $result === '..')
                continue;

            if (is_dir($path . '/' . $result)) {
                $res[] = $result;
            }
        }
        return $res;
    }    
    
    
}
