<?php
	mysql_connect("localhost","howlate_super","bdU,[}B}k@7n");
	mysql_select_db("howlate_main");
	$list=mysql_query("SELECT CodeVal FROM usercodes WHERE userCode = 'TZ' ");
	while($row_list=mysql_fetch_assoc($list)) {
		$tz =  $row_list['CodeVal'];
		echo "'" . $tz . "'" ;
		if (date_default_timezone_set($tz) ) {
			echo " is valid ! " . date('l jS \of F Y h:i:s A');
		}
		echo "<br>";
	}

?>

