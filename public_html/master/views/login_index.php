<!DOCTYPE html>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet" >
        <link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">
        <link rel="apple-touch-icon" href="<?php echo $logourl ?>" >
        <link rel="icon" type="image/png" href="<?php echo $logourl; ?>" />

    </head>


    <body class="new-form custom-background">



        <div id="outer">
            <div id="middle">
                <div id="inner">
                    <div class="name-container font-on-custom-background">
                        <div class="company-name"><?php echo $companyname; ?></div>
                        <div class="login-info">Please log in to your account</div>
                    </div>
                    <div class="secure-info font-on-custom-background ">
                        <div class="secure-lock"></div>
                        This website is protected by 256-bit SSL security
                    </div>
                    <div class="form-pad-top clearb"></div>
                    <div class="boxMain">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="275" class="login-table">
                                    <?php if (isset($logourl)) { ?> 
                                        <img class="photo" alt="" title="" width="150" src="<?php echo $logourl; ?>" />
                                    <?php } ?>
                                </td>
                                <td> 
                                    <div class="login-right-container">
                                        <!-- script>var h1 = $(".notification-page.title-only").height(), h2 = $(".notification-page.title-only h3").height(), mt = (h1 - h2) * 0.5;
                                            $(".notification-page.title-only h3").css("marginTop", mt);</script -->
                                        <?php if (isset($password_incorrect) and $password_incorrect == 1) { ?>
                                            <div class="notification-page notification-error">
                                                <div class="notifyBox_oneline">
                                                    Sorry, your username or password is incorrect. Please try again.
                                                </div>
                                            </div>

                                        <?php } 
                                            if (isset($sentok) and $sentok == 1) { ?>

                                            <div class="notification-page notification-error">
                                                <div class="notifyBox_oneline">
                                                    We've sent the email to the address you provided (<strong><?php echo $email; ?></strong>).
				Please check your email and follow the link to reset your password.
                                                </div>
                                            </div>

                                        <?php 
                                              }
                                        ?> 
                                        
                                        
                                        <div class="login-forms">

                                            <form method="POST" id="form_forgot" style="display: none;" action="/login/forgot">
                                                <div class="form-block">
                                                    <p>Forgot your username or password? No worries, enter your email address below and we will hook you up.</p>

                                                    <label for="email">Email Address:</label>
                                                    <input type="text" name="email" id="email" maxlength="255" /><br />

                                                    <button class="button large green request" type="submit" name="Submit" value="Submit">Request Login Info</button>



                                                    <div class="forgot-link">
                                                        <a href="javascript:login();">
                                                            Return to your login page
                                                        </a>
                                                    </div>
                                                </div>
                                            </form>

                                            <form method="POST" id="form_openid" style="display: none;">
                                                <div class="form-block">
                                                    <p>If you installed how-late through the Google Apps Marketplace, you can sign in with your email address.</p>

                                                    <label for="openid_identifier">Google Apps Email:</label>
                                                    <input type="text" name="openid_identifier" id="openid_identifier" maxlength="50"  /><br />

                                                    <button class="button large green login" type="submit" name="Submit" value="Submit">Log in</button>

                                                    <div class="forgot-link">
                                                        <a href="javascript:login();">
                                                            Return to your login page
                                                        </a>
                                                    </div>
                                                </div>
                                            </form>

                                            <form name="login" method="POST" id="form_login" style="display: block;" action="/login/attempt">
                                                <div class="form-block">
                                                    <label for="username">Username:</label>
                                                    <input type="text" name="username" id="username" maxlength="50" value=""  /><br />

                                                    <label for="password">Password:</label>
                                                    <input type="password" name="password" id="password" maxlength="120" value=""  /><br />

                                                    <button class="button large green login" id="log_in" type="submit" name="Submit" value="Submit">Log in</button>

                                                    <div class="forgot-link">
                                                        <a href="javascript:forgot();">
                                                            Forgot your username or password?
                                                        </a>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="openid" id="openid" value="" />
                                                <input type="hidden" name="forgotten" id="forgotten" value="" />
                                                <input type="hidden" name="javascriptDisabled" id="javascriptDisabled" value="1" />
                                            </form>

                                        </div>

                                        <div class="clearb"></div>

                                    </div>

                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="login-box-shadow"></div>
                </div>
            </div>
        </div>

        <script src="js/login.js" type="text/javascript" ></script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#welcome').slideDown();

                // focus on username
                $('#form_login input[value=""]:first').trigger('focus');

                // on submit of forgotten password form remove login form fields else removed forgotten form fields
                $('#form_forgot, #form_openid, #form_login').submit(function() {
                    document.getElementById('javascriptDisabled').value = 0;
                });


                /* add placeholder text to input fields only for mobile devices */
                if ($('.form-block label').css('display') == 'none') {
                    $('#username').attr('placeholder', 'Username');
                    $('#password').attr('placeholder', 'Password');
                    $('#email').attr('placeholder', 'Email Address');
                    $('#openid_identifier').attr('placeholder', 'Google Apps Email Address');
                }
            });

            function switchForms(src, dst, element) {
                if (!src.is(':visible')) {
                    return;
                }
                src.slideUp('normal', function() {
                    dst.slideDown(function() {
                        element.focus();
                    });
                });
            }

            function forgot() {
                
                $('#forgotten').val('1');
                $('#openid').val('');

                //guiders.hideAll();
                    
                var form = $('#form_forgot').is(':visible') ? $('#form_forgot') : $('#form_login');

                switchForms(form, $('#form_forgot'), document.getElementById('email'));
            }

            function openid() {
                $('#forgotten').val('');
                $('#openid').val('1');

                var form = $('#form_forgot').is(':visible') ? $('#form_forgot') : $('#form_login');

                switchForms(form, $('#form_openid'), document.getElementById('openid_identifier'));
            }

            function login() {
                $('#forgotten').val('');
                $('#openid').val('');
                $(['form_forgot', 'form_openid']).each(function() {
                    switchForms($('#' + this), $('#form_login'), document.getElementById('username'));
                });
            }

        </script>        