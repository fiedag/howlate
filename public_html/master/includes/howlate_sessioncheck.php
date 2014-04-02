<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

if (!isset($_SESSION["USER"]) or $_SESSION["LAST_ACTIVITY"] < time() - 3600)
{
    session_unset(); 
    session_destroy();

    $login = "http://" . __FQDN . "/login";
    header("location: " . $login);
}

$_SESSION["LAST_ACTIVITY"] = time();


?>

