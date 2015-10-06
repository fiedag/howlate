
<?php $controller->get_header(); ?>


<div class='container primary-content'>

    <form id="org" name="org" method="post" action="/support/contactsubmit">

        <div class="alert alert-info">
            <h3>    <?php echo $msg; ?></h3>
        </div>
        <div class="form-group">
            <label class="control-label" for="Note">Message:</label>
            <textarea class="form-control" id="Note" name="Note" rows="5" cols="80" required></textarea>
            <input type="hidden" readonly="readonly" class="form-control" id="OrgID" name="OrgID" value="<?php echo $controller->org->OrgID; ?>"><br>
            <input type="hidden" readonly="readonly" class="form-control" id="Subdomain" name="Subdomain" value="<?php echo $controller->org->Subdomain; ?>"><br>
            <input type="hidden" readonly="readonly" class="form-control" id="FQDN" name="FQDN" value="<?php echo $controller->org->FQDN; ?>"><br>
            <input type="hidden" readonly="readonly" class="form-control" id="UpdIndic" name="UpdIndic" value="<?php echo $controller->org->UpdIndic; ?>"><br>

            <button class="btn btn-primary" type="submit" name="Submit" value="Submit"><div class="glyphicon glyphicon-envelope">&nbsp;</div>Contact me!</button>
        </div>
    </form>
</div>


<?php $controller->get_footer(); ?>


