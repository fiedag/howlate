<?php include 'includes/howlate_sessioncheck.php'; ?>
<?php $controller->get_header(); ?>


<div class='container primary-content'>

<table>

<?php foreach($controller->org->Clinics as $key => $value) { ?>
  <tr>
		<td><?php echo "Key = $key";?></td>
		<td><?php echo "Value = $value->ClinicName";?></td>
		<td><?php echo "ID = $value->Phone";?></td>
		<td><?php echo "ID = $value->Address1";?></td>
		<td><?php echo "ID = $value->Address2";?></td>
		<td><?php echo "ID = $value->City";?></td>
		<td><?php echo "ID = $value->Zip";?></td>
		<td><?php echo "ID = $value->State";?></td>		
  </tr>
<?php } ?>


</table>
</div>


<?php $controller->get_footer(); ?>


