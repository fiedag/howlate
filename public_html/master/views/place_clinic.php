<script type="text/javascript">

$(document).ready(function() {

	$('#table-3').tableDnD({
	    onDrop: function(table, row) {
	        alert("Result of $.tableDnD.serialise() is "+$.tableDnD.serialize());
		    //$('#AjaxResult').load("/articles/ajaxTest.php?"+$.tableDnD.serialize());
        }
	});  
});


</script>


<form name="place" id="place" action="/place/save" method="post">

    <div class='container primary-content'>

        <table id="table-3">
            <?php
            $j = 0;
            $lastclinic = "";
            foreach ($controller->org->Practitioners as $pract) {
                if ($pract->ClinicPlaced != $lastclinic) {
                    $lastclinic = $pract->ClinicPlaced;
                    ?>
                    <tr>
                        <td class="bg_systemcolor2"><?php echo $pract->ClinicName; ?></td><td></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td><input type="hidden" name="<?php echo $pract->ID; ?>" id="<?php echo $pract->ID; ?>" value="<?php echo $pract->ClinicName; ?>"><?php echo "$pract->FullName"; ?></td><td><?php echo "$pract->ClinicPlaced"; ?></input></td>
                </tr>
                <?php
            }
            ?>

        </table>

        <script type="text/javascript">
            var table = document.getElementById('table-3');
            var tableDnD = new TableDnD();
            tableDnD.init(table);
            console.log('declared tableDND');
        </script>

        
        
    </div>

    <input type='submit' class='medium green button' id='save' name='Save' value='Save' />

</form>
