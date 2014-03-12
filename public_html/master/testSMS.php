<html>

<head>

</head>

<body>
    <form id="form1" name="form1" method="post" action="">
		From:<input type="text" name="from"><br>
		To:<input type="text" name="to"><br>
		Message:<input type="text" name="message"><br>
		<input type="submit" name="submit" value="submit">
    </form> 
</body>


<?php

	function __autoload($classname) {
		$filename = "./lib/". $classname . ".php";
		include_once($filename);
	}

	echo "Is submit set?<br>";
    if(isset($_POST['submit']))
	{
		$from  = $_REQUEST['from'];
		$to  = $_REQUEST['to'];
		$message = $_REQUEST['message'];
		
		$click = new clickatell();

		echo "Sending $message to $to <br>";
		
		$click->httpSend($from, $to, $message);

		echo "<center>SMS sent</center>";
	}

	
?>
