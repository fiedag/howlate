

<div class='container primary-content'>
    <h3>1. Download the executable and place in e.g. C:\Program Files\HowLate\ folder of your EHR <i>Database</i> Server.</h3>
    <div class="xcrud-nav">
        <a href="/agent/exe"><button class="xcrud-button xcrud-cyan">Download HowLateAgent.exe</button></a>
    </div>
    <h3>2. Please supply the connection information to be written to the config file.  If your system does not appear in the list, contact us to have it added!</h3>

    <form id="clinicselect" name="clinicselect" method="post" action="/agent/clinicselect">
        <div class="control-group">
            <label class="control-label" for="ClinicID">Clinic:</label>
            <select class="clinic-dropdown" name="ClinicID" id="ClinicID" class="dropdown" onchange="this.form.submit();"><?php $controller->get_clinic_options($ClinicID); ?>
            </select>
        </div>

    </form>

    
    <form id="org" name="org" method="post" action="/agent/update">
        <div class="control-group">
            <label class="control-label" for="PMSystem">System:</label>
            <select class="clinic-dropdown" name="PMSystem" id="PMSystem" class="dropdown" ><?php $controller->get_system_options($PMSystem); ?>
            </select>
        </div>
        <div class="control-group">
            <label class="control-label" for="ConnectionType">Connection Type:</label>
            <select class="clinic-dropdown" name="ConnectionType" id="ConnectionType" class="dropdown" >
                <?php $controller->get_connection_options($ConnectionType); ?>
            </select>
        </div> 
        <div class="control-group">
            <label class="control-label" for="ConnectionString">Connection String:</label>
            <textarea class="controls" id="ConnectionString" name="ConnectionString" rows="4" cols="150" form="org"><?php echo $ConnectionString;?></textarea>
        </div> 
        <div class="control-group">
            <label class="control-label" for="ConnectionStringExample"><i>Example:</i></label>
            <textarea class="controls" id="ConnectionStringExample" name="ConnectionStringExample" rows="4" cols="150" readonly></textarea>

        </div> 
        <div class="control-group">
            <label class="control-label" for="PollInterval">Poll Interval (seconds):</label>
            <input type="number" class="controls" id="Interval" name="PollInterval" size="5" min="60" max="600" value="<?php echo $PollInterval;?>"></input>
        </div>  
        <div class="control-group">
            <label class="control-label" for="HLUserID">How Late UserID:</label>
            <select class="clinic-dropdown" name="HLUserID" id="HLUserID" class="dropdown" ><?php $controller->get_user_options($HLUserID); ?>
            </select>
        </div>  
    <h3>3. Download the config file and place in the same folder as the exe</h3>
        <div>
            <button type="submit" class="xcrud-button xcrud-cyan ">(SAVE) Download HowLateAgent.exe.config</button>
        </div>
        <input type="hidden" readonly="readonly" class="controls" id="OrgID" name="OrgID" value="<?php echo $controller->Organisation->OrgID;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="ClinicID" name="ClinicID" value="<?php echo $controller->currentClinic;?>"><br>
        <input type="hidden" readonly="readonly" class="controls" id="Subdomain" name="Subdomain" value="<?php echo $controller->Organisation->Subdomain;?>"><br>
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

<script>

$( document ).ready(function() {
    $("#ConnectionStringExample").html(exampleConnStr($("#ConnectionType").val())).css("background-color","#fafafa");
});


$("#ConnectionType").change(
        function() {
                $("#ConnectionStringExample").html(exampleConnStr($(this).val()));
        }
);

function exampleConnStr($connType) {
    if ($connType == "ODBC DSN") {
        return "DSN=systemdsn_here;UID=userid_here;PWD=passwordhere";
    }
    else
        return "Server=hostname_here\\instancename_here;Database=dbname_here;UID=username_here;PWD=password_here";
}


</script>
    

<?php $controller->get_footer(); ?>


