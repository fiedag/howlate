<?php 

abstract class TranType {
	const CLIN_ADD   =  "CLIN_ADD";	
        const CLIN_ARCH  =  "CLIN_ARCH";	
        const CLIN_CHG   =  "CLIN_CHG";	
        const CLIN_DEL   =  "CLIN_DEL";
	const DEV_REG    =  "DEV_REG";	
        const DEV_UNREG  =  "DEV_UNREG";    
        const DEV_SMS = "DEV_SMS";
	const LATE_GET   =  "LATE_GET";	
        const LATE_RESET =  "LATE_RESET";	
        const LATE_UPD   =  "LATE_UPD";
	const MISC_MISC  =  "MISC_MISC"; 
        const SESS_UPD = "SESS_UPD"; 
        const QUE_NOTIF = "QUE_NOTIF";
        const APTYPE_UPD = "APTYP_UPD";
	const ORG_ADD    =  "ORG_ADD";	
        const ORG_CHG    =  "ORG_CHG";	
        const ORG_DEL    =  "ORG_DEL";
	const PRAC_ARCH  =  "PRAC_ARCH";
        const PRAC_CRE   =  "PRAC_CRE";	
        const PRAC_DEL   =  "PRAC_DEL";	
        const PRAC_DISP  =  "PRAC_DISP"; 
        const PRAC_PLACE =  "PRAC_PLACE";
	const USER_ADD   =  "USER_ADD";
        const USER_ARCH  =  "USER_ARCH";
        const USER_CHG   =  "USER_CHG";	
        const USER_SUSP  =  "USER_SUSP";
        const USER_DNE = "USER_DNE"; 
        const USER_PWE = "USER_PWE";
        const AGT_START = "AGT_START"; 
        const AGT_STOP = "AGT_STOP"; 
        const AGT_ERROR = "AGT_ERROR"; 
        const AGT_INFO = "AGT_INFO";
        const AGT_APPT = "AGT_APPT";
}


?>