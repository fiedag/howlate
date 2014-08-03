<?php

class howlate_util {

    public static function tobase10($base26) {
        $base10 = 0;
        $len = strlen($base26);
        for ($x = 0; $x < $len; $x++) {
            $char = substr($base26, $len - $x - 1, 1);
            if ($char == 'Z') {
                $dig = 0;
            } else {
                $dig = ord($char) - 64;
            }
            //echo "Digit (from right): " . $dig . "<br>";
            $base10 = $base10 + $dig * pow(26, $x);
        }
        return $base10;
    }

    public static function tobase26($base10) {
        if ($base10 == 0) {
            return 'Z';
        } else {
            $rem = $base10 % 26;
            if ($rem == 0) {
                $res = 'Z';
            } else {
                $res = chr($rem + 64);
            }
            $intval = intval($base10 / 26);
            return (($intval == 0) ? '' : self::tobase26($intval)) . $res;
        }
    }

    public static function checkdigit($str) {
        $len = strlen($str);
        //echo "strlen = $len <br>";
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $asc = ord(substr($str, $len - $i - 1, 1));
            //echo "asc = $asc <br>";
            switch ($i) {
                case 0:
                    $num = $num + $asc;
                    break;
                case 1:
                    $num = $num + 3 * $asc;
                    break;
                case 2:
                    $num = $num + 7 * $asc;
                    break;
                case 3:
                    $num = $num + 17 * $asc;
                    break;
            }
            //echo "num = $num <br>";
        }
        return chr(65 + $num % 26);
    }

    public static function validatePin($pin) {
        $elem = explode('.', $pin);
        if (count($elem) != 2) {
            die('Input Error: The PIN is not formatted correctly.  It must be of form X.Y e.g. ABD.R');
        }
        if (!$elem[1] == self::checkdigit($elem[0])) {
            die('Input Error: The PIN entered is not valid.  The check digit should be "' . self::checkdigit($elem[0]) . '"');
        }
    }

    public static function orgFromPin($pin) {
        self::validatePin($pin);
        $elem = explode('.', $pin);
        return $elem[0];
    }

    public static function idFromPin($pin) {
        self::validatePin($pin);
        $elem = explode('.', $pin);
        return $elem[1];
    }

    public static function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    
    public static function logoURL($subd) {

        if (file_exists("pri/$subd/logo.png")) {
            return "/pri/$subd/logo.png";
        } else {
            return "/pri/logo.png";
        }
    }

    
    public static function diag($str) {
        if (defined('__DIAG')) {
            echo $str . "<br>";           
        }
        
    }
    
    private function redirect($url) {
        if (headers_sent()) {
            die('<script type="text/javascript">window.location.href="' . $url . '";</script>');
        } else {
            header('Location: ' . $url);
            die();
        }
    }
    
}

?>
