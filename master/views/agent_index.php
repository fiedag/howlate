
<?php $controller->get_header(); ?>
<?php $controller->get_submenu(); ?>

<div class='container'>
    <!-- panel 1 -->
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">Agent Executable Program
            </div>
        </div>
        <div class="panel-body">
            1. Download the executable and place in e.g. <code>C:\Program Files\HowLate\HowLateAgent.exe</code> of your <i>Database</i> Server.
            <br><br>
            <a href="/agent/exe"><button class="btn btn-primary center-block"><span class="glyphicon glyphicon-download" aria-hidden="true"></span> Download</button></a>
        </div>

    </div>

    <!-- panel 2 -->
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">Agent Config File
            </div>
        </div>
        <div class="panel-body">
            2. Configure and download the HowLateAgent.exe.config file and place alongside the HowLateAgent.exe file from the previous step.

            <form id="clinicselect" name="clinicselect" method="post" action="/agent/clinicselect">
                <div class="input-group">
                    <label for="ClinicID">Clinic</label>
                    <select class="form-control" name="ClinicID" id="ClinicID" onchange="this.form.submit();"><?php $controller->get_clinic_options($ClinicID); ?></select>
                </div>
            </form>

            <form id="org" name="org" method="post" action="/agent/update">
                <div class="input-group">
                    <label for="PMSystem">System</label>
                    <select class="form-control" name="PMSystem" id="PMSystem"><?php $controller->get_system_options($PMSystem); ?></select>
                </div>
                <div class="input-group">
                    <label for="ConnectionType">Connection Type</label>
                    <select class="form-control" name="ConnectionType" id="ConnectionType" ><?php $controller->get_connection_options($ConnectionType); ?></select>
                </div> 
                <div class="input-group">
                    <label for="ConnectionString">Connection String</label>
                    <textarea class="form-control" id="ConnectionString" name="ConnectionString" rows="4" cols="150" form="org"><?php echo $ConnectionString; ?></textarea>
                </div> 
                <div class="input-group">
                    <label for="ConnectionStringExample"><i>Example of connection string:</i></label>
                    <code id="ConnectionStringExample" name="ConnectionStringExample" readonly>Whatever this says i guess is sized to fit</code>
                </div>
                <div class="input-group">
                    <label for="PollInterval">Poll Interval (seconds)</label>
                    <input type="number" class="controls" id="Interval" name="PollInterval" size="50" min="60" max="600" value="<?php echo $PollInterval; ?>"></input>
                </div>  
                <div class="input-group">
                    <label for="HLUserID">How Late UserID</label>
                    <select class="form-control" name="HLUserID" id="HLUserID" ><?php $controller->get_user_options($HLUserID); ?></select>
                </div>  
                <br><br>

                <div class="text-center">
                    <button type="submit" name="action" value="save" class="btn btn-primary"><span class="glyphicon glyphicon-save" aria-hidden="true"></span> Save</button>
                    <button type="submit" name="action" value="download" class="btn btn-primary"><span class="glyphicon glyphicon-download" aria-hidden="true"></span> Download</button>
                </div>
                <input type="hidden" readonly="readonly" class="controls" id="OrgID" name="OrgID" value="<?php echo $controller->Organisation->OrgID; ?>">
                <input type="hidden" readonly="readonly" class="controls" id="ClinicID" name="ClinicID" value="<?php echo $controller->currentClinic; ?>">
                <input type="hidden" readonly="readonly" class="controls" id="Subdomain" name="Subdomain" value="<?php echo $controller->Organisation->Subdomain; ?>">
            </form>
        </div>
    </div>
    <!-- panel 3 -->
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">Windows Service</div>
        </div>
        <div class="panel-body">
            Download the script to make this exe a Windows Service.  Place in the same folder as the other files.
            <br>
            <br>
            <div class="text-center">
                <a href="/agent/install"><button class="btn btn-primary"><span class="glyphicon glyphicon-save" aria-hidden="true"></span> Download</button></a>
            </div>
        </div>
    </div>


    <div class="xcrud-nav">
        <a class="pull-right" href="/agent/further">Click next for further instructions: <button class="btn btn-default pull-right ">Next...</button></a>
    </div>

</div>

<script>

    $(document).ready(function() {
        $("#ConnectionStringExample").html(exampleConnStr($("#ConnectionType").val())).css("background-color", "#fafafa");
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


<div id="helpbubble_holder"></div>


<script src="/js/libs/Ractive.min.js"></script>
<script src="/js/libs/lodash.js"></script>
<script src="/js/helpbubbles.min.js"></script>
<script src="/js/helpbubbles.fix.js"></script>

<script>
    var bubblecious;
    bubblecious = new window.HelpBubbles({
        el: 'helpbubble_holder',
        data: {
            y_adjustment: 20,
            bubbles: [
                {
                    target: 'nav-sub-agent',
                    content: 'The Agent runs on your practice management database server to update the how-late system.  Configure the connection information then download and install it.',
                },
                {
                    target: 'nav-sub-sessions',
                    content: 'Session start and end times from your clinical system are uploaded here so lateness can be timed out at the end of a session.',
                },
                {
                    target: 'nav-sub-appttype',
                    content: 'Appointment types from your clinical system are uploaded here by the agent.  Configure which ones can be used to catch up lost time or require no consultation (auto-complete).',
                },
                 {
                    target: 'nav-sub-apptstatus',
                    content: 'Appointment status codes from your clinical system are uploaded here by the agent.  Configure which ones can be used to catch up lost time or require no consultation (auto-complete).',
                    onTap: function () {
                        console.log('Alt Bubble-tapped');
                    }
                }
            ]
        }
    });



</script>



<?php $controller->get_footer(); ?>


