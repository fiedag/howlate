
<script>
  function openView(UDID) {
      var url = "http://m.<?php echo __DOMAIN; ?>/late/view?udid=" + UDID;
      window.open(url,"_blank");
  }


</script>

<div id="devices-content" class="container primary-content">
   <?php echo $xcrud_content; ?>
</div>



<div id="helpbubble_holder"></div>


<script src="/js/libs/Ractive.min.js"></script>
<script src="/js/libs/lodash.js"></script>
<script src="/js/helpbubbles.min.js"></script>
<script src="/js/helpbubbles.fix.js"></script>

<script>
    var bubblecious;
    bubblecious = new window.HelpBubbles({
        el: 'helpbubble_holder',
        data: {
            y_adjustment: 20,
            bubbles: [
                {
                    target: 'nav-sub-devices/index',
                    content: 'Devices (e.g. Mobile phones) which have been registered or invited .',
                },
                {
                    target: 'nav-sub-devices/perspectives',
                    content: 'Lateness of practitioners from the perspective of relevant devices',
                    onTap: function () {
                        console.log('Alt Bubble-tapped');
                    }
                }
            ]
        }
    });



</script>



<?php $controller->get_footer(); ?>


