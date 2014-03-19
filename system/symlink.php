<html>
	<head>
		<title>Site creation test </title>
	</head>
	<body>
	
<?php 
			include_once( dirname(__FILE__) . '/../lib/stdinclude.php');

			if (isset($_POST['subdomain']) ) {
				$subdomain = $_POST['subdomain'];
				$site = new howlate_site($subdomain);
				$site->create($subdomain);
				
				echo "Created subdomain ok<br>";
				
			}
			else {
?>		
			<form name='login' method='post' action="">
				<table>
                    <tr>
                        <td class="f1_label">Subdomain :</td><td><input type="text" name="subdomain" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <input type="submit" name="login" value="Create subdomain" style="font-size:18px; " />
                        </td>
										</tr>
                </table>
			</form>
<?php 
			}
?>
	</body>
</html>