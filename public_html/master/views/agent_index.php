
<?php $controller->get_header(); ?>


<div class='container primary-content'>

    <form id="org" name="org" method="post" action="/agent/update">

            

        <div class="control-group">
            <label class="control-label" for="Clinic">Clinic:</label>
            <select class="" name="Clinic" id="Clinic" class="dropdown" value="<?echo $controller->currentClinicName; ?>"><?php $controller->get_clinic_options(); ?>
            </select>
        </div>
        <div class="control-group">
            <label class="control-label" for="City">Instance:</label>
            <input type="text" class="controls" id="SQL Instance" name="Instance" size="25" value=".\BPSINSTANCE"></input>
        </div>
        <div class="control-group">
            <label class="control-label" for="Database">Database:</label>
            <input type="text" class="controls" id="Database" name="Database" size="7" value="BPSPatients"></input>
        </div>
        <div class="control-group">
            <label class="control-label" for="UID">UserID:</label>
            <input type="text" class="controls" id="UID" name="UID" size="25" value="BPSViewer"></input>
        </div>
        <div class="control-group">
            <label class="control-label" for="PWD">Password:</label>
            <input type="password" class="controls" id="PWD" name="PWD" size="25" value="123456"></input>
        </div>
        <input type="hidden" readonly="readonly" class="controls" id="OrgID" name="OrgID" value="<?php echo $controller->org->OrgID;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="Subdomain" name="Subdomain" value="<?php echo $controller->org->Subdomain;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="FQDN" name="FQDN" value="<?php echo $controller->org->FQDN;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="UpdIndic" name="UpdIndic" value="<?php echo $controller->org->UpdIndic;?>"><br>
        <div class="xcrud-nav">
            <button class="xcrud-button xcrud-cyan " type="submit" name="Submit" value="Submit">Save Changes</button>
        </div>
        

    </form>

    <a href="/agent/exe">Download Agent EXE</a>

</div>


<?php $controller->get_footer(); ?>

