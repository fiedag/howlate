<html>


    <?php $controller->get_header(); ?>

    <body>

        
        <div class="container primary-content">
            <div>
                The Amount due calculated based on the number and size of clinics on each billing day.<br>
                Your next billing day is <?php echo $billing_day; ?>
                
            </div>
            
            <?php $controller->getPricing(); ?>
        </div>

    </body>



    <?php $controller->get_footer(); ?>    

</html>