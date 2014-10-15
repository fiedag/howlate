<?php


/*
 * called by the apiController class
 * the methods in this class return valid
 * arrays which can be JSON encoded
 * else a string like 
 * "The text of the error".
 * 
 * 
 */
class howlate_api {

    
    ///
    /// This has all notifications in the post parameters 
    ///

    
    

    public static function agent_start() {
        $credentials = filter_input(INPUT_POST, "credentials");
        //if ($credentials == null) {
            //throw new Exception("Credentials not supplied.");
        //}
        list($userid, $passwordhash) = explode(".", $credentials);
        $db = new howlate_db();

        $org = new organisation();
        $org->getby(__SUBDOMAIN, "Subdomain");
        if (!$db->isValidPassword($org->OrgID, $userid, $passwordhash)) {
            throw new Exception("Invalid Credentials.");
        }
        $assemblyversion = filter_input(INPUT_POST, "assemblyversion");
        $clinic = filter_input(INPUT_GET, "clin");
        $msg = "Agent version " . $assemblyversion . " started at clinic " . $clinic;
        $db->trlog(TranType::AGT_START, $msg, $org->OrgID, $clinic, null,null);
        return $msg;

    }
    
    public static function agent_stop() {
        $credentials = filter_input(INPUT_POST, "credentials");
        //if ($credentials == null) {
            //throw new Exception("Credentials not supplied.");
        //}
        list($userid, $passwordhash) = explode(".", $credentials);
        $db = new howlate_db();

        $org = new organisation();
        $org->getby(__SUBDOMAIN, "Subdomain");
        if (!$db->isValidPassword($org->OrgID, $userid, $passwordhash)) {
            throw new Exception("Invalid Credentials.");
        }
        $assemblyversion = filter_input(INPUT_POST, "assemblyversion");
        $clinic = filter_input(INPUT_GET, "clin");
        $msg = "Agent version " . $assemblyversion . " stopped at clinic " . $clinic;
        $db->trlog(TranType::AGT_STOP, $msg, $org->OrgID, $clinic, null,null);
        return $msg;
    }
    
    
    public static function agent_error() {
        $credentials = filter_input(INPUT_POST, "credentials");
        //if ($credentials == null) {
            //throw new Exception("Credentials not supplied.");
        //}
        list($userid, $passwordhash) = explode(".", $credentials);
        $db = new howlate_db();

        $org = new organisation();
        $org->getby(__SUBDOMAIN, "Subdomain");
        if (!$db->isValidPassword($org->OrgID, $userid, $passwordhash)) {
            throw new Exception("Invalid Credentials.");
        }
        $error = filter_input(INPUT_POST, "error");
        $clinic = filter_input(INPUT_GET, "clin");
        $msg = "Agent Error reported: " . $error . " at clinic " . $clinic;
        $db->trlog(TranType::AGT_ERROR, $msg, $org->OrgID, $clinic, null,null);
        return $msg;
    }
    
    public static function agent_info() {
        $credentials = filter_input(INPUT_POST, "credentials");
        list($userid, $passwordhash) = explode(".", $credentials);
        $db = new howlate_db();

        $org = new organisation();
        $org->getby(__SUBDOMAIN, "Subdomain");
        if (!$db->isValidPassword($org->OrgID, $userid, $passwordhash)) {
            throw new Exception("Invalid Credentials.");
        }
        $info = filter_input(INPUT_POST, "info");
        $clinic = filter_input(INPUT_GET, "clin");
        $msg = "Clinic $clinic Agent Info Message: " . $info;
        $db->trlog(TranType::AGT_ERROR, $msg, $org->OrgID, $clinic, null,null);
        return $msg;
    }
    
}

?>