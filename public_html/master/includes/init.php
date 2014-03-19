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

function __autoload($class_name) {
    $filename = strtolower($class_name) . '.class.php';
    $file = __SITE_PATH . '/model/' . $filename;

    if (file_exists($file) == false) {
        return false;
    }
    include ($file);
}

include_once("error_handler.php");
date_default_timezone_set('Australia/Adelaide');

$host = $_SERVER["SERVER_NAME"];
$subd = substr($host, 0, strpos($host, '.how-late.com'));

define('__SUBDOMAIN', $subd);



/* * * a new registry object ** */
$registry = new registry;

/* * * create the database registry object ** */
// $registry->db = db::getInstance();
?>
