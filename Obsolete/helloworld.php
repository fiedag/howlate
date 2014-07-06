<?php 

include "model/howlate_db.class.php";

$now = new DateTime();

$db = new howlate_db();

$arr = $db->getAllLatenessesTZ();


foreach ($arr as $k => $v) {
    
    $dt = $v->Updated;    
    $op = $v->OpeningHrs;
    $cl = $v->ClosingHrs;
    
    $updated = new DateTime($dt);
    $updated->setTimezone(new DateTimeZone($v->Timezone));
    
    $opening = date("d/m/Y H:i:s",strtotime($op));
    $closing = date("d/m/Y H:i:s",strtotime($cl));
    
    $now = new DateTime();
    $now->setTimezone(new DateTimeZone($v->Timezone));
    $now = $now->format("d/m/Y H:i:s");

    if ($now > $closing or ($now < $opening and $now < $closing)) {
        echo " clinic is closed so delete the lateness and make practitioner on-time";
        $db->deleteLate($v->OrgID, $v->ID);
    }
    
}

?>

