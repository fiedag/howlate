<html>
	<head>
		<title>Symbolic links </title>
	</head>
	<body>
	
<?php 
			include_once('./lib/stdinclude.php');

			if (isset($_POST['target']) ) {

				$target = $_POST['target'];
				$link = $_POST['link'];
				//unlink($link);
				echo "Creating link...<br>"; 
				if (!symlink($target, $link)) {
					trigger_error('Could not create link', E_USER_ERROR);
				}

				echo "Created link ok<br>";
				
				//chmod($link,0644) or die('Could not chmod link:' . print_r(error_get_last()));
			}
			else {
?>		
			<form name='login' method='post' action="">
				<table>
                    <tr>
                        <td class="f1_label">Target :</td><td><input type="text" name="target" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td class="f1_label">Link  :</td><td><input type="text" name="link" value=""  />
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <input type="submit" name="login" value="Create symbolic link" style="font-size:18px; " />
                        </td>
										</tr>
                </table>
			</form>
<?php 
			}
?>
	</body>
</html>