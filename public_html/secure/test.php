<?php

	include "session.inc";
    echo '<br /><a href="page2.php?' . SID . '">page 2</a>';
	
	function check_auth($passwd) {
		echo "check_auth for password $passwd returning 4 <br>";
		return 4;
	}
	
?>

<html>
	<head>
		<title>login </title>
	</head>
	<body>
	
<?php 
			if (isset($_POST['login']) && ($_POST['login'] == 'Log in') &&
				($uid = check_auth($_POST['password']))) {
				echo " user successfully logged in, setting cookie<br>";
				$_SESSION['uid'] = $uid;
				header('Location: http://how-late.com/helloworld.php');
			}
			else {
?>		
			<form name='login' method='post' action="">
				<table>
                    <tr>
                        <td class="f1_label">User Name :</td><td><input type="text" name="username" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td class="f1_label">Password  :</td><td><input type="password" name="password" value=""  />
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <input type="submit" name="login" value="Log In" style="font-size:18px; " />
                        </td>
                    </tr>
                </table>
			</form>
<?php 
			}
?>
	</body>
</html>
