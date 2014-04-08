<?php include 'includes/howlate_sessioncheck.php'; ?>
<?php $controller->get_header(); ?>


<div class='container primary-content'>

    <form id="org" name="org" method="post" action="/org/update">
        <div class="control-group">
            <label class="control-label" for="OrgName">Name:</label>
            <input type="text" class="controls" id="OrgName" name="OrgName" value="<?php echo $controller->org->OrgName;?>"></input>
        </div>
        <div class="control-group">
            <label class="control-label" for="OrgShortName">Short Name:</label>
            <input type="text" class="controls" id="OrgShortName" name="OrgShortName" value="<?php echo $controller->org->OrgShortName;?>"></input><br>
        </div>
        <div class="control-group">
            <label class="control-label" for="Address1">Address:</label>
            <input type="text" class="controls" id="Address1" name="Address1" value="<?php echo $controller->org->Address1;?>"></input>
        </div>   
        <div class="control-group">
            <label class="control-label" for="Address1">Address2:</label>
            <input type="text" class="controls" id="Address2" name="Address2" value="<?php echo $controller->org->Address2;?>"></input>
        </div>   
        <div class="control-group">
            <label class="control-label" for="City">City:</label>
            <input type="text" class="controls" id="City" name="City" value="<?php echo $controller->org->City;?>"></input>
        </div>
        <div class="control-group">
            <label class="control-label" for="Zip">Zip:</label>
            <input type="text" class="controls" id="Zip" name="Zip" value="<?php echo $controller->org->Zip;?>"></input>
        </div>
        <div class="control-group">
            <label class="control-label" for="Country">Country:</label>
            <input type="text" class="controls" id="Country" name="Country" value="<?php echo $controller->org->Country;?>"></input>
        </div>
        <input type="text" readonly="readonly" class="controls" id="OrgID" name="OrgID" value="<?php echo $controller->org->OrgID;?>"><br>
        <input type="text" readonly="readonly" class="controls" id="Subdomain" name="Subdomain" value="<?php echo $controller->org->Subdomain;?>"><br>
        <input type="text" readonly="readonly" class="controls" id="FQDN" name="FQDN" value="<?php echo $controller->org->FQDN;?>"><br>

        <button class="button large green " type="submit" name="Submit" value="Submit">Save Changes</button>


    </form>



</div>


<?php $controller->get_footer(); ?>


