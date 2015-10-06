function signupFunction()
{

    company = $("#domain_input").val();
    email = $("#email_input").val();
    if (company == "" || email == "") {
        alert("Please supply a domain name and email address");
        return;
    }
    if (!isValidEmailAddress(email)) {
        alert("The email address is not valid.");
        return;
    }

    $("#signup_link").hide();
    $("#start_trial").html("Please wait.  This may take about 1 minute");
    $("#loader_container").css("display", "block");
    $("#loader").css("display", "block");
    $("#start_trial").html("Creating domain for " + company + ".  Please wait.");

    url_get = "https://m.how-late.com/signup/create?company=" + company + "&email=" + email;
    post_data = "";
    //$("#start_trial").html("Calling " + url_get);
    debugger;
    $.get(url_get, function(data) {
        resultFunction(data);
    });

}
function resultFunction(html) {
    $("#start_trial").html(html);
    $("#loader_container").css("display", "none");
    $("#signup_link").show();
    
}

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};


function getQueryStrings() {
    var assoc = {};
    var decode = function(s) {
        return decodeURIComponent(s.replace(/\+/g, " "));
    };
    var queryString = location.search.substring(1);
    var keyValues = queryString.split('&');

    for (var i in keyValues) {
        var key = keyValues[i].split('=');
        if (key.length > 1) {
            assoc[decode(key[0])] = decode(key[1]);
        }
    }

    return assoc;
}

function getBaseURL() {
    var baseUriRegex = new RegExp('(https?://([^/]+))(/[^\?#]*)?');
    return baseUriRegex.exec(location.href);
}

function redirectToOrderSystem(reservationCode) {
    window.location = "https://" + window.location.hostname + "/store/?code=" + reservationCode;
}

var adroll_adv_id = "Q5ODOSHZJVEBBG7FW2CY3P";
var adroll_pix_id = "IZR7HAJIINEJHETKC43M2Z";

(function($) {

    var oldonload = window.onload;

    window.onload = function() {
        __adroll_loaded = true;
        var scr = document.createElement("script");
        var host = (("https:" == document.location.protocol) ? "https://s.adroll.com" : "http://a.adroll.com");
        scr.setAttribute('async', 'true');
        scr.type = "text/javascript";
        scr.src = host + "/j/roundtrip.js";
        ((document.getElementsByTagName('head') || [null])[0] ||
                document.getElementsByTagName('script')[0].parentNode).appendChild(scr);
        if (oldonload) {
            oldonload()
        }
    };


    $(document).ready(function() {
        var mobile = ("ontouchend" in document.documentElement);
        var queryStrings = getQueryStrings();
        var baseURI = 'http://' + location.host + '/reservations';

        if (mobile) {
            //force mobile checker
            $('#nav-mobile-checker').css('display', "block");
            $('body').addClass('ios-scroll-fix');
        } else {
            // Header Snap
            $(window).bind('scroll', function() {
                if (reserve_mode)
                    return;
                $('#nav').fixedNav({$window: $(this)});
            });
        }

        if ("true" === queryStrings['reserve']) {
            displayDialog();
        }

        // Smooth Scrolling
        if (!isIE) {
            $('body').on('click.anchor', 'a[href^="#"]', function(evt) {
                
                var anchor = $(this).attr('href');
                if (anchor.length < 2)
                    return;
                evt.preventDefault();
                $('body,html').animate({'scrollTop': $(anchor).offset().top}, 500);
                
            });

            //only on main page
            var page = getBaseURL()[3];
            if (page == '/' || page == '/index.php') {
                $('.brand').on('click.anchor', function(evt) {
                    evt.preventDefault();
                    $('body,html').animate({'scrollTop': 0}, 500);
                });
            }
        }

        // IE 'placeholder'
        if (isIE) {
            $('#email_input, #email_input_mobile').val('email');
        }

        $('input[type=email]').on('click', function() {
            $(this).select();
        });

        // IE 8 Rounded Corners
        if (window.PIE) {
            $('.circle').each(function() {
                PIE.attach(this);
            });
        }

        // Video Player
        $('.video-link').on('click', function(evt) {
            var navVisible = $('nav:visible').length > 0;
            if (!navVisible)
                return;
            evt.preventDefault();
            var width = Math.min(880, $(window).width() - 60);
            var height = width * 0.588;
            $('#video_modal .modal-body').html('<iframe width="' + width + '" height="' + height + '" src="' + $(this).attr('href') + '" frameborder="0" allowfullscreen></iframe>');
            $('#video_modal').css({marginLeft: ($('#video_modal').width() / 2) * -1});
            $('#video_modal').modal('show');
            dataLayer.push({'event': 'videoLoaded'});
        });

        $('#video_modal').on('hidden', function() {
            $('#video_modal .modal-body').html('');
        });

        // Reserve Flow
        $('#email_input, #email_input_mobile').on('keypress', function(evt) {
            if (evt.which == 13) {
                evt.preventDefault();
                if (!$('#submit_link').hasClass('hide')) {
                    $('#submit_link').trigger('click.modal');
                }
            }
        });

        $('#reserve_link').on('click.modal', function(evt) {
            evt.preventDefault();
            displayDialog();
        });

        $('.reserve-cancel').on('click.modal', function() {
            dismissDialog();
        });

        // submit email address
        $('#submit_link, #submit_link_mobile').on('click.modal', function(evt) {
            evt.preventDefault();

            $email_input = $('#email_input_mobile').is(':visible') ? $('#email_input_mobile') : $('#email_input');
            $quantity_input = $('#quantity_input_mobile').is(':visible') ? $('#quantity_input_mobile') : $('#quantity_input');

            var is_valid = false;
            if (isIE) {
                is_valid = $email_input.val().length > 0;
                if (is_valid)
                    is_valid = validateEmailIE($email_input.val());
            } else {
                is_valid = $email_input.is(':valid');
            }

            dataLayer.push({
                'event': 'emailSubmit',
                'emailValid': is_valid,
                'emailAddress': $email_input.val()
            });

            if (!is_valid) {
                $('.error').text('Please enter a valid email.');
            } else {
                $('#submit_link, .reserve-welcome').addClass('hide');
                $.post(baseURI + '/reservequantity/' + encodeURIComponent($email_input.val()) + '/1', function(data) {
                    var reservationCode = data.ReservationCode;
                    if (reservationCode) {
                        redirectToOrderSystem(reservationCode);
                    } else {
                        $('.reserve-sorry').removeClass('hide').addClass('in');
                    }
                }).fail(function(err) {

                    var responseText = err.responseText;
                    var responseObject = JSON.parse(responseText);
                    var messageString = (responseObject) ? responseObject.message : null;

                    if (messageString) {
                        if ((messageString.indexOf("reservation") === 0) && (messageString.indexOf("exists") !== -1)) {
                            var messageTokens = messageString.split(' ');
                            var reservationCode = messageTokens[1];
                            if (reservationCode) {
                                redirectToOrderSystem(reservationCode);
                                return;
                            }
                        }
                    }
                    $('.reserve-sorry').removeClass('hide').addClass('in');
                });
            }
        });

        // Installation
        $('#installation #open_door_link').on('click', function() {
            $('#installation .frame1').fadeOut();
            $('#installation .frame2').fadeIn();
        });
        $('#installation #close_door_link').on('click', function() {
            $('#installation .frame2').fadeOut();
            $('#installation .frame1').fadeIn();
        });


        // 'Safe' App Screens
        $('.safer-features a').on('mouseover click', function(evt) {
            evt.preventDefault();

            var current = this;
            $('.safer-features a').each(function() {
                current == this ? $(this).parent().addClass('active') : $(this).parent().removeClass('active');
            });
        });

        // gtm on safety level items
        var newLevelActive = "";
        $('.newlevel-item').on('mouseover', function() {

            var newLevelTitle = $(this).children('a').attr('title')

            // avoid superfast repeats on inner elms
            if (newLevelTitle !== newLevelActive) {
                newLevelActive = newLevelTitle;
                dataLayer.push({
                    'event': 'safeLinkMouseover',
                    'safeLinkSection': newLevelActive
                });
            }

        })

        // gtm on job apply clicks
        $('.jobflo_job_post').each(function() {
            $post = $(this);
            var jobTitle = $post.find('.jobflo_job_title').text();
            $post.find('.jobflo_job_description table a').on('click', function(evt) {
                evt.preventDefault();
                dataLayer.push({
                    'event': 'jobClick',
                    'jobTitle': jobTitle
                });
            });

        });

        $('#app_features').carousel({interval: false});

        $('.safer-features a[app-feature]').on('mouseover click', function(evt) {
            evt.preventDefault();
            $('#app_features').carousel(parseInt($(this).attr('app-feature')));
        });

        // Twitter
        $('.social-tweet').on('click.share', function(evt) {
            evt.preventDefault();
            var tweet = "Just%20signup%20up%20to%20How-Late.Com%20";
            window.open('http://twitter.com/share?url=&text=' + tweet, 'twitterwindow', 'height=420, width=550, top=' + ($(window).height() / 2 - 225) + ', left=' + $(window).width() / 2 + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
        });

        // Facebook
        $('.social-facebook').on('click.share', function(evt) {
            evt.preventDefault();
            var obj = {
                method: 'feed',
                link: 'http://how-late.com',
                picture: '',
                name: 'How-Late Cloud',
                caption: "Just joined up with How-Late.com",
                description: "How-Late Cloud"
            };

            FB.ui(obj);
        });


        //modals
        $('a[data-toggle=modal]').on('click', function() {
            $('body').css('overflow', 'hidden');
        });

        $('#compatibilityDialog').on('click', function(event) {
            $('body').css('overflow', 'auto');
            $(this).find('button.close').trigger('click');
        });

    });

    $.fn.extend({
        fixedNav: function(options) {

            if ($(this).length == 0)
                return;

            if ($('#nav-mobile-checker').css('display') != 'none')
                return;

            var $self = $(this),
                    self = this,
                    $window = options.$window,
                    offset = $self.offset().top + 15,
                    $fixedWrapper = $('#fixedNav'),
                    $circleWrapper = $('.header-circle'),
                    circleOffset = offset - 91;

            if ($window.scrollTop() >= offset && !$('#fixedNav').length) {

                dataLayer.push({'event': 'menuAttached'});

                console.log("menu attached!")

                $fixedWrapper = $('<div id="fixedNav"></div>').prependTo('body');
                $fixedWrapper.append($self.clone())
                        .find('nav')
                        .removeAttr('id');

                setTimeout(function() {
                    $fixedWrapper.find('nav').addClass('fixed');
                }, 0);

                $circleWrapper.children('.header-circle-inner').animate({marginTop: '-90px'}, {duration: 500, easing: 'easeOutQuad', queue: false});

                $('#reserve_link').animate({top: '90px'}, {duration: 500, easing: 'easeOutQuad', queue: false});
                $('#welcome').addClass('fade');

            } else if ($fixedWrapper.length && $window.scrollTop() <= offset) {
                $fixedWrapper.remove();
                $('#reserve_link').animate({top: '130px'}, {duration: 500, easing: 'easeOutQuad', queue: false});
                $circleWrapper.children('.header-circle-inner').animate({marginTop: '-100px'}, {duration: 500, easing: 'easeOutQuad', queue: false});
                $('#welcome').removeClass('fade');
            }

            if ($('#fixedNav').length) {
                $circleWrapper.height($self.height() - 15);
            } else {
                var circleHeightOffset = parseInt(100 + $('.header-circle-inner').css('margin-top').replace('px', ''))
                var circleMin = 91 + circleHeightOffset;
                var circleHeight = Math.min(circleMin, Math.max($self.height() - 15, $self.height() - ($window.scrollTop() - $self.offset().top)));
                $circleWrapper.css('height', circleHeight);
            }

            return self;
        }

    });

    var reserve_mode = false;
    var isIE = /*@cc_on!@*/false;

    // From StackOverflow: 46155
    function validateEmailIE(val) {
        var re = /\S+@\S+\.\S+/;
        return re.test(val);
    }

    function dismissDialog() {
        debugger;
        reserve_mode = false;
        $("#signup_link").show();
        $("#loader_container").hide();
        $('.error, #confirmation_number').text('');
        $('#email_input, #email_input_mobile').val('');
        $(".reserve-cancel").css('display', 'none');
        $(".reserve-window").animate({height: '0px'}, {duration: 240, easing: 'easeOutQuad', queue: false});
        $(".reserve-backdrop, .reserve-thank-you, .reserve-sorry").removeClass('in');
        $(".reserve-sorry, .confirmation-copy").addClass('hide');
        $(".reserve-window-hide-on-open").show();
        setTimeout(function() {
            $(".reserve-backdrop").detach();
            $(".reserve-welcome").removeClass('hide');
            $('#nav').fixedNav({$window: $(document)});
            window.scrollTo(0, window.scrollY + 5);
            //fix clipping issue on desktops
            setTimeout(function() {
                window.scrollTo(0, window.scrollY - 5);
            }, 20);
        }, 140);
    }

    function displayDialog() {
        var delay = 0;
        var navVisible = $('nav:visible').length > 0;
        if (!$('#fixedNav').length && navVisible) {
            delay = 600;
            $(document.body).animate({'scrollTop': $('#nav').offset().top + 50}, 500);
        }
        setTimeout(function() {
            reserve_mode = true;
            setTimeout(function() {
                $backdrop.addClass('in');
            }, 100);
            $backdrop = $('<div class="reserve-backdrop fade"></div>');
            $('body').append($backdrop);
            $('.reserve-thank-you').css('display', 'none');
            $('#submit_link, .reserve-welcome, .confirmation-copy').removeClass('hide');
            $(".reserve-window").animate({height: navVisible ? '575px' : '800px'}, {duration: 240, easing: 'easeOutQuad', queue: false});
            $(".reserve-window-hide-on-open").hide();
            setTimeout(function() {
                $(".reserve-cancel").css('display', 'block');
            }, 300);
            if (isIE)
                $(".reserve-cancel").css('display', 'block');
        }, delay);
    }



})(jQuery);