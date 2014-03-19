<?php 

abstract class TranType {
	const CLIN_ADD   =  "CLIN_ADD";	const CLIN_ARCH  =  "CLIN_ARCH";	const CLIN_CHG   =  "CLIN_CHG";	const CLIN_DEL   =  "CLIN_DEL";
	const DEV_REG    =  "DEV_REG";	const DEV_UNREG  =  "DEV_UNREG";
	const LATE_GET   =  "LATE_GET";	const LATE_RESET =  "LATE_RESET";	const LATE_UPD   =  "LATE_UPD";
	const MISC_MISC  =  "MISC_MISC";
	const ORG_ADD    =  "ORG_ADD";	const ORG_CHG    =  "ORG_CHG";	const ORG_DEL    =  "ORG_DEL";
	const PRAC_ARCH  =  "PRAC_ARCH";const PRAC_CRE   =  "PRAC_CRE";	const PRAC_DEL   =  "PRAC_DEL";	const PRAC_DISP  =  "PRAC_DISP"; const PRAC_PLACE =  "PRAC_PLACE";
	const USER_ADD   =  "USER_ADD";	const USER_ARCH  =  "USER_ARCH";const USER_CHG   =  "USER_CHG";	const USER_SUSP  =  "USER_SUSP"; const USER_DNE = "USER_DNE"; const USER_PWE = "USER_PWE";
}

?>