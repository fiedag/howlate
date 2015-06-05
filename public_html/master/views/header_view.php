<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!-- Mobile Specific Meta -->
        <title>How Late</title>
        <meta name='description' content='How Late is my appointment.'>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=1">
        <link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet">
        <link rel="stylesheet" href="/styles/modal.css">
        <link rel="stylesheet" href="/styles/guiders.css" type="text/css" />
        <link media="screen" href="/includes/xcrud/themes/default/xcrud.css" type="text/css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script type="text/javascript" src="/js/guiders.js"></script>
    </head>
    <body>
        <div id="navmain" class="fresh-header">
            <div class="container  relative-box">
                <!-- top links -->
                <ul class="nav-toplinks">
                    <li><a><?php echo (isset($_SESSION["USER"])) ? "User: " . $_SESSION["USER"] : ""; ?></a></li>
                    <li><a id="nav-log-out" href="https://<?php echo __FQDN; ?>/logout">Log out</a></li>
                </ul>
                <!-- end top links -->
                <div class="logo-exists">
                    <div id="logo-container" class="logo-container">
                        <img alt="" id="sysLogo" class="system-logo" 
                             title="How Late Admin System" src="<?php echo HowLate_Util::logoURL(__SUBDOMAIN); ?>" height="100" width="100">
                    </div>
                    <div class="orgname">
                        <?php echo $controller->org->OrgName; ?>
                        
                    </div>
                    <div class="nav-mainlinks-container  font-on-custom-background">
                        <ul class="nav-mainlinks custom-background" id="nav-mainlinks">
                            <li class="first <?php
                            if (get_class($controller) == "MainController") {
                                echo 'active';
                            }
                            ?> custom-background-dark-hover">
                                <span><a id="nav-main" title="Look up and change lateness manually, and invite mobile devices." class="<?php echo (get_class($controller) == "MainController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/main">Main</a></span>
                            </li>
                            <li class="<?php
                            if (get_class($controller) == "DevicesController") {
                                echo 'active';
                            }
                            ?> custom-background-dark-hover">
                                <span><a id="nav-devices" title="Check the devices (mobile phones) which have been registered for updates for various practitioners." class="<?php echo (get_class($controller) == "DevicesController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/devices">Devices</a></span>
                            </li>
                            <li class="<?php
                                if (get_class($controller) == "PractController") {
                                    echo 'active';
                                }
                            ?> custom-background-dark-hover">
                                <span><a id="nav-practitioners" title="Set up your practitioners and assign them to a clinic." class="<?php echo (get_class($controller) == "PractController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/pract">Practitioners</a></span>
                            </li>
                            <li class="<?php
                                if (get_class($controller) == "ClinicsController") {
                                    echo 'active';
                                }
                                ?> custom-background-dark-hover">
                                <span><a id="nav-clinics" title="Set up clinics for your organization." class="<?php echo (get_class($controller) == "ClinicsController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/clinics">Clinics</a></span>
                            </li>
                            <li class="<?php
                                if (get_class($controller) == "OrgController") {
                                    echo 'active';
                                }
                                ?> custom-background-dark-hover">
                            <span><a id="nav-organisation" title="Set up your organization details." class="<?php echo (get_class($controller) == "OrgController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/org">Organization</a></span>
                                </li>
                                <li class="<?php
                                if (get_class($controller) == "UsersController") {
                                    echo 'active';
                                }
                                ?> custom-background-dark-hover">
                                    <span><a id="nav-users" title="Set up users and reset passwords." class="<?php echo (get_class($controller) == "UsersController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/users">Users</a></span>
                                </li>
                                <li class="<?php
                                if ( in_array(get_class($controller), array('AgentController','SessionsController','ApptTypeController','ApptStatusController') )) {
                                    echo 'active';
                                }
                                ?> custom-background-dark-hover">
                                    <span><a id="nav-agent" title="Setup parameters for Agents which integrate with your Practice Management system." class="<?php echo ( in_array(get_class($controller) , array("AgentController",'SessionsController','ApptTypeController','ApptStatusController'))) ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/agent">Integration</a></span>
                                </li>                            
                                <li class="<?php
                                if (get_class($controller) == "TranLogController") {
                                    echo 'active';
                                }
                                ?> custom-background-dark-hover">
                                    <span><a id="nav-activity" title="Review, search or download one week's activity log." class="<?php echo (get_class($controller) == "TranLogController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/tranlog">Activity Log</a></span>
                                </li>
                        </ul>
                    </div>
                    <div class="clearb"></div>
                </div>
            </div>
        </div>
        
<?php include_once('views/header_guiders.php'); ?>
        

        
        