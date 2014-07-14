<?php
//
// Entry point for MVC for how-late admin website.
// subdomain.how-late.com redirects to public_html/master/index.php
//
 $host = $_SERVER["SERVER_NAME"];  // e.g. secure.how-late.com

 /*** error reporting on ***/
 error_reporting(E_ALL);

 /*** define the site path ***/
 $site_path = realpath(dirname(__FILE__));
 define ('__SITE_PATH', $site_path);  // e.g. /home/howlate/public_html/master 

 /*** include the init.php file ***/
 include 'includes/init.php';
 
 /*** load the router ***/
 $registry->router = new router($registry);

 /*** set the controller path ***/
 $registry->router->setPath (__SITE_PATH . '/controller');

 /*** load up the template ***/
 $registry->template = new template($registry);

 /*** load the controller ***/
 $registry->router->loader();

?>
