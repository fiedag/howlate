<html>


    <?php $controller->get_header(); ?>

    <body>

        
        <div class="container primary-content">
            <div>
                Your next billing day is <?php echo date($billing_day); ?><br>
                You are currently on the <b> <?php echo $package_name; ?> </b> package.(<?php echo $package_id; ?>)<br>
                <?php echo "$item_descrip ($item_id)"; ?><br>
                Login to your <a href="https://how-late.chargeover.com">billing portal</a> to check invoices and payments.
                <br>
            </div>
        </div>

    </body>



    <?php $controller->get_footer(); ?>    

</html>