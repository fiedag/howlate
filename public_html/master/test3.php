<?php

$tonearest = 5;
$offset = 10;

for ($i = 1; $i <= 100; $i++) {

    echo "tonearest = $tonearest, offset = $offset, i = $i, ";

    $display = round($i / $tonearest, 0) * $tonearest - $offset;
    $display = ($display>0)?$display:0;
    
    echo "display = $display";
    echo "<br>";
}
?>
