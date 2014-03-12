<?php

	session_start();
	$_SESSION = array();

	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	}

	// Finally, destroy the session.
	session_destroy();
	
	echo 'You are logged out.  <a href="https://how-late.com/login.php">Log back in.</a><br>';
	
?>
