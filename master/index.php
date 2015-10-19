<?php

//
// Entry point for MVC for how-late website.
// subdomain.how-late.com redirects to public_html/master/index.php
//

/* * * error reporting on ** */
error_reporting(E_ALL);

$host = $_SERVER["SERVER_NAME"];  // e.g. m.how-late.com
$firstdot = strpos($host, '.');
define('__FQDN', $host);
define("__DOMAIN", substr($host, $firstdot + 1));
define('__SUBDOMAIN', substr($host, 0, $firstdot));
define('__SITE_PATH', realpath(dirname(__FILE__)));  // e.g. /home/howlate/public_html/master 

include __SITE_PATH . '/application/' . 'controller_base.class.php';
include __SITE_PATH . '/application/' . 'registry.class.php';
include __SITE_PATH . '/application/' . 'router.class.php';
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

// agent API loaded manually
include __SITE_PATH . '/api/' . 'agent.api.php';


include_once "includes/error_handler.php";

$registry = new registry;
$registry->router = new router($registry);
$registry->router->setPath(__SITE_PATH . '/controller');
$registry->template = new template($registry);
$registry->router->loader();
