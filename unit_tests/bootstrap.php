<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$site_path = 'D:/xampp/htdocs/public_html/master';
define('__SITE_PATH', $site_path); 

date_default_timezone_set("Australia/Adelaide");
$_SERVER["SERVER_NAME"] = 'm.howlate.com';
include_once(__SITE_PATH . '/includes/init.php');
ob_start();
