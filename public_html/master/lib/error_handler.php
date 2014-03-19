<?php

/*
abstract class MyErrorTypes
{
	const E_APP_ERROR = 1;
	const E_API_ERROR = 2;
	const E_DATA_ERROR = 4;
	const E_OTHER_ERROR = 0;
}
*/


function customErrorHandler($errno, $errstr, $errfile, $errline)
{

    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

	switch ($errstr.substr(0,1)) {
		case "1" :
			$errtype = MyErrorTypes::E_APP_ERROR;
			break;
		case "2" :
			$errtype = MyErrorTypes::E_API_ERROR;
			break;
		case "4" :
			$errtype = MyErrorTypes::E_DATA_ERROR;
			break;
		default:
			$errtype = MyErrorTypes::E_OTHER_ERROR;
			break;
	}
	
    switch ($errno) {
		case E_USER_ERROR:
			$db = new howlate_db();
			$db->write_error($errno, $errtype, $errstr, $errfile, $errline);
			echo "ERROR: $errstr <br>";
			echo "File $errfile , line $errline " . "<br>";
			echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")<br>";
			echo "Exiting...<br>";
			exit(1);
			break;
	
		case E_USER_WARNING:
			$db = new howlate_db();
			$db->write_error($errno, $errtype, $errstr, $errfile, $errline);
			echo "WARNING: $errstr <br>";
			echo "File $errfile , line $errline " . "<br>";
			echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")<br>";
			break;
	
		case E_USER_NOTICE:
			echo "NOTICE: $errstr <br>";
			echo "File $errfile , line $errline " . "<br>";
			echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")<br>";
			break;
	
		default:
			echo "Unknown error type: [$errno] $errstr<br>";
			break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

set_error_handler("customErrorHandler");

?>
