<?php

class howlate_util {

//    public static $cpanelUser = "howlate";
//    public static $cpanelPassword = "PzaLQiH9av";
//
//    public static $mysqlUser = "howlate_super";
//    public static $mysqlPassword = "NuNbHG4NQn";
    
    private static $testdomain = "fiedlerconsulting.com.au";
    
    public static function cpanelUser()
    {
        return (__DOMAIN==self::$testdomain)?"fiedlerc":"howlate";
    }
    public static function cpanelPassword() {
        return (__DOMAIN==self::$testdomain)?"PqoXLF0FRS":"PzaLQiH9av";
    }

    public static function mysqlDb() {
        return (__DOMAIN==self::$testdomain)?"fiedlerc_hldev":"howlate_main";
    }

    public static function mysqlBillingDb() {
        return (__DOMAIN==self::$testdomain)?"fiedlerc_bill":"howlate_billing";
    }

    
    public static function mysqlUser() {
        return (__DOMAIN==self::$testdomain)?"fiedlerc_super":"howlate_super";
    }
    public static function mysqlPassword() {
        return (__DOMAIN==self::$testdomain)?"Az#XlXEx~hkk":"NuNbHG4NQn";
    }
    
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
        $elem = explode(".", $pin);
        if (count($elem) != 2 or $elem[0] == '' or $elem[1] == '') {
            die("Input Error: The PIN ($pin) is not formatted correctly.  It must be of form X.Y e.g. AAADD.R");
        }
        if (!$elem[1] == self::checkdigit($elem[0])) {
            die("Input Error: The PIN ($pin) entered is not valid.  (elem0 = $elem[0] , elem1 = $elem[1]) . The check digit should be '" . self::checkdigit($elem[0]) . "'");
        }
    }

    public static function orgFromPin($pin) {
        self::validatePin($pin);
        $elem = explode(".", $pin);
        return $elem[0];
    }

    public static function idFromPin($pin) {
        self::validatePin($pin);
        $elem = explode(".", $pin);
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

    
    public static function logoURL($subd = "") {

        if (file_exists("pri/$subd/logo.png")) {
            return "/pri/$subd/logo.png";
        } else {
            return "/images/logos/logo.png";
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
    

    public static function secondsSinceMidnight($datestr, $timezone) {
        $midnight = new DateTime("00:00:00", new DateTimeZone($timezone));
        $localtime = new DateTime($datestr, new DateTimeZone($timezone));
        
        return ($localtime->format("U") - $midnight->format("U"));
    }
    
    public static function dayName($datestr, $timezone) {
        $day = new DateTime($datestr, new DateTimeZone($timezone));
        return $day->format("l");
    }
    
    //
    // method probably belongs elsewhere
    //
    public static function register($pin, $udid) {
        
        $org = self::orgFromPin($pin);
        $id = self::idFromPin($pin);       
        
        $db = new howlate_db();
        $db->register($udid, $org, $id);
        $db->trlog(TranType::DEV_REG, 'Device ' . $udid . 'registered pin ' . $pin, $org, null, $id, $udid);
              
    }
    
    public static function invite($pin, $udid, $domain) 
    {
        $org = self::orgFromPin($pin);
        $id = self::idFromPin($pin);       
        
        $db = new howlate_db();
        $prac = $db->getPractitioner($org, $id);

        $message = 'To receive lateness updates for ' . $prac->PractitionerName . ' at ' . $prac->ClinicName;
        $message .= ', click : ';
        $message .= "http://secure." . $domain . "/late/view&udid=$udid";

        howlate_sms::httpSend($org, $udid, $message);
        $db->trlog(TranType::DEV_SMS, "invited $udid using SMS gateway", $org, null, $id, $udid);
    }


    // SSL Certificate for *.how-late.com
    public static function getSSLCertificate()
    {
$crt = <<<EOD
-----BEGIN CERTIFICATE-----
MIIFUjCCBDqgAwIBAgIQOi70mLLxfvw50BzryWb//zANBgkqhkiG9w0BAQsFADCB
kDELMAkGA1UEBhMCR0IxGzAZBgNVBAgTEkdyZWF0ZXIgTWFuY2hlc3RlcjEQMA4G
A1UEBxMHU2FsZm9yZDEaMBgGA1UEChMRQ09NT0RPIENBIExpbWl0ZWQxNjA0BgNV
BAMTLUNPTU9ETyBSU0EgRG9tYWluIFZhbGlkYXRpb24gU2VjdXJlIFNlcnZlciBD
QTAeFw0xNDA3MTIwMDAwMDBaFw0xNTA3MTIyMzU5NTlaMFsxITAfBgNVBAsTGERv
bWFpbiBDb250cm9sIFZhbGlkYXRlZDEdMBsGA1UECxMUUG9zaXRpdmVTU0wgV2ls
ZGNhcmQxFzAVBgNVBAMUDiouaG93LWxhdGUuY29tMIIBIjANBgkqhkiG9w0BAQEF
AAOCAQ8AMIIBCgKCAQEA0buK3REaVs/G+894spJs8n2NPF4Y5h+NjnNkmu8Xp7DY
eswr123W9T+ZuAuwQ3Z2mS+da5g/ds2Rhx34revs2OAk8hGwBMd0GGYZv9fEfgx9
4kR8/njkQlXZQwzc0IeSpFkYD8SRmN56NrF60F7Y4QQianFx6gYeScxT/qWquAct
ZlYw/+YBS6N4PtUpC4ZpcNg2zQDc65kaeibzDJco4sOhjFU6D6V6HWhNJ7uCDIUh
HDhgsnEsQeSJvrKqv6IgdJAy2RRJO1IifBMSfPg/8REQyNTw9fpWiborX4Hmuolh
rH/1b76M/mXIV6sxeCGXoUvcu1WF9FMRtBR8z511dwIDAQABo4IB2jCCAdYwHwYD
VR0jBBgwFoAUkK9qOpRaC9iQ6hJWc99DtDoo2ucwHQYDVR0OBBYEFKuWb/2Xyx50
tRZBz4+MsuKq2NSaMA4GA1UdDwEB/wQEAwIFoDAMBgNVHRMBAf8EAjAAMB0GA1Ud
JQQWMBQGCCsGAQUFBwMBBggrBgEFBQcDAjBQBgNVHSAESTBHMDsGDCsGAQQBsjEB
AgEDBDArMCkGCCsGAQUFBwIBFh1odHRwczovL3NlY3VyZS5jb21vZG8ubmV0L0NQ
UzAIBgZngQwBAgEwVAYDVR0fBE0wSzBJoEegRYZDaHR0cDovL2NybC5jb21vZG9j
YS5jb20vQ09NT0RPUlNBRG9tYWluVmFsaWRhdGlvblNlY3VyZVNlcnZlckNBLmNy
bDCBhQYIKwYBBQUHAQEEeTB3ME8GCCsGAQUFBzAChkNodHRwOi8vY3J0LmNvbW9k
b2NhLmNvbS9DT01PRE9SU0FEb21haW5WYWxpZGF0aW9uU2VjdXJlU2VydmVyQ0Eu
Y3J0MCQGCCsGAQUFBzABhhhodHRwOi8vb2NzcC5jb21vZG9jYS5jb20wJwYDVR0R
BCAwHoIOKi5ob3ctbGF0ZS5jb22CDGhvdy1sYXRlLmNvbTANBgkqhkiG9w0BAQsF
AAOCAQEAeEOvMpPm7vCC3UI/9ekwZWrTUyCuRFlHVbFA609AHyS5lY7SxL2lGgv6
1fWyo3HjuGnI8i2J9hdL2UunIHynGGhviYMv/32/UqmpvT/QNRkEyEoI8Xd91xwv
XqenwGF4LSO0bXBfnCHdSMbd5INC0773Tlu4yor+eRVeLdob5WqaZKFRZR+69ywm
rCG64cbjMR8Z95wbEOvgxeAkewjbAk2taB7D3bBqQB+LfDJHlwdCDe814Sau8nEr
f2CVGj8R4rYgHbWf7mkn4u3oX76Q6bu4vKtQlxjALTbwP2vp5GIOI1jES3JMbD9R
Ec2JP4hnT/LFh0+kOuemIlOI+QnfHw==
-----END CERTIFICATE-----
EOD;
    return $crt;
    }
    
}

?>
