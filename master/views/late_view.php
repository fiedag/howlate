<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">

<link media="screen" href="/styles/howlate_base.css" type="text/css" rel="stylesheet">
<link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">

<script type="text/javascript">
  $(document).ready(function() {
    $("#bookmarkme").click(function() {
      if (window.sidebar) { // Mozilla Firefox Bookmark
        window.sidebar.addPanel(location.href,document.title,"");
      } else if(window.external) { // IE Favorite
        window.external.AddFavorite(location.href,document.title); }
      else if(window.opera && window.print) { // Opera Hotlist
        this.title=document.title;
        return true;
  }
});
</script>



</head>
<body>
<table>
<tr>
<td id="when"><?php echo $when_refreshed; ?></td><td id="refresh" onClick="history.go(0)" VALUE="Refresh">Refresh</td>
</tr>
</table>
<p />
<?php 
		foreach($lates as $clinic => $latepract ) {
?>
		<table class="lateness">
		
<?php
			$i = 0;
			foreach($latepract as $key => $r) {
				/* if first iteration, display extra column showing img */
					$i++;
					if ($i == 1) {
						?><tr>
										<td class="clinrow"><img class="logo" src="/pri/<?php echo $r->Subdomain; ?>/logo.png"></td> 
										<td class="clinrow"><?php echo $clinic . ' (' . $r->OrgID . ')'; ?></td>
							</tr>
						<?php
					
					}
?>
					<tr>
						<td class="practrow" colspan=2><?php echo $r->AbbrevName; ?> is running <?php echo $r->MinutesLateMsg; ?></td>
					</tr>

<?php
			}
?>
		</table>
		<p />
<?php 
		}
?>


<span id="footer">
	<span id="refresh" onClick="history.go(0)" VALUE="Refresh">Refresh</span>
	<a id="bookmarkme" href="#" rel="sidebar" title="bookmark this page">Bookmark This Page</a>
</span>

</body>

</html>