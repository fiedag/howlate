<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!-- Mobile Specific Meta -->
        <title>Update the lateness of doctors for this organisation.</title>

        <meta name='description' content='Change the lateness of doctors in your organisation.'>

        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <link media="screen" href="/styles/howlate_branding.css" type="text/css" rel="stylesheet">
        <link media="screen" href="/styles/howlate_base.css" type="text/css" rel="stylesheet">
        <link media="screen" href="/styles/howlate_extra.css" type="text/css" rel="stylesheet">
        <link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">        
		
    </head>
    <body>
	
        <div id="navmain" class="fresh-header">

            <div class="container  relative-box">
                <!-- top links -->
                <ul class="nav-toplinks">
                    <li class="">		<a id="nav-my-account" class="" href="http://<?php echo __FQDN;?>/account">My Account</a>
                    </li>
                    <li class="">		<a id="nav-settings" class="" href="http://<?php echo __FQDN;?>/settings">Settings</a>
                    </li>
                    <li class="">		<a id="nav-help" class="" href="http://<?php echo __FQDN;?>/needHelp">Help</a>
                    </li>
                    <li class="last">		<a id="nav-log-out" class="" href="http://<?php echo __FQDN;?>/logout">Log out</a>
                    </li>
                </ul>
                <!-- end top links -->

                <div class="logo-exists">
                    <div class="logo-container">
                        <img alt="" id="sysLogo" class="system-logo" src="/pri/<?php echo $org->Subdomain; ?>/logo.png" height="100" width="100">
                        <a href="#" class="logo-upload-button inverse" id="nav-upload-logo" style="display: none;">Upload your logo</a>
                    </div>
					<div class="orgname">
						<?php echo $org->OrgName; ?>
					</div>
                    <div class="nav-mainlinks-container  font-on-custom-background">
                        <ul class="nav-mainlinks custom-background" id="nav-mainlinks">
                            <li class="first <?php if (get_class($controller) == "mainController") {echo 'active';}?> custom-background-dark-hover">
                                <span><a id="nav-main" class="<?php echo (get_class($controller) == "mainController")?'custom-font-on-white':'font-on-custom-background';?>" href="http://<?php echo __FQDN;?>/main">Main</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "clinicsController")	 {echo 'active';}?> custom-background-dark-hover">
                                <span><a id="nav-clinics" class="<?php echo (get_class($controller) == "clinicsController")?'custom-font-on-white':'font-on-custom-background';?>" href="http://<?php echo __FQDN;?>/clinics">Clinics</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "practController") {echo 'active';}?> custom-background-dark-hover">
								<span><a id="nav-practitioners" class="<?php echo (get_class($controller) == "practController")?'custom-font-on-white':'font-on-custom-background';?>" href="http://<?php echo __FQDN;?>/pract">Practitioners</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "orgController") {echo 'active';}?> custom-background-dark-hover">
								<span><a id="nav-organisation" class="<?php echo (get_class($controller) == "orgController")?'custom-font-on-white':'font-on-custom-background';?>" href="http://<?php echo __FQDN;?>/org">Organization</a></span>
                            </li>
                        </ul>
                    </div>
                    <div class="clearb"></div>
                </div>
            </div>
        </div>



