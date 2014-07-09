
<?php $controller->get_header(); ?>

<script>
  function gotoAssign(orgid,surrogKey) {
      document.getElementById('assignorgid').value = orgid;
      document.getElementById('assignsurrogkey').value = surrogKey;
     
      window.location.href = "#assign";
  }


</script>



<div class='container primary-content'>

<?php $controller->getXcrudTable(); ?>
    
    
</div>


<div id="assign" class="modalDialog">
	<div> 
	<a href="#close" title="Close" class="close">X</a>
	<h2>Assign this practitioner to a new clinic </h2>
	<form name="assign" action="/pract/assign" method='POST'>
		<input type="hidden" id="assignorgid" name="assignorgid" readonly='readonly'>
		<input type="hidden" id="assignsurrogkey" name="assignsurrogkey" readonly='readonly'>
                <select name="selectedclinic" id="selectedclinic" class="dropdown" >
                    <?php $controller->get_clinic_options(); ?>
                </select>
    		<input type="submit" id="submit" name="submit" value="Assign">
	</form>
	</div>
</div>


<?php $controller->get_footer(); ?>


