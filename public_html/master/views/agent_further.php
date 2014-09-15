<?php $controller->get_header(); ?>

<div class='container primary-content'>
    <h3>1. You should have a folder which has files in it like this:</h3>
    
    <img src="/images/further1.png">


    <h3>2. Double-click to run the svc_install.bat.  Then check your Windows Services.  You should have a new service like this:</h3>
    
    <img src="/images/further2.png">

    <h3>3. Change the startup type to Automatic and start the service.</h3>
    <img src="/images/further3.png">   
    
    <h3>4. Launch the Event Viewer to check what the Service is doing.</h3>
    <img src="/images/further4.png">   

    <h3>5. Check Windows Logs -> Application.</h3>
    <img src="/images/further5.png">   

    <h3>Troubleshooting</h3>
    <list>
    <li>If the agent cannot connect to the database or the details change, re-download the .config file with new details, or edit the .config file yourself with a text editor.</li>
    <li>If the doctor's lateness is not updating, ensure the FullName of the doctor on the how-late site is exactly the same as the Provider Name in the Best Practice system.</li>
    </list>
    
    
    

    
</div>

<?php $controller->get_footer(); ?>


