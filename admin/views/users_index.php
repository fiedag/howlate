<?php $controller->get_header(); ?>


<!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/bean.js"></script>
<![endif]-->


<script>
  function gotoReset(orgid,userid,email) {
      document.getElementById('resetorgid').value = orgid;
      document.getElementById('resetuserid').value = userid;
      document.getElementById('resetemail').value = email;  
      document.getElementById('emailaddress').innerHTML = email;
   
      window.location.href = "#modal-show";
  }

</script>


<div class='container primary-content'>

<?php $controller->getXcrudTable(); ?>
    
</div>
<!-- A modal with its content -->
<section class="modal--show" id="modal-show"
         tabindex="-1" role="dialog" aria-labelledby="label-show" aria-hidden="true">

    <div class="modal-inner">
        <header>
            <h2 id="label-show">Reset Password for this user. A link will be sent to <span id='emailaddress' name='emailaddress'</h2>
        </header>

        <div class="modal-content">
	<form name="reset" action="/users/passwordreset" method='POST'>
		<input type="hidden" id="resetorgid" name="resetorgid" readonly='readonly'>
		<input type="hidden" id="resetuserid" name="resetuserid" readonly='readonly'>
		<input type="hidden" id="resetemail" name="resetemail" readonly='readonly'>
    		<input type="submit" id="submit" name="submit" value="Reset">
	</form>

        </div>
    </div>

    <footer>
        <p>Footer</p>
    </footer>
</div>
<a href="#!" class="modal-close" title="Close this modal"
   data-dismiss="modal" data-close="Close">&times;</a>
</section>
    
    
<?php $controller->get_footer(); ?>


