<?php

//
// Entry point for MVC for how-late admin website.
// subdomain.how-late.com redirects to public_html/master/index.php
//
$host = $_SERVER["SERVER_NAME"];  // e.g. m.how-late.com
$firstdot = strpos($host, '.');

define("__DOMAIN", substr($host, $firstdot + 1));
define('__SUBDOMAIN', substr($host, 0, $firstdot));
define('__FQDN', $host);

/* * * error reporting on ** */
error_reporting(E_ALL);

/* * * define the site path ** */
$site_path = realpath(dirname(__FILE__));
define('__SITE_PATH', $site_path);  // e.g. /home/howlate/public_html/admin in this case

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

include_once("includes/error_handler.php");
//date_default_timezone_set('Australia/Adelaide');

/* * * a new registry object ** */
$registry = new registry;

/* * * load the router ** */
$registry->router = new router($registry);

/* * * set the controller path ** */
$registry->router->setPath(__SITE_PATH . '/controller');

/* * * load up the template ** */
$registry->template = new template($registry);

/* * * load the controller ** */
$registry->router->loader();
?>
