<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * An object for all latenesses for a particular registered device ($udid)
 * or for a particular organisation
 *
 * @author Alex
 */
class latenesses {
    //put your code here
    
    public function getby($key, $field = 'udid') {
        $db = new howlate_db();

        
        $res = $db->getLatenesses($key, $udid);
        
    }
    
    
}
