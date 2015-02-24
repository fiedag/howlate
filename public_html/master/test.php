<?php 


function __autoload($class_name) {
    $filename = strtolower($class_name) . '.class.php';
    $file = '/home/howlate/public_html/master/model/' . $filename;
    if (file_exists($file) == false) {
        return false;
    }
    include ($file);
}

for ($i = 0 ; $i < 100 ; $i++) {
  echo "i = $i = " . howlate_util::toBase26($i) . "<br>";
}


?>