<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!-- Mobile Specific Meta -->
        <title>How Late</title>
        <meta name='description' content='How Late is my appointment.'>
        <link media="screen" href="/includes/xcrud/themes/default/xcrud.css" type="text/css" rel="stylesheet">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">

        <style>


            .footer{clear:both;height:130px;padding-top:30px;color:#999}
            .footer {clear:both;width:760px;position:relative}

            .footer h4,.footer li,.footer a:link,.footer a:visited{color:#999;text-decoration:none}
            .footer ul,.footer li{list-style:none;margin:0;padding:0}
            .footer-content .title{margin-bottom:10px}
            .footer-content .title,.footer-content a{color:#999;font-weight:normal;text-decoration:none;display:block}
            .footer-content .title,.footer h4{font-size:12px;margin-bottom:6px;font-weight:bold}
            .footer a:hover{background:0;color:#fff;background:#999}
            .footer-content a:hover{text-decoration:none}
            .footer-content:hover .title,.footer-content:hover a{color:#000;background:0}
            .footer-content p:last-child{margin:0}
            .footer-content:hover .linkish{color:#00f}
            .footer-content:hover .linkish:hover{background:#00f;color:#fff;text-decoration:none}
            #footer-magic{position:absolute;left:0;top:-48px}
            .footer-border{border-top:1px solid #ccc;padding-top:15px}

        </style>

    </head>
    <body>
        <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src="/js/jquery.jeditable.min.js"></script>

        <div class="container">
            <!-- top links -->

            <div class="container">
                <ul class="list-inline pull-right">
                    <li><?php echo (isset($_SESSION["USER"])) ? "User: " . $_SESSION["USER"] : ""; ?></li>
                    <li><a href="/logout">Log out</a></li>
                </ul>
            </div>
            <!-- end top links -->


            <div class="container">

                <img title="How Late Admin System" src="<?php echo HowLate_Util::logoURL(__SUBDOMAIN); ?>">


                <span class="center" style="font-size:48px"><?php echo $controller->Organisation->OrgName; ?></span>

            </div>

            <div class="navbar">
                <ul class="nav nav-tabs">
                    <li class="first <?php if (get_class($controller) == "OrgController") {echo 'active';} ?> ">
                        <a id="nav-main" title="org table in howlate_main db." href="/org">Orgs</a>
                    </li>
                    <li class="first <?php if (get_class($controller) == "ClinController") {echo 'active';} ?> ">
                        <a id="nav-main" title="clinics table in howlate_main db." href="/clin">Clinics</a>
                    </li>
                    <li class="first <?php if (get_class($controller) == "DevicesController") {echo 'active';} ?> ">
                        <a id="nav-main" title="devices table in howlate_main db." href="/devices">Devices</a>
                    </li>
                    <li class="first <?php if (get_class($controller) == "PmSystemsController") {echo 'active';} ?> ">
                        <a id="nav-main" title="pmsystems table in howlate_main db." href="/pmsystems">PM Systems</a>
                    </li>
                    <li class="first <?php if (get_class($controller) == "UsersController") {echo 'active';} ?> ">
                        <a id="nav-main" title="users table in howlate_main db." href="/users">Users</a>
                    </li>
                    <li class="first <?php if (get_class($controller) == "TranLogController") {echo 'active';} ?> ">
                        <a id="nav-main" title="tranlog table in howlate_main db." href="/tranlog">TranLog</a>
                    </li>
                    <li class="first <?php if (get_class($controller) == "AnalyticsController") {echo 'active';} ?> ">
                        <a id="nav-main" title="Analytics submenu." href="/analytics">Analytics</a>
                    </li>
                    <li class="first <?php if (get_class($controller) == "BillingController") {echo 'active';} ?> ">
                        <a id="nav-main" title="Billing submenu." href="/billing">Billing</a>
                    </li>
                    <li class="first <?php if (get_class($controller) == "TestingController") {echo 'active';} ?> ">
                        <a id="nav-main" title="Testing submenu." href="/testing">Testing</a>
                    </li>

                </ul>

            </div>
            <div class="clearb"></div>

        </div>


