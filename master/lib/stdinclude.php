<?php 
	include_once("error_handler.php");	
	date_default_timezone_set('Australia/Adelaide');
	
	$host = $_SERVER["SERVER_NAME"];
	$subd = substr($host, 0, strpos($host, '.how-late.com'));

	define('__SUBDOMAIN', $subd);
	
	
?>