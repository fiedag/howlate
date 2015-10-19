function getQueryStrings() { 
  var assoc  = {};
  var decode = function (s) { return decodeURIComponent(s.replace(/\+/g, " ")); };
  var queryString = location.search.substring(1); 
  var keyValues = queryString.split('&'); 

  for(var i in keyValues) { 
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
  window.location = "https://" + window.location.hostname + "/store/?code=" + reservationCode + "&utm_source=mainsite&utm_medium=web&utm_content=redirect&utm_campaign=mainsite&siteVersion=galleryEnabledCarousel";
}

var adroll_adv_id = "Q5ODOSHZJVEBBG7FW2CY3P";
var adroll_pix_id = "IZR7HAJIINEJHETKC43M2Z";

(function($) {
  
  var oldonload = window.onload;
  
  window.onload = function(){
    __adroll_loaded=true;
    var scr = document.createElement("script");
    var host = (("https:" == document.location.protocol) ? "https://s.adroll.com" : "http://a.adroll.com");
    scr.setAttribute('async', 'true');
    scr.type = "text/javascript";
    scr.src = host + "/j/roundtrip.js";
    ((document.getElementsByTagName('head') || [null])[0] ||
    document.getElementsByTagName('script')[0].parentNode).appendChild(scr);
    if(oldonload){oldonload()}
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
        if (reserve_mode) return;
        $('#nav').fixedNav({ $window: $(this) });
      });
    }

    if ("true" === queryStrings['reserve']) {
      displayDialog();
    }

   // Smooth Scrolling
   if (!isIE) {
      $('body').on('click.anchor', 'a[href^="#"]', function(evt) {
         var anchor = $(this).attr('href');
         if (anchor.length < 2) return;
       evt.preventDefault();
       $(document.body).animate({'scrollTop': $(anchor).offset().top}, 500);
      });

      //only on main page
      var page = getBaseURL()[3];
      if (page == '/' || page == '/index.html') {
        $('.brand').on('click.anchor', function(evt) {
          evt.preventDefault();
          $(document.body).animate({'scrollTop': 0}, 500);
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
      if (!navVisible) return;
      evt.preventDefault();
      var width = Math.min(880, $(window).width() - 60);
      var height = width*0.588;
      $('#video_modal .modal-body').html('<iframe width="'+width+'" height="'+height+'" src="'+$(this).attr('href')+'" frameborder="0" allowfullscreen></iframe>');
      $('#video_modal').css({ marginLeft: ($('#video_modal').width() / 2) * -1});
      $('#video_modal').modal('show');
    });
  
    $('#video_modal').on('hidden', function () {
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
        if (is_valid) is_valid = validateEmailIE($email_input.val());
      } else {
        is_valid = $email_input.is(':valid');
      }

      if (!is_valid) {
        $('.error').text('Please enter a valid email.');
      } else {
        $('#submit_link, .reserve-welcome').addClass('hide');
        
        $.post(baseURI + '/reservequantity/'+encodeURIComponent($email_input.val())+'/1', function(data) {
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

    // Gallery
    $('#august_carousel').carousel({ interval: 10000 });
    $('#august_carousel').carousel('cycle');
    $('#august_carousel a.prev').on('click', function(evt) {evt.preventDefault();$('#august_carousel').carousel('prev');});
    $('#august_carousel a.next').on('click', function(evt) {evt.preventDefault();$('#august_carousel').carousel('next');});
    
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
      if(newLevelTitle !== newLevelActive) {
        newLevelActive = newLevelTitle;
        dataLayer.push({
          'event': 'safeLinkMouseover',
          'safeLinkSection': newLevelActive
        });
      }
      
    })
    
    $('#app_features').carousel({ interval: false });
    
    $('.safer-features a[app-feature]').on('mouseover click', function(evt) {
      evt.preventDefault();
      $('#app_features').carousel(parseInt($(this).attr('app-feature')));
    });
    
    // Twitter
    $('.social-tweet').on('click.share', function(evt) {
      evt.preventDefault();
      var tweet = "Just%20pre-ordered%20my%20%40AugustSmartLock.%20%20Get%20yours%20at%20www.august.com%20%23easyaccess";
      window.open('http://twitter.com/share?url=&text=' + tweet, 'twitterwindow', 'height=420, width=550, top='+($(window).height()/2 - 225) +', left='+$(window).width()/2 +', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
    });
    
    // Facebook
    $('.social-facebook').on('click.share', function(evt) {
      evt.preventDefault();
      var obj = {
        method: 'feed',
        link: 'http://www.how-late.com',
        picture: '/master/images/facebook-image.jpg',
        name: 'How Late Cloud App',
        caption: "Just signed up to How-Late.Com.  Get yours at how-late.com.",
        description: "How Late Cloud App"
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
    
    fixedNav: function( options ) {
      
      if ($(this).length == 0) return;

      if ($('#nav-mobile-checker').css('display') != 'none')
        return;

      var $self       = $(this),
        self        = this,
        $window     = options.$window,
        offset      = $self.offset().top + 15,
        $fixedWrapper   = $('#fixedNav'),
        $circleWrapper  = $('.header-circle'),
        circleOffset    = offset - 91;

      if ( $window.scrollTop() >= offset && !$('#fixedNav').length ) {
        
        dataLayer.push({'event': 'menuAttached'});
        
        $fixedWrapper = $('<div id="fixedNav"></div>').prependTo('body');
        $fixedWrapper.append($self.clone())
          .find('nav')
          .removeAttr('id');
          
        setTimeout(function() {$fixedWrapper.find('nav').addClass('fixed');}, 0);
        
        $circleWrapper.children('.header-circle-inner').animate({ marginTop: '-70px'}, { duration: 500, easing: 'easeOutQuad', queue: false });
        
        $('#reserve_link').animate({ top: '90px'}, { duration: 500, easing: 'easeOutQuad', queue: false });
        $('#welcome').addClass('fade');
        
      } else if ( $fixedWrapper.length && $window.scrollTop() <= offset ) {
        $fixedWrapper.remove();
        $('#reserve_link').animate({ top: '130px'}, { duration: 500, easing: 'easeOutQuad', queue: false });
        $circleWrapper.children('.header-circle-inner').animate({ marginTop: '-100px'}, { duration: 500, easing: 'easeOutQuad', queue: false });
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
    reserve_mode = false;
    $('.error, #confirmation_number').text('');
    $('#email_input, #email_input_mobile').val('');
    $(".reserve-cancel").css('display', 'none');
    $(".reserve-window").animate({ height: '0px'}, { duration: 240, easing: 'easeOutQuad', queue: false });
    $(".reserve-backdrop, .reserve-thank-you, .reserve-sorry").removeClass('in');
    $(".reserve-sorry, .confirmation-copy").addClass('hide');
    $(".reserve-window-hide-on-open").show();
    setTimeout(function() {
      $(".reserve-backdrop").detach();
      $(".reserve-welcome").removeClass('hide');
      $('#nav').fixedNav({ $window: $(document) });
      window.scrollTo(0,window.scrollY+5);
      //fix clipping issue on desktops
      setTimeout(function() {
        window.scrollTo(0,window.scrollY-5);
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
      setTimeout(function() {$backdrop.addClass('in');}, 100);
      $backdrop = $('<div class="reserve-backdrop fade"></div>');
      $('body').append($backdrop);
      $('.reserve-thank-you').css('display', 'none');
      $('#submit_link, .reserve-welcome, .confirmation-copy').removeClass('hide');
      $(".reserve-window").animate({ height: navVisible ? '475px' : '800px'}, { duration: 240, easing: 'easeOutQuad', queue: false });
      $(".reserve-window-hide-on-open").hide();
      setTimeout(function() {$(".reserve-cancel").css('display', 'block');}, 300);
      if (isIE) $(".reserve-cancel").css('display', 'block');
    }, delay);
  }
  

})(jQuery);