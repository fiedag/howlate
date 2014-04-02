<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!-- Mobile Specific Meta -->
        <title>Update the lateness of doctors for this organisation.</title>

        <meta name='description' content='Change the lateness of doctors in your organisation.'>

        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
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
                    <div class="nav-mainlinks-container  font-on-custom-background">
                        <ul class="nav-mainlinks custom-background">

                            <li class="first active custom-background-dark-hover">
                                <span><a id="nav-home" class="custom-font-on-white" href="http://<?php echo __FQDN;?>/main">Main</a></span>
                            </li>
                            <li class="custom-background-dark-hover">
                                <span><a id="nav-people" class="font-on-custom-background" href="http://<?php echo __FQDN;?>/clinics">Clinics</a></span>
                            </li>
                            <li class="custom-background-dark-hover"><span><a id="nav-expenses" class="font-on-custom-background" href="http://<?php echo __FQDN;?>/practitioners">Practitioners</a></span>
                            </li>
                            <li class="custom-background-dark-hover">
                                <span>
                                    <a id="nav-reports" class="font-on-custom-background" href="https://fiedlerconsulting.freshbooks.com/viewReport">Reports</a>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="clearb"></div>
                </div>
            </div>
        </div>



