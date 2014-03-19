<html>
	<head>
		<title>Symbolic links </title>
	</head>
	<body>
	
<?php 
			if (isset($_POST['target']) ) {

				$target = $_POST['target'];
				echo "Target: " . $target;
				$link = $_POST['link'];
				symlink($target, $link) or die('Could not create link:' . error_get_last());
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
