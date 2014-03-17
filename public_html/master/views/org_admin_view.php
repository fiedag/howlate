<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">

<link media="screen" href="/styles/howlate_base.css" type="text/css" rel="stylesheet">
<link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">
<link rel="apple-touch-icon" href="/pri/<?php echo __SUBDOMAIN; ?>/logo.png" >


<script>
	function gotoInvite(pin) {
		//document.getElementById(pin).innerHTML = document.getElementById(pin).innerHTML + "*"; 
		document.getElementById('invitepin').value = pin;
		document.getElementById('invitepin2').innerHTML = pin;
		window.location.href = "#invite";
	}
	


</script>

</head>
<body>

This is the body of org_admin for subdomain <?php echo __SUBDOMAIN	; ?>

<h1>Organisation: <?php echo $org->OrgName; ?></h1>

<table>
<?php
	foreach($org->Clinics as $ckey => $clinic) {
		echo "<th> $clinic->ClinicName  </th>";
		foreach($org->Practitioners as $pkey => $practitioner) {
			if ($clinic->ClinicName == $practitioner->ClinicName) {
				echo "<tr><td>Practitioner: $practitioner->AbbrevName </td><td>";	
				echo "<div id='$practitioner->Pin' onclick=\"gotoInvite('$practitioner->Pin')\">$practitioner->Pin</div></td></tr>";
			}
		}
	}

?>

</table>


<div id="invite" class="modalDialog">
	<div> 
	<a href="#close" title="Close" class="close">X</a>
	<h2>Invite a smartphone user to updates for <p id="invitepin2">Pin goes here</p></h2>
	<p>Please enter a mobile phone number to send an invitation to</p>
	<form name="invite" action="org_admin/invite">
		<input type="text" id="invitepin" name="invitepin">
		Mobile:<input type="text" id="udid" name="udid">
		<input type="submit" id="submit" name="submit" value="Invite">
	</form>
	</div>
</div>

</body>
</html>
