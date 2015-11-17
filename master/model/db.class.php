<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of db
 *
 * @author Alex
 */
class db {
    public static function getInstance() {
        $pdo = new PDO('mysql:host=localhost;dbname=howlate_main;charset=utf8', HowLate_Util::mysqlUser(), HowLate_Util::mysqlPassword(),
        array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        return $pdo;
    }
    
}
