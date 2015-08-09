<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!-- Mobile Specific Meta -->
        <title>How Late Admin Panel</title>
        <meta name='description' content='How Late is my appointment.'>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=1">
        <link media="screen" href="/styles/howlate_admin.css" type="text/css" rel="stylesheet">
        <link rel="stylesheet" href="/styles/modal.css">
        <link media="screen" href="/includes/xcrud/themes/default/xcrud.css" type="text/css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
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
                            <li class="first <?php if (get_class($controller) == "OrgController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-main" title="Check on organisations." class="<?php echo (get_class($controller) == "OrgController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/org">Orgs</a></span>
                            </li>

                            <li class="first <?php if (get_class($controller) == "ClinController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-clinics" title="Check on clinics." class="<?php echo (get_class($controller) == "ClinController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/clin">Clinics</a></span>
                            </li>
                            
                            <li class="first <?php if (get_class($controller) == "ClinIntController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-clinics" title="Check on clinic integration." class="<?php echo (get_class($controller) == "ClinIntController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/clinint">Clin Int</a></span>
                            </li>

                            <li class="<?php if (get_class($controller) == "DevicesController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-devices" title="Check the devices (mobile phones) which have been registered for updates for various practitioners." class="<?php echo (get_class($controller) == "DevicesController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/devices">Devices</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "PmSystemsController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-integrations" title="Create and change integrations (Practice Mgt Systems)." class="<?php echo (get_class($controller) == "PmSystemsController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/pmsystems">PM Systems</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "UsersController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-users" title="Set up users and reset passwords." class="<?php echo (get_class($controller) == "UsersController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/users">Users</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "TranLogController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-agent" title="Activity log." class="<?php echo (get_class($controller) == "TranLogController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/tranlog">Activity Log</a></span>
                            </li>
                             <li class="<?php if (get_class($controller) == "AnalyticsController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-agent" title="Analytics." class="<?php echo (get_class($controller) == "AnalyticsController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/analytics">Analytics</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "BillingController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-agent" title="Billing details." class="<?php echo (get_class($controller) == "BillingController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/billing">Billing</a></span>
                            </li>
                            <li class="<?php if (get_class($controller) == "TestingController") {
                            echo 'active';
                        } ?> custom-background-dark-hover">
                                <span><a id="nav-agent" title="Tests." class="<?php echo (get_class($controller) == "TestingController") ? 'custom-font-on-white' : 'font-on-custom-background'; ?>" href="https://<?php echo __FQDN; ?>/testing">Testing</a></span>
                            </li>
                        </ul>
                    </div>
                    <div class="clearb"></div>
                </div>
            </div>
        </div>



