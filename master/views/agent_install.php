<?php
header('Content-type: text/plain');
header('Content-disposition: attachment; filename=svc_install.bat');
?>REM This script installs the executable as a service.  
REM The encoding of this file should be ASCII not unicode
REM Created: Alex Fiedler 21 July 2014
REM 
pushd %cd%
sc create HowLateAgent start= auto  error= normal binpath= "%CD%\HowLateAgent.exe" displayname= "How-Late Agent for <?php echo $interface; ?>"
sc description HowLateAgent "Update Lateness information based on local <?php echo $interface; ?> database"
net start HowLateAgent
popd

