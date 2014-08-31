<?php


include("/home/howlate/public_html/master/model/howlate_db.class.php");

$db = new howlate_db();

$result = $db->getAllLatenesses();
foreach($result as $key => $val) {
    $localtime = new DateTime(null, new DateTimeZone($val->Timezone));
    
    echo "<h3>$key</h3>"; 
    
    echo "<b> $val->ClinicName : $val->Timezone </b> where the local time is : " . $localtime->format('H:i:s');
    echo " and the clinic closes at " ;
    
    
//    $src_dt = '2012-05-15 10:50:00';
//    $src_tz =  new DateTimeZone('Asia/Manila');
//    $dest_tz = new DateTimeZone('America/Vancouver');
//
//    $dt = new DateTime($src_dt, $src_tz);
//    $dt->setTimeZone($dest_tz);
//
//    $dest_dt = $dt->format('Y-m-d H:i:s');
    
    
    
    
}

?>
