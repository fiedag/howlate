<?php
abstract class MyErrorTypes
{
	const E_APP_ERROR = 1;
	const E_API_ERROR = 2;
	const E_DATA_ERROR = 4;
	const E_OTHER_ERROR = 0;
}

/*
 * 
 * Invoked below.  This custom error handler is used
 * whenever a trigger_error is encountered.
 * 
 */
function customErrorHandler($errno, $errstr, $errfile, $errline)
{

    if (!(error_reporting() & $errno)) {
        echo("This error code is not included in error_reporting, errno =  $errno, errstr = $errstr <br>");
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
			echo "ERROR: [$errno] $errstr <br>";
			echo "File $errfile , line $errline " . "<br>";
			echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")<br>";
			echo "Exiting...<br>";
			exit(1);
			break;
	
		case E_USER_WARNING:
			$db = new howlate_db();
			$db->write_error($errno, $errtype, $errstr, $errfile, $errline);
			echo "WARNING: [$errno] $errstr <br>";
			echo "File $errfile , line $errline " . "<br>";
			echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")<br>";
			break;
	
		case E_USER_NOTICE:
			echo "NOTICE: [$errno] $errstr <br>";
			echo "File $errfile , line $errline " . "<br>";
			echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")<br>";
			break;
	
		default:
                        $db = new howlate_db();
			$db->write_error($errno, $errtype, $errstr, $errfile, $errline);
			echo "Unknown error type: [$errno] $errstr <br>";
			echo "File $errfile , line $errline " . "<br>";
			echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")<br>";
                        exit(1);
			break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

set_error_handler("customErrorHandler");

?>
