<!DOCTYPE html>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

        <!-- link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet" -->
        <link rel="apple-touch-icon" href="<?php echo $logourl ?>" >
        <link rel="icon" type="image/png" href="<?php echo $logourl; ?>" />
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">

    </head>

    <style>

.custom-background,
.custom-background-hover:hover {
    background-color: #337ab7 !important;
}

.font-on-custom-background, .font-on-custom-background-hover:hover {
    color: #ffffff !important;
}

.company-name {
    font: bold 24px Arial,Helvetica,sans-serif;
    padding: 10px 25px 5px 14px;
}
.login-info {
    width: 370px;
    float: left;
    font: normal 14px Arial,Helvetica,sans-serif;
    padding-left: 14px;
}

#box {
    width: 60%;
    margin-top: 10%;
    margin-left: auto;
    margin-right: auto;
    text-align: left;
    border: 0;
}
        
.boxMain {
    background: #e9e9e9 url('images/arrow.gif') no-repeat 0 center;
    border: 1px solid #d9d9d9;
    -o-border-radius: 8px;
    -webkit-border-radius: 8px;
    -moz-border-radius: 8px;
    border-radius: 8px;
    width: 100%;
    padding: 0;
    position: relative;
    overflow: hidden;
    height: 100%;
}

.login-table {
    min-height: 300px;
    padding-top: 5px;
}
.login-table img {
    margin-left: 45px;
}
.login-right-container {
    padding: 40px 20px;
}

.horiz-pad {
    height: 2em;
}
.secure-info {
    font: normal 11px/18px Arial,Helvetica,sans-serif;
    float: right;
    width: 60%;
    padding: 0 20px 0 0;
    text-align: right;
}
    </style>    
    
    

    <body class="custom-background">
                <div class="panel panel-default custom-background" id="box">
                    <div class="font-on-custom-background">
                        <div class="company-name"><?php echo $companyname; ?></div>
                        <div class="login-info">Please log in to your account</div>
                    </div>
                    <div class="font-on-custom-background secure-info">
                        <div class="glyphicon glyphicon-lock"></div>This website is protected by 256-bit SSL security
                    </div>
                    <div class="horiz-pad"></div>
                    <div class="boxMain">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="275" class="login-table">
                                    <img class="photo" alt="" title="" width="150" src="<?php echo HowLate_Util::logoURL(__SUBDOMAIN); ?>" />
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
                                                    <input type="text" name="email" id="email" maxlength="255" placeholder="Enter Email" /><br />

                                                    <button class="button large green request" type="submit" name="Submit" value="Submit">Request Login Info</button>



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
                                                    <input type="text" class="form-control" name="username" id="username" maxlength="50" value="" placeholder="Enter Email address or User Id" /><br />

                                                    <label for="password">Password:</label>
                                                    <input type="password" class="form-control" name="password" id="password" maxlength="120" value=""  /><br />

                                                    <button class="btn btn-primary" id="log_in" type="submit" name="Submit" value="Submit">Log in</button>

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


        <script src="js/login.js" type="text/javascript"></script>

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