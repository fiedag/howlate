<?php
	function check_auth($pass) {
		return 4;
	}
	
	if (isset($_POST['login']) && ($_POST['login'] == 'Log in') && ($uid = check_auth($_POST['password']))) {
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
						<input type="submit" name="login" value="Log in" />
					</td>
				</tr>
			</table>
		</form>
<?php
	}
?>
</body>
</html>
