<!doctype html>
<!--[if lt IE 9 ]><html lang="en" class="ie8"><![endif]-->
<!--[if (gt IE 8)|!(IE)]><!--><html lang="en"><!--<![endif]-->
    <head>
        <title>Doctor Running Late App</title>
        <meta name="google-site-verification" content="84C-kFLmCZgIPgtYPCa-wbW5Mcb2XIZaqlvtK6fkBq0" />
        <meta name="twitter:card" content="photo">
        <meta name="twitter:image" content="/master/images/twitter-image.jpg">
        <meta name="twitter:title" content="How-Late Software">
        <meta name="twitter:domain" content="how-late.com">
        <meta name="twitter:site" content="@HowLateIsMyAppt">
        <meta name="twitter:creator" content="@HowLateIsMyAppt">
        <meta name="twitter:image:width" content="475">
        <meta name="twitter:image:height" content="323">
        <meta name="fb:admins" content="HowLateIsMyAppointment">
        <meta name="fb:app_id" content="466443880106725">
        <meta name="og:title" content="How-Late Software">
        <meta name="og:type" content="website">
        <meta name="og:image" content="/master/images/gallery2.jpg" itemprop="red lock 991x660">
        <meta name="og:url" content="http://www.how-late.com/index.php">
        <meta name="og:description" content="Arrive late, but in good time.">
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="How-Late Software allows doctors to tell their patients if their appointment is running late.  Allows patients to see if their doctors appointment is running late.">
        <meta name="keywords" content="doctor running late app, how late, medical practitioner, patient, appointment, booking, cloud, software">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <link rel="apple-touch-icon-precomposed" href="master/images/apple-touch-icon.png" />
        <link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.1/css/bootstrap.min.css" rel="stylesheet" />
        <link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.1/css/bootstrap-responsive.min.css" rel="stylesheet">
        <link href="master/styles/howlate-brochure.css" rel="stylesheet" />
        <!-- test -->
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/css3pie/1.0.0/PIE.js"></script>
        <![endif]-->
    </head>
    <body>
        <div id="site-container">
            <!-- Begin Header / Reserve Flow -->

            <header class="navbar navbar-fixed-top">
                <div class="navbar-inner">
                    <div class="reserve-window">
                        <div class="container-fluid">
                            <div class="row-fluid">
                                <div class="span12">
                                    <div class="container">
                                        <div class="row">

                                            <div class="span5">
                                                <div class="logo"></div>
                                            </div>
                                            <div class="span3 offset3">
                                                <div class="reserve-circle">
                                                    <div class="reserve-circle-inner circle">
                                                        <p>Sign Up<span class="mobile-disable-small">&nbsp;NOW</span></p>
                                                        <div class="reserve-cancel" data-gtm-event="orderNow" data-gtm-info="Close"></div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div id="signup_div" class="row">
                                        <div class="span3 offset1">
                                            <div class="reserve-welcome fade in">
                                                
                                                <hgroup>
                                                    <h1>Sign up for</h1>
                                                    <h1>How Late Cloud.</h1>
                                                </hgroup>
                                                <p id="start_trial" class="start-trial">Start your free trial.  Enter your preferred domain and email address to begin.</p>
                                                <div class="" style="display:inline-box-align">
                                                    <input id="domain_input" type="text" class="input-block-level" required placeholder="e.g. xyzclinic" />
                                                    <span class='form-label'><b>&nbsp;.how&#8209;late.com</b></span>
                                                    <br><input id="email_input" type="email" class="input-block-level" required placeholder="email" />
                                                    &nbsp;&nbsp;
                                                    <span class="form-label">
                                                        &nbsp;
                                                        <span id="signup_link" title="Submit" onclick="signupFunction();">Try it free for 30 days</span>
                                                    </span><p class="error"></p>

                                                </div>
                                            </div>
                                        </div>
                                      <div class="span3" style="width:400px;height:400px;">
                                        <div id="loader_container" class="load-container" style="display:none;">
                                            <div id="loader" class="loader"></div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid reserve-window-hide-on-open">
                        <div class="row-fluid">
                            <div class="span12">
                                <div id="outercontainer" class="container">
                                    <div id="hereitis" class="row">
                                        <div class="span2 brand-wrapper">
                                            <a class="brand" title="how late" href="/"><div id="logo-howlate" class="logo"></div></a>
                                        </div>
                                        <div class="span3 offset6">
                                            <div class="header-circle">
                                                <div class="header-circle-inner">
                                                    <a href="#" id="reserve_link" title="Sign up Now" data-gtm-event="orderNow" data-gtm-info="Open">SIGN UP<span class="mobile-disable-small">&nbsp;NOW</span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div style="padding: 10px;" class="span1 mobile-disable">
                                            <div class="fb-like" data-href="http://how-late.com" data-send="false" data-layout="button_count" data-width="450" data-show-faces="true" data-font="arial" data-colorscheme="dark"></div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- End Header / Reserve Flow -->
            <section id="welcome">
                <div class="container-fluid">
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="container">
                                <div class="row">
                                    <div class="span6">
                                        <div class="intro">
                                            <hgroup>
                                                <h1>Come on in.</h1>
                                                <h3>The smart way to keep patients informed of appointment start times.</h3>
                                            </hgroup>
                                            <br>
                                            <hgroup>
                                                <h3><img src="master/images/checkmark-green.png" />  Increase efficiency of reception staff.</h3>
                                                <h3><img src="master/images/checkmark-green.png" />  Loved by patients!</h3>
                                                <br>
                                            </hgroup>

                                            <!-- a 
                                                href="http://www.youtube.com/embed/cf9DRZLEVTM_hide?rel=0&autoplay=1&enablejsapi=1" class="video-link">
                                                <div class="video-player"></div>
                                            </a -->

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Begin Nav -->

            <nav class="navbar" id="nav">
                <div class="navbar-inner">
                    <div class="container">
                        <a class="mobile-enable" id="home_nav" href="/" title="Home">
                            <div class="mobile-logo"></div>
                        </a>
                        <ul class="nav nav-normal" style="margin-left:-65px;">
                            <li class="active mobile-disable"><a id="features_nav" href="#featurelink" title="Features" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">FEATURES</a></li>
                            <li class=""><a id="faq_nav" title="FAQ" href="/faq.php" title="Frequently Asked Questions" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">FAQ</a></li>
                            <li class=""><a id="pricing_nav" title="PRICING" href="/pricing.php" title="How Late Pricing Plans" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">PRICING</a></li>
                            <li class=""><a id="press_nav" title="Press" href="/press.php" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">PRESS</a></li>
                            <li class=""><a id="privacy_nav" title="Privacy" href="/privacy.php" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">PRIVACY</a></li>
                        </ul>
                        <ul class="nav nav-dropdown">
                            <li class="dropdown">
                                <a class="dropdown-toggle"
                                   data-toggle="dropdown"
                                   href="#">
                                    SITE
                                    <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- links -->
                            <li class="active mobile-disable"><a id="features_nav" href="#featurelink" title="Features" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">FEATURES</a></li>
                            <li class=""><a id="faq_nav" title="FAQ" href="/faq.php" title="Frequently Asked Questions" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">FAQ</a></li>
                            <li class=""><a id="pricing_nav" title="PRICING" href="/pricing.php" title="How Late Pricing Plans" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">PRICING</a></li>
                            <li class=""><a id="press_nav" title="Press" href="/press.php" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">PRESS</a></li>
                            <li class=""><a id="privacy_nav" title="Privacy" href="/privacy.php" style="padding-left:0px; padding-right:20px;" data-gtm-event="navigation" data-gtm-info="Header">PRIVACY</a></li>
                                    
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
                <span id="nav-mobile-checker" ></span>
            </nav>
            <!-- End Nav -->
            <section class="container-fluid" id="safe"><a id="simplelink"></a>
                <aside class="orange-circle circle"></aside>
                <div class="row-fluid">
                    <div class="span12">
                        <div class="container">
                            <div class="row">
                                <div class="span4 offset1">
                                    <div class="mobile-phone"><img src="master/images/logonphone.png" itemprop="393x800 white bg"/></div>
                                    <h1>A new take on being punctual.</h1>
                                    <ul class="safer-features">
                                        <li class="newlevel-item active">
                                            <a title="Quick" href="#" app-feature="0">
                                                <h3>Quick</h3><p>How-late's lateness lookup is faster than making a phone call.  Like five seconds instead of five minutes!</p>
                                            </a>
                                        </li>
                                        <li class="newlevel-item">
                                            <a title="Comprehensive" href="#" app-feature="1">
                                                <h3>Comprehensive</h3><p>As many doctors or practitioners as you like can appear on the same screen.</p>
                                            </a>
                                        </li>
                                        <li class="newlevel-item">
                                            <a title="Text Alerts" href="#" app-feature="2">
                                                <h3>Text Alerts</h3><p>Send SMS messages to the next two<sup>*</sup> hours' scheduled appointments advising them of your situation.</p>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="span6">
                                    <div class="app-features-wrapper">
                                        <div id="app_features" class="app-features carousel">
                                            <div class="carousel-inner">
                                                <div class="item active">
                                                    <img src="/master/images/lateview1.png">
                                                </div>
                                                <div class="item">
                                                    <img src="/master/images/lateview2.png">
                                                </div>
                                                <div class="item">
                                                    <img src="/master/images/lateview3.png">
                                                </div>
                                                <div class="item">
                                                    <img src="/master/images/lateview4.png">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="hand"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="container-fluid" id="simple">
                <aside class="light-orange-circle"><div class="light-orange-circle-inner circle"></div></aside>
                <div class="row-fluid">
                    <div class="span12">
                        <div class="container">
                            <div class="row">
                                <div class="span6 door-wrapper">
                                    <div class="simple-image"></div>
                                </div>
                                <div class="span5 offset1">
                                    <div class="simple-column">
                                        <div class="row">
                                            <div class="span12">
                                                <h1>Simple for Receptionists.</h1>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="span8">
                                                <ul class="simple-features">
                                                    <li>
                                                        <h3>Auto-Update</h3>
                                                        <p>With Auto-Update your reception staff do <i>exactly nothing</i>.  An agent program reads the information from your existing appointment book and updates the server.</p>
                                                    </li>
                                                    <li>
                                                        <h3>Manual Update</h3>
                                                        <p>You can override the lateness setting manually any time for a while or for the rest of the day.  Or run without agent integration entirely.</p>
                                                    </li>
                                                    <li>
                                                        <h3>Configured Offset</h3>
                                                        <p>You can configure the system so the lateness is under-reported by X minutes, giving you a safety margin.  This is useful so you can use short visits or cancellations to catch up.</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="container-fluid" id="social">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="container">
                            <div class="row">
                                <div class="span6 social-wrapper">
                                    <div class="social-image"></div>
                                </div>
                                <div class="span5 offset1">
                                    <div class="social-features">
                                        <h1>Reaching out to patients.</h1>
                                        <ul class="social-features">
                                            <li>
                                                <h3>Quick Invites</h3>
                                                <p>Patients are invited to use the app by a simple SMS which contains a link.</p>
                                            </li>
                                            <li>
                                                <h3>Extra Invites</h3>
                                                <p>Often patients are being treated by more than one practitioner.  Receptionists can add practitioners to a patient's app quickly and easily.</p>
                                            </li>
                                            <li>
                                                <h3>Complete Control</h3>
                                                <p>Invite a patient to have access for a few days, weeks or months.  Expiry can be set to automatic.</p>
                                            </li>
                                            <li>
                                                <h3>Privacy Assured</h3>
                                                <p>No sensitive patient information needs to be stored on our servers.  Check out our full privacy guarantee below.</p>
                                            </li>
                                        </ul>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="container-fluid" id="features">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="container">

                            <h1 id="featurelink">Features</h1>


                            <div class="features-info">
                                <div class="row">
                                    <div class="scaleup span3">
                                        <h5>SCALE UP</h5>
                                        <p>The system is designed to handle as many clinics as you have.  Share practitioners between clinics as required. The app displays the practitioner's details no matter where they are that day.</p>
                                    </div>
                                    <div class="universal span3">
                                        <h5>UNIVERSAL</h5>
                                        <p>Because it is so simple, the app screen is built with HTML5. This means no compatibility issues and it will display beautifully on iPhones, Android phones and PCs.</p>
                                    </div>
                                    <div class="nocost span3">
                                        <h5>NO COST UP-FRONT</h5>
                                        <p>For patients there is never a cost.  For doctors there is a monthly subscription billed in arrears which includes a generous allowance for SMS messages.</p>
                                    </div>
                                    <div class="freemium span3">
                                        <h5>FREEMIUM</h5>
                                        <p>For sole practitioners, subscription is free and always will be.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Begin Footer -->

            <?php include("footer.php"); ?>
            <!-- End Footer -->
        </div> <!-- here -->
        <div id="fb-root"></div>
        <div id="video_modal" class="modal hide fade">
            <div class="modal-cancel" data-dismiss="modal"></div>
            <div class="modal-body"></div>
        </div>
        
        <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-55071264-1', 'auto');
  ga('require', 'displayfeatures');
  ga('send', 'pageview');

</script>
        <!-- Google Tag Manager -->
        <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-WLZ9QD"
                          height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <script>(function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({'gtm.start':
                            new Date().getTime(), event: 'gtm.js'});
                var f = d.getElementsByTagName(s)[0],
                        j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                        '//www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-WLZ9QD');</script>
        <!-- End Google Tag Manager -->

        <script>
            window.fbAsyncInit = function() {
                FB.init({
                    appId: '737738429614686',
                    xfbml: true,
                    version: 'v2.1'
                });
            };

            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {
                    return;
                }
                js = d.createElement(s);
                js.id = id;
                js.src = "//connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>


        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
        <script src="/master/js/bootstrap/3.3.1/js/bootstrap.min.js"></script>

        
        <script src="/master/js/main.js"></script>


    </body>
</html>
