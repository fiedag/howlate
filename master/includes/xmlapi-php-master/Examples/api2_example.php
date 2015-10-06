<?php

include '../xmlapi.php';


$ip = 'localhost';
$userid = "howlate";
$domain = "how-late.com";
$root_pass = "PzaLQiH9av";

$account = "howlate";

$xmlapi = new xmlapi($ip);
$xmlapi->password_auth($userid,$root_pass);
$xmlapi->set_output("xml");
$xmlapi->set_protocol("http");
$xmlapi->set_debug(1);


$email_account = "admin@how-late.com";
$email_domain = "how-late.com";

// https://hmc.how-late.com/includes/xmlapi-php-master/Examples/api2_example.php

//echo $xmlapi->api2_query($account, "Email", "getdiskusage", array(domain=>$email_domain, login=>$email_account) );

//echo $xmlapi->api2_query($account, "SSLInfo", "fetchinfo", array(domain=>"hmc.how-late.com"));

//echo $xmlapi->api2_query($account, "SSL", "listcrts", array(domain=>"how-late.com"));
//echo $xmlapi->api2_query($account, "SSL", "listsslitems", array(domains=>"*.how-late.com|fiedlermedical.how-late.com"));

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
        
        
//echo $xmlapi->api2_query($account, "SSL", "installssl", array(domain=>"vorinmedical.how-late.com",crt=>$crt));
echo $xmlapi->api2_query($account, "SubDomain", "addsubdomain", array( dir=>"/public_html/prod", domain=>"deleteme.how-late.com", rootdomain=>"how-late.com"));
