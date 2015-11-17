<div class='container primary-content'>
 
    
    <form id="update" action="/billing/delete_co_cust" method="POST">
        <div class="control-group">
            <label class="control-label" for="CustomerID">CO Customer ID:</label>
            <input type="text" class="controls" id="CustomerID" name="CustomerID" value="" required>
            <input class="btn btn-primary" type="submit" value="Delete Chargeover customer" name="submit">
        </div>
    </form>



</div>


<?php $controller->get_footer(); ?>
