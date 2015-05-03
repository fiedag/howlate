<?php
/* * * include the controller class ** */
include __SITE_PATH . '/application/' . 'controller_base.class.php';
/* * * include the registry class ** */
include __SITE_PATH . '/application/' . 'registry.class.php';
/* * * include the router class ** */
include __SITE_PATH . '/application/' . 'router.class.php';
/* * * include the template class ** */
include __SITE_PATH . '/application/' . 'template.class.php';


/* * * auto load model classes ** */
spl_autoload_register(function ($class_name) {
    $filename = strtolower($class_name) . '.class.php';
    $file = __SITE_PATH . '/model/' . $filename;
    if (file_exists($file) == false) {
        return false;
    }
    include ($file);
});

//date_default_timezone_set('Australia/Adelaide');

$host = $_SERVER["SERVER_NAME"];
$firstdot = strpos($host,'.');

define("__DOMAIN", substr($host, $firstdot + 1));
define('__SUBDOMAIN', substr($host, 0, $firstdot));
define('__FQDN', $host);


include_once("error_handler.php");

/* * * a new registry object ** */
$registry = new registry;


/* * * create the database registry object ** */
// $registry->db = db::getInstance();



set_exception_handler('unh_excep');

function unh_excep() {
    
    
}


?>
