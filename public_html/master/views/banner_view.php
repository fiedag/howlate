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
                <div class="logo-exists">
                    <div id="logo-container" class="logo-container">
                        <img alt="" id="sysLogo" class="system-logo" 
                             title="How Late Admin System" src="<?php echo howlate_util::logoURL(__SUBDOMAIN); ?>" height="100" width="100">
                    </div>
                    <div class="orgname">
                        <?php echo $controller->org->OrgName; ?>
                        
                    </div>
                    <div class="clearb"></div>
                </div>
            </div>
        </div>
        