<footer>
    <div class="footer">
        <div class="container">
            <div id="fading-message" class="fading-message"><?php if (isset($fadingmessage)) { echo $fadingmessage; } ?></div>
            <div id="footer-magic"><img src="/images/spacer.gif" /></div>
            <div class="footer-border">
                <div class="span-10 append-1">
                    <h4>Product</h4>
                    <ul>
                        <li><a target="_blank" href="http://<?php echo __DOMAIN; ?>/pricing.php" id="footer-feature">Billing</a></li>
                        <li><a href="http://<?php echo __FQDN; ?>/help" id="footer-feature">Help</a></li>
                        <!-- li><a target="_blank" href="http://<?php echo __FQDN; ?>/support/newfeatures">New Features</a></li -->
                    </ul>
                </div>
                <div class="span-8 append-1">
                    <h4>Community</h4>
                    <ul>
                        <li><a target="_blank" href="http://<?php echo __DOMAIN;?>/welcome/blog/">Blog</a></li>
                        <li><a target="_blank" href="http://<?php echo __DOMAIN;?>/faq.php">FAQ</a></li>
                    </ul>
                </div>
                <div class="span-8 append-1">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="http://<?php echo __FQDN; ?>/support/contact">Contact</a></li>
                        <li><a target="_blank" href="http://<?php echo __FQDN; ?>/terms">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="span-8 append-1"><a href="https://www.positivessl.com" style="font-family: arial; font-size: 10px; color: #212121; text-decoration: none;"><img class="ssl-seal" src="https://www.positivessl.com/images-new/PositiveSSL_tl_trans.png" alt="Secured by COMODO Positive SSL" title="Secured by COMODO Positive SSL" border="0" /></a></div>
            </div>
            <div class="clearb"></div>
        </div>
    </div>


<!-- This is a comment in the footer view -->


</footer>

<script>

$( document ).ready(function () {  $("#fading-message").fadeOut(5000)})

</script>

</body>
</html>