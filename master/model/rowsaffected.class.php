<?php

class RowsAffected {

    
    public static function zero($num_rows) {
        if ($num_rows != 0) {
            throw new Exception("Expected to update zero rows.");
        }
    }
    
}