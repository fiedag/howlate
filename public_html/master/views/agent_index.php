
<?php $controller->get_header(); ?>


<div class='container primary-content'>
    <h3>1. Download the executable and place in e.g. C:\Program Files\HowLateAgent\ folder of your Best Practice Server.</h3>
    <div class="xcrud-nav">
        <a href="/agent/exe"><button class="xcrud-button xcrud-cyan">Download HowLateAgent.exe</button></a>
    </div>
    <h3>2. Please supply the connection information to be written to the config file</h3>
    <form id="org" name="org" method="post" action="/agent/update">
        <div class="control-group">
            <label class="control-label" for="Clinic">Clinic:</label>
            <select class="clinic-dropdown" name="Clinic" id="Clinic" class="dropdown" value="<?echo $controller->currentClinicName; ?>"><?php $controller->get_clinic_options(); ?>
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
            <input type="text" class="controls" id="PWD" name="PWD" size="25" value="123456"></input>
        </div>
    <h3>3. Download the config file and place in the same folder as the exe</h3>
        <div class="xcrud-nav">
            <button class="xcrud-button xcrud-cyan " type="submit" name="Submit" value="Submit">Download HowLateAgent.exe.config</button>
        </div>
        <input type="hidden" readonly="readonly" class="controls" id="OrgID" name="OrgID" value="<?php echo $controller->org->OrgID;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="Subdomain" name="Subdomain" value="<?php echo $controller->org->Subdomain;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="FQDN" name="FQDN" value="<?php echo $controller->org->FQDN;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="UpdIndic" name="UpdIndic" value="<?php echo $controller->org->UpdIndic;?>"><br>
    </form>

    <h3>4. Download the script to make this exe a Windows Service.  Place in the same folder as the other files.</h3>
    <div class="xcrud-nav">
        <a href="/agent/install"><button class="xcrud-button xcrud-cyan ">Download Script</button></a>
    </div>
    <h3>5. Click below for further instructions.</h3>
    <div class="xcrud-nav">
        <a href="/agent/further"><button class="xcrud-button xcrud-cyan ">Further Instructions</button></a>
    </div>
    
</div>


<?php $controller->get_footer(); ?>


