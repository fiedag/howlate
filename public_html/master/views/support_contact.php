
<?php $controller->get_header(); ?>


<div class='container primary-content'>

    <form id="org" name="org" method="post" action="/support/contactsubmit">
        <div class="control-group">
            <label class="control-label" for="Note">Note:</label>
            <textarea class="controls" id="Note" name="Note" rows="5" cols="80"></textarea>
        </div>
        <input type="hidden" readonly="readonly" class="controls" id="OrgID" name="OrgID" value="<?php echo $controller->org->OrgID;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="Subdomain" name="Subdomain" value="<?php echo $controller->org->Subdomain;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="FQDN" name="FQDN" value="<?php echo $controller->org->FQDN;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="UpdIndic" name="UpdIndic" value="<?php echo $controller->org->UpdIndic;?>"><br>
        <div class="xcrud-nav">
            <button class="xcrud-button xcrud-cyan " type="submit" name="Submit" value="Submit">Submit</button>
        </div>
        

    </form>



</div>


<?php $controller->get_footer(); ?>


