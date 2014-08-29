<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


include("/home/howlate/public_html/master/model/howlate_db.class.php");

$db = new howlate_db();


$result = $db->getAllLatenesses();

foreach($result as $item) {
    echo "<h3>Next Item   " . $item->OrgID . " </h3>";
    echo "Name: $item->FullName <br> $item->Timezone";
    $datetime = new DateTime('2008-08-03 12:35:23');
    echo "where it is $datetime";
    
}


?>