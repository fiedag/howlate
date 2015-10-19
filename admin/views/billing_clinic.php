<div class='container primary-content'>
    <?php echo $xcrud_content; ?>
    
    <?php if($controller->package) { ?>
    
    <h3>Currently active package = <?php if($controller->package) {echo $controller->package->package_id;} ?></h3>

    <table>
        <th>line_item_id</th>
        <th>descrip</th>
        <th>item_external_key</th>
    <?php
    foreach($controller->package->line_items as $key=>$val) {
    ?>
        <tr>
            <td><?php echo $val->line_item_id; ?></td>
            <td><?php echo $val->descrip; ?></td>
            <td><?php echo $val->external_key;?></td>
            <td><?php echo $val->item_external_key;?></td>
        </tr>    
        
    <?php    
    }
    ?>
        
    </table>
    
    <form id="update" action="/billing/packageline" method="POST">
        <input type="hidden" id="OrgID" name="OrgID" value="<?php echo $controller->Organisation->OrgID;?>">
        
        <div class="control-group">
            <label class="control-label" for="package_id">Package ID:</label>
            <input type="text" readonly class="controls" id="package_id" name="package_id" size="24" value="<?php echo $controller->package->package_id; ?>"></input><br>
        </div>
        <div class="control-group">
            <label class="control-label" for="line_item_id">Line Item ID:</label>
            <input type="text" class="controls" id="line_item_id" name="line_item_id" size="24"></input><br>
        </div>
        <div class="control-group">
            <label class="control-label" for="external_key">External Key (ClinicID):</label>
            <input type="text" class="controls" id="external_key" name="external_key" size="24"></input><br>
        </div>
        <div class="control-group">
            <label class="control-label" for="descrip">Descrip:</label>
            <input type="text" class="controls" id="descrip" name="descrip" size="24"></input><br>
        </div>
        <div class="control-group">
            <label class="control-label" for="item_quantity">Qty:</label>
            <input type="text" class="controls" id="item_quantity" name="item_quantity" size="24"></input><br>
        </div>
        <input type="submit" value="Update Package Line" name="submit">
    </form>




</div>

<?php } ?>

<?php $controller->get_footer(); ?>
