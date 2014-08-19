REM This script installs the executable as a service.  
REM The encoding of this file should be ASCII not unicode
REM Created: Alex Fiedler 21 July 2014
REM 
sc create HowLateAgent start= demand  error= normal binpath= "%CD%\HowLateAgent.exe" displayname= "How-Late Agent for BestPractice"
