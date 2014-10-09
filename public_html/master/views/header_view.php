<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!-- Mobile Specific Meta -->
        <title>How Late</title>
        <meta name='description' content='How Late is my appointment.'>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet">
        <link media="screen" href="/includes/xcrud/themes/default/xcrud.css" type="text/css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		
    </head>
    <body>
	
        <div id="navmain" class="fresh-header">

            <div class="container  relative-box">
                <!-- top links -->
                <ul class="nav-toplinks">
                    <li><a><?php echo (isset($_SESSION["USER"]))?"User: " . $_SESSION["USER"]:"";?></a></li>
                    <li><a id="nav-log-out" href="https://<?php echo __FQDN;?>/logout">Log out</a></li>
                </ul>
                <!-- end top links -->

                <div class="logo-exists">
                    <div class="logo-container">
                        <img alt="" id="sysLogo" class="system-logo" 
                             title="<?php if (isset($usercookie)) {echo "User cookie $usercookie .";} if (isset($orgidcookie)) {echo "  OrgID cookie $orgidcookie";} ?>" 
                             src="<?php echo howlate_util::logoURL(__SUBDOMAIN); ?>" height="100" width="100">
                        <a href="#" class="logo-upload-button inverse" id="nav-upload-logo" style="display: none;">Upload your logo</a>
                    </div>
					<div class="orgname">
						<?php echo $org->OrgName; ?>
					</div>
                    <div class="nav-mainlinks-container  font-on-custom-background">
                        <ul class="nav-mainlinks custom-background" id="nav-mainlinks">
                            <li class="first <?php if (get_class($controller) == "mainController") {echo 'active';}?> custom-background-dark-hover">
                                <span><a id="nav-main" title="Look up and change lateness manually, and invite mobile devices." class="<?php echo (get_class($controller) == "mainController")?'custom-font-on-white':'font-on-custom-background';?>" href="https://<?php echo __FQDN;?>/main">Main</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "devicesController") {echo 'active';}?> custom-background-dark-hover">
								<span><a id="nav-devices" title="Check the devices (mobile phones) which have been registered for updates for various practitioners." class="<?php echo (get_class($controller) == "devicesController")?'custom-font-on-white':'font-on-custom-background';?>" href="https://<?php echo __FQDN;?>/devices">Devices</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "practController") {echo 'active';}?> custom-background-dark-hover">
								<span><a id="nav-practitioners" title="Set up your practitioners and assign them to a clinic." class="<?php echo (get_class($controller) == "practController")?'custom-font-on-white':'font-on-custom-background';?>" href="https://<?php echo __FQDN;?>/pract">Practitioners</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "clinicsController")	 {echo 'active';}?> custom-background-dark-hover">
                                <span><a id="nav-clinics" title="Set up clinics for your organization." class="<?php echo (get_class($controller) == "clinicsController")?'custom-font-on-white':'font-on-custom-background';?>" href="https://<?php echo __FQDN;?>/clinics">Clinics</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "orgController") {echo 'active';}?> custom-background-dark-hover">
								<span><a id="nav-organisation" title="Set up your organization details." class="<?php echo (get_class($controller) == "orgController")?'custom-font-on-white':'font-on-custom-background';?>" href="https://<?php echo __FQDN;?>/org">Organization</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "usersController") {echo 'active';}?> custom-background-dark-hover">
								<span><a id="nav-users" title="Set up users and reset passwords." class="<?php echo (get_class($controller) == "usersController")?'custom-font-on-white':'font-on-custom-background';?>" href="https://<?php echo __FQDN;?>/users">Users</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "agentController") {echo 'active';}?> custom-background-dark-hover">
								<span><a id="nav-agent" title="Setup parameters for Agents which integrate with your Practice Management system." class="<?php echo (get_class($controller) == "agentController")?'custom-font-on-white':'font-on-custom-background';?>" href="https://<?php echo __FQDN;?>/bps_agent">Integration</a></span>
                            </li>                            
                            <li class="<?php if (get_class($controller) == "tranlogController") {echo 'active';}?> custom-background-dark-hover">
								<span><a id="nav-agent" title="Review, search or download one week's activity log." class="<?php echo (get_class($controller) == "tranlogController")?'custom-font-on-white':'font-on-custom-background';?>" href="https://<?php echo __FQDN;?>/tranlog">Activity Log</a></span>
                            </li>                            
                        </ul>
                    </div>
                    <div class="clearb"></div>
                </div>
            </div>
        </div>



