<?php

header('Content-Type: text/plain');

$ch = curl_init('https://how-late.chargeover.com/api/v3/customer?_dummy=1&where=external_key:EQUALS:AAADD');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');


$public = 'IfCopeybjKkJOwBsgdqHSRat8lh5X6zv';
$private  = 'rNjiGRbW6EfA7LC5mgo218MdHSwz4yP3';

curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $public . ':' . $private);

curl_setopt($ch, CURLOPT_VERBOSE, true);

$fp = fopen('php://output', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $fp);

$out = curl_exec($ch);

print('[[[' . $out . ']]]');

print_r(curl_getinfo($ch));

?>
