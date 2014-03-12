<?php 


	if (!function_exists('__autoload')) {
		function __autoload($classname) {
			global $ROOT, $LIB;
			$filename = $LIB . '/' . $classname . '.php';
			include_once($filename);
		}
	}


	include_once("error_handler.php");	
	
	date_default_timezone_set('Australia/Adelaide');

?>