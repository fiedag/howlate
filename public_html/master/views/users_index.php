<?php $controller->get_header(); ?>

<script>
  function gotoReset(orgid,userid,email) {
      document.getElementById('resetorgid').value = orgid;
      document.getElementById('resetuserid').value = userid;
      document.getElementById('resetemail').value = email;  
      document.getElementById('emailaddress').innerHTML = email;
   
      window.location.href = "#resetpass";
  }

</script>


<div class='container primary-content'>

<?php $controller->getXcrudTable(); ?>
    
    
    
</div>

<div id="resetpass" class="modalDialog">
	<div> 
	<a href="#close" title="Close" class="close">X</a>
	<h2>Reset password for this user. A link will be sent to <span id='emailaddress' name='emailaddress'></span></h2>
	<form name="reset" action="/users/passwordreset" method='POST'>
		<input type="text" id="resetorgid" name="resetorgid" readonly='readonly'>
		<input type="text" id="resetuserid" name="resetuserid" readonly='readonly'>
		<input type="text" id="resetemail" name="resetemail" readonly='readonly'>
    		<input type="submit" id="submit" name="submit" value="Reset">
	</form>

</div>


<?php $controller->get_footer(); ?>


