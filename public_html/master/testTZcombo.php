<!DOCTYPE html>
<html>

<head>

</head>
<body>
    <form id="form1" name="form1" method="post" action="">
        list of timezones:
        <select name='timezone'>
            <option value="">--- Select ---</option>
            <?php
            mysql_connect("localhost","howlate_super","bdU,[}B}k@7n");
            mysql_select_db("howlate_main");
            $list=mysql_query("SELECT CodeVal FROM usercodes WHERE userCode = 'TZ' AND CodeVal like 'Australia%'");
            while($row_list=mysql_fetch_assoc($list)){
            ?>
				<option value="<?php echo $row_list['CodeVal'];?>">
					<?php echo $row_list['CodeVal'];?>
				</option>
            <?php
            }
            ?>
        </select><br>
		TZ: <input type="text" name="tz"><br>
		<input type="submit" name="submit" value="submit">
    </form> 
</body>
</html>


<?php
	if (isset($_POST["submit"])) {
		$tz = $_POST["timezone"];
		echo $tz . "<br>";
		if (date_default_timezone_set($tz) ) {
			echo date('l jS \of F Y h:i:s A');
		}
		$tz = $_POST["tz"];
		echo $tz . "<br>";
		if (date_default_timezone_set($tz) ) {
			echo date('l jS \of F Y h:i:s A');
		}
	
	}




?>
