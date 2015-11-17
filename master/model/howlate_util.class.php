<?php

class HowLate_Util {

//    public static $cpanelUser = "howlate";
//    public static $cpanelPassword = "PzaLQiH9av";
//
//    public static $mysqlUser = "howlate_super";
//    public static $mysqlPassword = "NuNbHG4NQn";

    
    public static function admin_orgid() {
        return 'CCCTV';
    }

    public static function admin_sms() {
        return "61403569377";
    }

    public static function admin_email() {
        return "admin@how-late.com";
    }

    private static $testdomain = "howlate.com";

//    public static function clickatell() {
//        return (__DOMAIN == self::$testdomain) ? new MockClickatell() : new Clickatell();
//    }


    public static function cpanelUser() {
        return (__DOMAIN == self::$testdomain) ? "howlate" : "howlate";
    }

    public static function cpanelPassword() {
        return (__DOMAIN == self::$testdomain) ? "PzaLQiH9av" : "PzaLQiH9av";
    }

    public static function mysqlDb() {
        return (__DOMAIN == self::$testdomain) ? "howlate_main" : "howlate_main";
    }

    public static function mysqlBillingDb() {
        return (__DOMAIN == self::$testdomain) ? "howlate_billing" : "howlate_billing";
    }

    public static function mysqlAnalyticsDb() {
        return (__DOMAIN == self::$testdomain) ? "howlate_analytics" : "howlate_analytics";
    }

    public static function mysqlUser() {
        return (__DOMAIN == self::$testdomain) ? "howlate_super" : "howlate_super";
    }

    public static function mysqlPassword() {
        return (__DOMAIN == self::$testdomain) ? "NuNbHG4NQn" : "NuNbHG4NQn";
    }

    public static function basePath() {
        return "/home/" . self::cpanelUser() . "/public_html";
    }

    public static function masterPath() {
        return self::basePath() . "/master";
    }

    public static function noreplySmtpUsername() {
        return "noreply@" . __DOMAIN;
    }

    public static function noreplySmtpPassword() {
        return (__DOMAIN == self::$testdomain) ? "qC7MK1JnAh" : "Kh6z9z6y6c";
    }

    public static function chargeoverUsername() {
        return 'IfCopeybjKkJOwBsgdqHSRat8lh5X6zv';
    }

    public static function chargeoverPassword() {
        return 'rNjiGRbW6EfA7LC5mgo218MdHSwz4yP3';
    }

    public static function chargeoverApiUrl() {
        return 'https://how-late.chargeover.com/api/v3';
    }

    ///
    /// base 26 numbering system, whereby A = 0, B = 1, up to Z = 25, BA = 26 etc. 
    ///
    ///
    public static function tobase10($base26) {
        $base10 = 0;
        $len = strlen($base26);
        for ($x = 0; $x < $len; $x++) {
            $char = substr($base26, $len - $x - 1, 1);
            if ($char == 'A') {
                $dig = 0;
            } else {
                $dig = ord($char) - 65;
            }
            //echo "Digit (from right): " . $dig . "<br>";
            $base10 = $base10 + $dig * pow(26, $x);
        }
        return $base10;
    }

    public static function tobase26($base10) {
        if ($base10 == 0) {
            return 'A';
        } else {
            $rem = $base10 % 26;
            if ($rem == 0) {
                $res = 'A';
            } else {
                $res = chr($rem + 65);
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
            throw new Exception("Input Error: The PIN ($pin) is not formatted correctly.  It must be of form X.Y e.g. AAADD.R");
        }
        if (!$elem[1] == self::checkdigit($elem[0])) {
            throw new Exception("Input Error: The PIN ($pin) entered is not valid.  (elem0 = $elem[0] , elem1 = $elem[1]) . The check digit should be '" . self::checkdigit($elem[0]) . "'");
        }
    }

    public static function toHHMMSS($seconds_since_midnight) {
        $hh = floor($seconds_since_midnight / 3600);
        $mm = floor(($seconds_since_midnight - $hh * 3600) / 60);
        $ss = $seconds_since_midnight - $hh * 3600 - $mm * 60;

        return $hh . ":" . $mm . ":" . $ss;
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

    public static function logoURL($subd = '') {
        if ($subd == '' or ! file_exists("pri/logos/$subd.png")) {
            return "/images/logos/logo.png";
        }
        return "/pri/logos/$subd.png";
    }

    public static function logoWhiteBG() {
        return "/images/logos/logo_sq_whbk.png";
    }

    public static function diag($str) {
        if (defined('__DIAG')) {
            echo $str . "<br>";
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
//    public static function register($pin, $udid) {
//
//        $org = self::orgFromPin($pin);
//        $id = self::idFromPin($pin);
//
//        $db = new howlate_db();
//        $db->register($udid, $org, $id);
//        //$db->trlog(TranType::DEV_REG, 'Device ' . $udid . ' registered pin ' . $pin, $org, null, $id, $udid);
//    }

    // SSL Certificate for *.how-late.com
    public static function getSSLCertificate() {
        if (__DOMAIN == self::$testdomain) {
            $crt = <<<EOD
-----BEGIN CERTIFICATE-----
MIIESzCCAzOgAwIBAgIFARQgvXEwDQYJKoZIhvcNAQEFBQAwgb0xCzAJBgNVBAgM
AlNBMSwwKgYJKoZIhvcNAQkBFh1hbGV4QGZpZWRsZXJjb25zdWx0aW5nLmNvbS5h
dTELMAkGA1UEBhMCQVUxIzAhBgNVBAMMGiouZmllZGxlcmNvbnN1bHRpbmcuY29t
LmF1MRQwEgYDVQQHDAtCZXVsYWggUGFyazEbMBkGA1UECwwSRmllZGxlciBDb25z
dWx0aW5nMRswGQYDVQQKDBJGaWVkbGVyIENvbnN1bHRpbmcwHhcNMTQwODI5MDQx
NjQ5WhcNMTUwODI5MDQxNjQ5WjCBvTELMAkGA1UECAwCU0ExLDAqBgkqhkiG9w0B
CQEWHWFsZXhAZmllZGxlcmNvbnN1bHRpbmcuY29tLmF1MQswCQYDVQQGEwJBVTEj
MCEGA1UEAwwaKi5maWVkbGVyY29uc3VsdGluZy5jb20uYXUxFDASBgNVBAcMC0Jl
dWxhaCBQYXJrMRswGQYDVQQLDBJGaWVkbGVyIENvbnN1bHRpbmcxGzAZBgNVBAoM
EkZpZWRsZXIgQ29uc3VsdGluZzCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoC
ggEBAMaZ1K8HiKub2e3s6929Es9PCrGPEhIlQb7k6/I7m8ddiUI1nL/dk3kRsmka
ZNVH78k5x+KequIbwSr3MrBS/Pu1IJY2yU1sylnz8yjn9T6b63dMbb7FMplCGHkz
MLU2iID/An0hGncekqQiKiOYc9MfXzZygPM0y9XuTZ9UJ9uDWP+khBeHcrl0KJpT
glok1slTCnuNpfK0ZYulmgKRgWa7nLYuT/V34f1Of0g+Aky6xxBgvtXRoJcRoV9m
tv7xF9Z3zuYTXuLVweBklpRikEYXCq7OVDOnH9ufnesXlm5tx3Ufp1LhoQ8og80Y
DMFRSIm/RUSYg9AaJ4xsBufdR58CAwEAAaNQME4wHQYDVR0OBBYEFEb/en3oKQQQ
3Qv7zhJijV/KewEmMB8GA1UdIwQYMBaAFEb/en3oKQQQ3Qv7zhJijV/KewEmMAwG
A1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADggEBAMCbJGabGdBtqtlXmz/I8v1a
BJtHs888toJP3nVvgHSNbkb4l7e3acqqTVQ7I8xN734Uy2g/1Z5r39QQrqLe341D
cSYAwLdnWDEnjW5WFSUU9RbO6AvI/xjeLcDI33deIUAOYv2ubLwGT+0k4cnnsdSm
fFmyWdmdZLpfJKDS+xww0t+GgAa+mr92NtvQI//km5LAsQ73LiPS5xe5GlyZIjOr
3J9scGjP3nl486IODAkyR9sZeNXOIuvp2P4jOSddI2UBPZK2QEQN9sKp9fi/BMc2
2ByNXGmeylCtYGmcCeY6cuCROiM/TtR37NzZsEV1rdg+OkTqCa+X7ohiYtUF4q0=
-----END CERTIFICATE-----
EOD;
        } else {  // how-late real certificate exp Jul 2016
            $crt = <<<EOD
-----BEGIN CERTIFICATE-----
MIIFUjCCBDqgAwIBAgIRANQ1oUNKCJsaQlftNyFn7s8wDQYJKoZIhvcNAQELBQAw
gZAxCzAJBgNVBAYTAkdCMRswGQYDVQQIExJHcmVhdGVyIE1hbmNoZXN0ZXIxEDAO
BgNVBAcTB1NhbGZvcmQxGjAYBgNVBAoTEUNPTU9ETyBDQSBMaW1pdGVkMTYwNAYD
VQQDEy1DT01PRE8gUlNBIERvbWFpbiBWYWxpZGF0aW9uIFNlY3VyZSBTZXJ2ZXIg
Q0EwHhcNMTUwNzEyMDAwMDAwWhcNMTYwNzExMjM1OTU5WjBbMSEwHwYDVQQLExhE
b21haW4gQ29udHJvbCBWYWxpZGF0ZWQxHTAbBgNVBAsTFFBvc2l0aXZlU1NMIFdp
bGRjYXJkMRcwFQYDVQQDDA4qLmhvdy1sYXRlLmNvbTCCASIwDQYJKoZIhvcNAQEB
BQADggEPADCCAQoCggEBAMP1aQ5rhSTlmfZmwAs3EMYp8mY1y4LUuyXWL63KVzP/
80P4FFMVkXZT8WRzeiiDAedtm+n7w3N3fZPooj2Ty6lz3/SXyj0pphSA7o4W2+kN
f+Sld+qg1n1crz2mmdw3+BU/OSi1qD/3rGd/7yObA4cgkL2xzo6jsapBUM2plMSh
HhGq0z6pOOjhNnaUuRUg9pC8vP1VyWiF+8V2wCjy/SzIkOLke74qGyjo7wUEQWNp
IoRZfKdmNUGWtHyIP9aQVZGG6O+99eqY9nW4CVTeHfgMi7Qvl4eL0cQAt6o9jeho
Pq3CbCkaal/OVUcIEMlegZqBrdG0bWHFuw1u+T1qwO8CAwEAAaOCAdkwggHVMB8G
A1UdIwQYMBaAFJCvajqUWgvYkOoSVnPfQ7Q6KNrnMB0GA1UdDgQWBBSjEG0EwZmL
oUw6I+d9GODQf60Y+TAOBgNVHQ8BAf8EBAMCBaAwDAYDVR0TAQH/BAIwADAdBgNV
HSUEFjAUBggrBgEFBQcDAQYIKwYBBQUHAwIwTwYDVR0gBEgwRjA6BgsrBgEEAbIx
AQICBzArMCkGCCsGAQUFBwIBFh1odHRwczovL3NlY3VyZS5jb21vZG8uY29tL0NQ
UzAIBgZngQwBAgEwVAYDVR0fBE0wSzBJoEegRYZDaHR0cDovL2NybC5jb21vZG9j
YS5jb20vQ09NT0RPUlNBRG9tYWluVmFsaWRhdGlvblNlY3VyZVNlcnZlckNBLmNy
bDCBhQYIKwYBBQUHAQEEeTB3ME8GCCsGAQUFBzAChkNodHRwOi8vY3J0LmNvbW9k
b2NhLmNvbS9DT01PRE9SU0FEb21haW5WYWxpZGF0aW9uU2VjdXJlU2VydmVyQ0Eu
Y3J0MCQGCCsGAQUFBzABhhhodHRwOi8vb2NzcC5jb21vZG9jYS5jb20wJwYDVR0R
BCAwHoIOKi5ob3ctbGF0ZS5jb22CDGhvdy1sYXRlLmNvbTANBgkqhkiG9w0BAQsF
AAOCAQEAZhUcZsPYDH+pmOaSGJnSyrbPcBOH/yfWV747qGniogXPHUeDEdc4nJrz
sz1UOnignno3GQ6SSP4Pv/8Ib3vc3y62dKwYanhvP0Dte4mmLQmF7fdzlpQ+ohM7
pUiaJeaEqm6r5McyFHYA8d7fO0kNURsO9YqDG0CsC18LqoITFuhK2Z6iseoykL5a
HkBWD9Jk/ObCrHsSr/InKILCu042djv+pa5Ub8XY3PUBFC20Fn4JySt1drMEPzBr
OD3NCsAoGMMi95R1U5hi/cmE3PT9DfIdo4EZ2jlxodL/Vo4Qnr0Py0VL9xVWRwRF
Yvi1r8pb+x36zTeKwOqcnkWzaPKikw==
-----END CERTIFICATE-----                    
EOD;
        }
        return $crt;
    }

    public static function googleMapsURL($address1, $address2, $city, $zip) {
        $str = "http://maps.google.com/maps?q=$address1";
        if ($clin->Address2 != '') {
            $str .= "+$address2";
        }
        if ($clin->City != '') {
            $str .= "+$city";
        }
        if ($clin->Zip != '') {
            $str .= "+$zip";
        }

        return $str;
    }

    //
    // Timezones which have lateness perhaps needing to be cleaned up
    //
    public static function getLateTimezones() {
        $q = "SELECT DISTINCT Timezone FROM vwLateTZ";

        $timezones = array();
        if ($result = MainDb::getInstance()->query($q)) {
            $tempArray = array();
            while ($row = $result->fetch_object()) {
                $tempArray = $row;
                array_push($timezones, $tempArray);
            }
            return $timezones;
        }
        $result->close();
    }

    /// for a given timezone, get all those latenesses joined to any session times which exist
    public static function getLatesAndSessions($timezone, $day, $time) {
        // $day is Monday, Tuesday etc.
        // $time is in seconds since midnight
        $q = " SELECT v.*, s.Day, IFNULL(s.StartTime, -1) As StartTime, IFNULL(s.EndTime,-1) As EndTime " .
                " FROM vwLateTZ v " .
                " LEFT OUTER JOIN sessions s on s.OrgID = v.OrgID and s.ID = v.ID and (Day = '$day' or Day = 'All')" .
                " WHERE v.Timezone = '$timezone'";

        $toprocess = array();
        if ($result = MainDb::getInstance()->query($q)) {
            $tempArray = array();
            while ($row = $result->fetch_object()) {
                $tempArray = $row;
                array_push($toprocess, $tempArray);
            }
            return $toprocess;
        }
    }

    public static function deleteOldLates() {
        $q = "DELETE FROM lates WHERE Updated < ADDTIME(NOW(),'-08:00:00')";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
    }

//    public static function getQueuedNotifications() {
//        $q = "SELECT * FROM notifqueue WHERE Status = 'Queued'";
//        $toprocess = array();
//        if ($result = MainDb::getInstance()->query($q)) {
//            $tempArray = array();
//            while ($row = $result->fetch_object()) {
//                $tempArray = $row;
//                array_push($toprocess, $tempArray);
//            }
//            return $toprocess;
//        }
//    }
//
//    public static function dequeueNotification($uid) {
//        $q = "UPDATE notifqueue SET Status = 'Sent' WHERE UID = $uid";
//        //echo $q;
//        $stmt = MainDb::getInstance()->prepare($q);
//        $stmt->execute();
//        if ($stmt->affected_rows == 0) {
//            trigger_error("The notification record was not deueued, error= " . $this->conn->error, E_USER_ERROR);
//        }
//    }

    public static function deleteSubdomain($subdomain) {
        $q = "CALL sp_DeleteSubd('" . $subdomain . "')";
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->execute() or trigger_error('# Query Error (' . $this->conn->errno . ') ' . $this->conn->error, E_USER_ERROR);
    }

    public static function to_xudid($udid) {
        $len = strlen($udid);
        $even = "";
        $odd = "";
        for ($i = 0; $i < $len; $i++) {
            if ($i % 2 == 0)
                $even .= $udid[$i];
            else
                $odd .= $udid[$i];
        }
        return $even . $odd;
    }

    public static function to_udid($xudid) {
        echo "xudid=$xudid";

        $len = strlen($xudid);
        $half = round($len / 2, 0, PHP_ROUND_HALF_UP);


        $even = substr($xudid, 0, $half);
        $odd = substr($xudid, $half);

        $res = "";

        for ($i = 0; $i < $half; $i++) {
            $res .= ($i < strlen($even) - 1) ? $even[$i] : "" . ($i < strlen($odd) - 1) ? $odd[$i] : "";
        }
        return $res;
    }

    public static function array_sort($array, $on, $order = SORT_ASC) {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

}

?>
