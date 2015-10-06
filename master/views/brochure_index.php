<!doctype html>
<html lang="en">
    <!--[if lt IE 9 ]><html lang="en" class="ie8"><![endif]-->
    <!--[if (gt IE 8)|!(IE)]><!--><html lang="en"><!--<![endif]-->

        <head>
            <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
            <meta http-equiv="Cache-control" content="no-cache">
            <meta name="apple-mobile-web-app-capable" content="yes" />

            <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
            <!-- Optional theme -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">       
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha256-Sk3nkD6mLTMOF0EOpNtsIry+s1CsaqQC1rVLTAy+0yc= sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
            <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
            <script src="js/iframeResizer.min.js"></script>

        </head>        
        <style>

            .logo {
                max-width: 30%;
            }
            .spacer {
                margin-top: 5%;
            }

            .pretend-i-frame { 
                z-index: -1;
                width:40.5%;
                height:52%;
                position:absolute;
                margin-top: 0px;
                margin-left: 0px;
                left:31%;
                top:11%;
            }

            .hand {
                max-width: 100%;
                z-image: -1;
            }

            .bubble1 
            {
                position: relative;
                width: 350px;
                height: auto;
                padding: 10px;
                background: #DDDDDD !important;
                color:black !important;
                -webkit-border-radius: 10px;
                -moz-border-radius: 10px;
                -webkit-print-color-adjust: exact;
                border-radius: 10px;
            }

            .bubble1:after 
            {
                content: '';
                position: absolute;
                border-style: solid;
                border-width: 16px 59px 16px 0;
                border-color: transparent #DDDDDD;
                display: block;
                width: 0;
                z-index: 1;
                left: -59px;
                top: 38px;
            }

            .bubble1-text {
                color:white !important;
                font-weight: bolder;
            }

            .bubble2
            {
                position: relative;
                width: 280px;
                height: auto;
                padding: 0px;
                background: #DDDDDD !important;
                -webkit-border-radius: 10px;
                -moz-border-radius: 10px;
                border-radius: 10px;
            }

            .bubble2:after 
            {
                content: '';
                position: absolute;
                border-style: solid;
                border-width: 15px 0 15px 15px;
                border-color: transparent #DDDDDD !important;
                display: block;
                width: 0;
                z-index: 1;
                right: -15px;
                top: 135px;
            }            
            #codes-bubble {
                display:inline-block; 
            }


            @media print
            {    
                .no-print, .no-print *
                {
                    display: none !important;
                }
            }            


        </style>
        <body>

            <div class="container-fluid">
                <div class="page-header">
                    <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                            <img class="img-responsive logo" src='<?php echo $icon_url; ?>'>
                        </div>
                        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7">
                            <h2><?php echo $controller->currentClinicName; ?></h2>
                            <h3>Is your appointment delayed?</h3>
                            <h6>Register to get updates on your smartphone.</h6>
                        </div>

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4 col-xs-4 col-sm-4 col-lg-4">
                        <img class="hand" src="/images/emptyhand_1.png">
                        <img class="pretend-i-frame" src="/images/brochure_3.png">
                    </div>
                    <div class="col-md-6 col-xs-6 col-sm-6">
                        <div class="bubble1">We always try hard to see you on time.  However sometimes emergencies arise.  If you have a smartphone (or a computer) you now have a free, easy way to check if your appointment is delayed.  
                            If you need to make a long appointment, please tell your receptionist when making your booking.
                        </div>
                    </div> 
                </div>
                <div class="spacer"></div>
                <div class="row">
                    <div class="col-lg-4 col-lg-offset-4 col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-2 col-xs-6 col-xs-offset-2">
                        <div id="codes-bubble" class="bubble2"><h5>How? Go to<pre class="text-center"><h4>www.how-late.com</h4></pre>and go to the <em>PATIENTS</em> link.  <?php echo $entry_instruction; ?>:</h5>
                            <div class="row">

                                <?php foreach ($controller->pract as $key => $val) { ?>

                                    <div class="col-sm-12 col-md-12 col-lg-6 col-xs-12 code"><?php echo $val->OrgID . "." . $val->ID . " for " . $val->AbbrevName; ?></div>

                                <?php } ?>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 ">
                        <img class="pretend-i-frame" src="/images/brochure_2.png">
                        <img class="hand" src="/images/emptyhand_1.png">
                    </div>
                </div>    
                <div class="panel">
                    <div class="row">
                        <div class="col-lg-12 text-center"><button class="btn-primary no-print" onclick="window.print()"><div class="glyphicon glyphicon-print">&nbsp;</div>Print this page</button></div>

                    </div>
                </div>

            </div> <!-- here -->
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
            <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script>
            <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/additional-methods.min.js"></script>

            <script src="js/main.js"></script>
        </body>
    </html>
