<?php

date_default_timezone_set('Australia/Melbourne');

echo ("################## BILL DUE ACCOUNTS ########################<br>");

include("/home/howlate/public_html/master/model/billing.class.php");
include("/home/howlate/public_html/master/model/logging.class.php");
include("/home/howlate/public_html/master/model/invoicer.class.php");
include("/home/howlate/public_html/master/model/howlate_util.class.php");

$billing = new billing();

$billing->prepareAllDueBills();

?>