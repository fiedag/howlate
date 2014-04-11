<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

if (!isset($_SESSION["USER"]) or $_SESSION["LAST_ACTIVITY"] < time() - 3600) {
    
    if (isset($_COOKIE["USER"]) and isset($_COOKIE["ORGID"])) {
        // get some info from the cookie
        $_SESSION["ORGID"] = $_COOKIE["ORGID"];
        $_SESSION["USER"] = $_COOKIE["USER"];
        $_SESSION['LAST_ACTIVITY'] = time();
        if (isset($_COOKIE["URL"])) {
            header("location: " . $_COOKIE["URL"]);
        }
    } else {

        session_unset();
        session_destroy();

        $login = "http://" . __FQDN . "/login";
        header("location: " . $login);
    }
}
 
$_SESSION["LAST_ACTIVITY"] = time();

$callingURL = "http://" . $_SERVER['HTTP_HOST'] . "/" . $_GET['rt'];
setcookie("URL", $callingURL, time() + 3600);

?>

