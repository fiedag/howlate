<?php include 'includes/howlate_sessioncheck.php'; ?>
<?php $controller->get_header(); ?>


<?php $controller->get_valid_lateness_datalist(); ?>


<div class='container primary-content'>
    <div class="clinic-header">
        <?php $controller->show_clinic_header(); ?>
    </div>

    <?php $controller->show_lateness_form(); ?>


</div>



<?php $controller->get_footer(); ?>


