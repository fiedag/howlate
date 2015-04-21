
-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 21, 2015 at 05:59 PM
-- Server version: 5.5.42-cll
-- PHP Version: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: 'howlate_main'
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `sp_AgentLateUpd`$$
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_AgentLateUpd`(IN `inOrgID` CHAR(6), IN `inID` CHAR(2), IN inMinutes decimal)
    MODIFIES SQL DATA
BEGIN

  select Sticky into @vSticky
  from lates
  where OrgID = inOrgID and ID = inID;
  IF @vSticky is null or @vSticky = 0 THEN
    replace into lates (OrgID, ID, Minutes, RealMinutes, Sticky) VALUES (inOrgID, inID, inMinutes, inMinutes, 0);
  END IF;
END$$

DROP PROCEDURE IF EXISTS `sp_CreateClinicIntegration`$$
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_CreateClinicIntegration`(IN `inOrgID` CHAR(5), IN `inClinicID` BIGINT)
    MODIFIES SQL DATA
begin
  INSERT INTO clinicintegration(OrgID, ClinicID, Instance, DbName, UID, PWD, PollInterval, HLUserID)
  SELECT c.OrgID, c.ClinicID, '.\\BPSInstance','BPSPatients','BPSViewer', 'passwordhere','120', MIN(u.UserID)
  FROM clinics c, orgusers u
  WHERE NOT EXISTS(SELECT 1 FROM clinicintegration WHERE OrgID = inOrgID and ClinicID = inClinicID)
  AND c.OrgID = inOrgID and c.ClinicID = inClinicID
  AND u.OrgID = c.OrgID
  GROUP BY c.OrgID, c.ClinicID, '.BPSInstance','BPSPatients','BBSViewer', 'passwordhere','120';

  SELECT * FROM clinicintegration
  WHERE OrgID = inOrgID AND ClinicID = inClinicID;
  
end$$

DROP PROCEDURE IF EXISTS `sp_CreatePractitioner`$$
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_CreatePractitioner`(IN `inOrgID` CHAR(6), IN `inClinicID` INT, IN `inFullName` VARCHAR(50))
    MODIFIES SQL DATA
BEGIN
  declare surr bigint;
  declare integr varchar(50);
  declare nextID varchar(2);
  declare fullName varchar(100);


  select ID into @vID
  from practitioners
  where OrgID = inOrgID
  and FullName = inFullName;
  
  if @vID is null or @vID = '' then
    SET nextID = getNextPractitionerID2(inOrgID);

    insert into practitioners (OrgID, ID, FirstName, LastName, FullName, AbbrevName, NotificationThreshold, LateToNearest, LatenessOffset)
    values (inOrgID, nextID, '', '', inFullName, inFullName, 25, 5, 10);
    
    if inClinicID is null or inClinicID = '' then
      SELECT MIN(ClinicID) into @assignedClinic FROM clinics WHERE OrgID = inOrgID;
    else
      SET @assignedClinic = inClinicID;
    end if;


    insert into placements (OrgID, ID, ClinicID) SELECT inOrgID, nextID, @assignedClinic;
    
end if;
  
END$$

DROP PROCEDURE IF EXISTS `sp_CreatePractitioner_prev`$$
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_CreatePractitioner_prev`(IN `inOrgID` CHAR(6), IN `inFullName` VARCHAR(50))
    MODIFIES SQL DATA
BEGIN
  declare surr bigint;
  declare integr varchar(50);
  declare nextID varchar(2);
  declare fullName varchar(100);

  select ID into @vID
  from practitioners
  where OrgID = inOrgID
  and FullName = inFullName;
  
  if @vID is null or @vID = '' then
    SET nextID = getNextPractitionerID2(inOrgID);

    insert into practitioners (OrgID, ID, FirstName, LastName, FullName, AbbrevName, NotificationThreshold, LateToNearest, LatenessOffset)
    values (inOrgID, nextID, '', '', inFullName, inFullName, 25, 5, 10);
    
    SELECT MIN(ClinicID) into @defaultClinic FROM clinics WHERE OrgID = inOrgID;
    
    insert into placements (OrgID, ID, ClinicID) SELECT inOrgID, nextID, @defaultClinic;
    
end if;
  
END$$

DROP PROCEDURE IF EXISTS `sp_DeleteOrg`$$
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_DeleteOrg`(IN `inOrgID` CHAR(6))
    MODIFIES SQL DATA
begin
  delete from transactionlog where OrgID = inOrgID;
  delete from lates where OrgID = inOrgID;
  delete from resetrequests where OrgID = inOrgID;
  delete from placements where OrgID = inOrgID;
  delete from practitioners where OrgID = inOrgID;
  delete from devicereg where OrgID = inOrgID;
  delete from clinics where OrgID = inOrgID;
  delete from orgusers where OrgID = inOrgID;
  delete from orgs where OrgID = inOrgID;
end$$

DROP PROCEDURE IF EXISTS `sp_DeleteSubd`$$
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_DeleteSubd`(IN `inSubd` VARCHAR(60))
    MODIFIES SQL DATA
begin
  select OrgID into @vOrgID
  from orgs
  where Subdomain = inSubd;
  
  delete from transactionlog where OrgID = @vOrgID;
  delete from lates where OrgID = @vOrgID;
  delete from resetrequests where OrgID = @vOrgID;
  delete from placements where OrgID = @vOrgID;
  delete from practitioners where OrgID = @vOrgID;
  delete from devicereg where OrgID = @vOrgID;
  delete from clinics where OrgID = @vOrgID;
  delete from orgusers where OrgID = @vOrgID;
  delete from orgs where OrgID = @vOrgID;
end$$

DROP PROCEDURE IF EXISTS `sp_EnqueueNotification`$$
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_EnqueueNotification`(IN `inOrgID` CHAR(6), IN `inClinicID` SMALLINT(2), IN `inPractitionerID` CHAR(2), IN `inMobilePhone` VARCHAR(24), IN `inMessage` VARCHAR(256))
proc_label:begin
  
  
  SELECT MAX(Created) INTO @vLastNotif
  FROM notifqueue
  WHERE OrgID = inOrgID
  AND PractitionerID = inPractitionerID
  AND ClinicID = inClinicID
  AND MobilePhone = inMobilePhone;
  
  IF DATE(@vLastNotif) = CURDATE() THEN
    -- already notified today
    leave proc_label;
  END IF;


  SELECT SuppressNotifications INTO @vSuppress
  FROM clinics
  WHERE OrgID = inOrgID
  AND ClinicID = inClinicID;
  
  IF @vSuppress = 1 THEN
    leave proc_label;
  END IF;
  
  /*  superfluous, do in PHP
  SELECT COUNT(0) INTO @placed
  FROM placements
  WHERE OrgID = inOrgID
  AND ClinicID = inClinicID
  AND ID = inPractitionerID;

  IF @placed = 0 THEN
    leave proc_label;
  END IF;
  */
  
  INSERT INTO notifqueue (OrgID, ClinicID, PractitionerID, MobilePhone, Created, Message, Status)
  VALUES (inOrgID, inClinicID, inPractitionerID, inMobilePhone, NOW(), inMessage, 'Queued');
  

end$$

DROP PROCEDURE IF EXISTS `sp_LateUpd`$$
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_LateUpd`(IN `inOrgID` CHAR(6), IN `inID` CHAR(2), IN inMinutes decimal, IN inSticky bit)
    MODIFIES SQL DATA
BEGIN

  select Sticky into @vSticky
  from lates
  where OrgID = inOrgID and ID = inID;
  IF @vSticky is null or @vSticky = 0 or inSticky = 1 THEN
    replace into lates (OrgID, ID, Minutes, RealMinutes, Sticky) VALUES (inOrgID, inID, inMinutes, inMinutes, inSticky);
  END IF;
  
  
END$$

--
-- Functions
--
DROP FUNCTION IF EXISTS `getHHMMSS_DeleteMe`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getHHMMSS_DeleteMe`(`inSecondsSinceMidnight` INT) RETURNS varchar(8) CHARSET latin1
    NO SQL
begin

  declare hh varchar(2);
  declare mm varchar(2);
  declare ss varchar(2);
  
  set hh = cast(truncate(inSecondsSinceMidnight / 3600,0) as char);
  if length(hh) < 2 then
    set hh = concat('0',hh);
  end if;
  
  set mm = cast(truncate(MOD(inSecondsSinceMidnight,3600),0) as char);
  if length(mm) < 2 then
    set mm = concat('0',mm);
  end if;
  
  return concat(hh,":",mm,":",ss);
  
  
end$$

DROP FUNCTION IF EXISTS `getHrsMins2_DeleteMe`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getHrsMins2_DeleteMe`(`inMinutes` INT, `inOrgID` VARCHAR(5), `inPractID` VARCHAR(3)) RETURNS char(60) CHARSET latin1
begin
  declare result char(60);
  declare display int;
  
  declare hr_word char(5);
  declare min_word char(7);


  select IF(LateToNearest = 0, 5, IFNULL(LateToNearest,5)), IF(LatenessOffset = 0, 5, IFNULL(LatenessOffset,5))
  into @latetonearest, @latenessoffset
  from practitioners
  where OrgID = inOrgID
  and ID = inPractID;
  
  set display = round( inMinutes / @latetonearest, 0) * @latetonearest - @latenessoffset;
  if display < 0 then 
    set display = 0;
  end if;

  if display < 60 then
    set result = concat(display, " minutes");
  else
    if display < 120 then
      set hr_word = "hour";
    else
      set hr_word = "hours";
    end if;

    if display % 60 = 1 then
      set min_word = "min";
    else
      set min_word = "min";
    end if;

    if display % 60 = 0 then
      set result = concat(display DIV 60, " ", hr_word);
    else
      set result = concat(display DIV 60, " ", hr_word, " ", display % 60, " ", min_word);
    end if;
  end if;

  return result;

end$$

DROP FUNCTION IF EXISTS `getHrsMins3_DeleteMe`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getHrsMins3_DeleteMe`(`inMinutes` INT, `inLateToNearest` INT, `inLatenessOffset` INT) RETURNS char(60) CHARSET latin1
begin
  declare result char(60);
  declare display int;
  
  declare hr_word char(5);
  declare min_word char(7);

  set inMinutes = IFNULL(inMinutes,0);
  set inLateToNearest = IFNULL(inLateToNearest,5);
  if inLateToNearest = 0 then
    set inLateToNearest = 5;
  end if;
  
  set inLatenessOffset = IFNULL(inLatenessOffset, 0);
  
  set display = round( inMinutes / inLateToNearest, 0) * inLateToNearest;
  set display = display - inLatenessOffset;

  if display < 60 then
    set result = concat(display, " minutes");
  else
    if display < 120 then
      set hr_word = "hour";
    else
      set hr_word = "hours";
    end if;

    if display % 60 = 1 then
      set min_word = "min";
    else
      set min_word = "min";
    end if;

    if display % 60 = 0 then
      set result = concat(display DIV 60, " ", hr_word);
    else
      set result = concat(display DIV 60, " ", hr_word, " ", display % 60, " ", min_word);
    end if;
  end if;

  return result;

end$$

DROP FUNCTION IF EXISTS `getHrsMins_DeleteMe`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getHrsMins_DeleteMe`(`inMinutes` INT) RETURNS char(60) CHARSET latin1
begin
  declare result char(60);

  declare hr_word char(5);
  declare min_word char(7);

  if inMinutes < 60 then
    set result = concat(inMinutes, " minutes");
  else
    if inMinutes < 120 then
      set hr_word = "hour";
    else
      set hr_word = "hours";
    end if;

    if inMinutes % 60 = 1 then
      set min_word = "minute";
    else
      set min_word = "minutes";
    end if;

    if inMinutes % 60 = 0 then
      set result = concat(inMinutes DIV 60, " ", hr_word);
    else
      set result = concat(inMinutes DIV 60, " ", hr_word, " ", inMinutes % 60, " ", min_word);
    end if;
  end if;

  return result;

end$$

DROP FUNCTION IF EXISTS `getIDToNum`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getIDToNum`(`ID` VARCHAR(5)) RETURNS int(11)
    READS SQL DATA
begin
  declare x int;
  declare res int;
  set res = 0;
  set x = 0;
  while x <= length(ID) do
    set res =  26 * res + ascii(mid(ID,x,1));
    
    set x = x + 1;
  end while;
  return res;

end$$

DROP FUNCTION IF EXISTS `getMinutesLateMsg`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getMinutesLateMsg`(`inMinutes` INT, `inOrgID` VARCHAR(5), `inPractID` VARCHAR(3)) RETURNS char(60) CHARSET latin1
begin
  declare result char(60);
  declare display int;  
  declare hr_word char(5);
  declare min_word char(7);

  if inMinutes = 0 then
    return "on time";
  end if;

  select NotificationThreshold, IF(LateToNearest = 0, 5, IFNULL(LateToNearest,5)), IFNULL(LatenessOffset,0), LatenessCeiling
  into @threshold, @latetonearest, @latenessoffset, @latenessceiling
  from practitioners
  where OrgID = inOrgID
  and ID = inPractID;

  if inMinutes < @threshold then
    return "on time";
  end if;

  set display = inMinutes - @latenessoffset;
  
  if display <= 0 then
    return "on time";
  end if;
  
  set display = round( display / @latetonearest, 0) * @latetonearest;
  if display < 0 then 
    set display = 0;
  end if;

  if display = 0 then
    return "on time";
  end if;


  if @latenessceiling > 0 and display > @latenessceiling then
    set display = @latenessceiling;
  end if;

  if display < 60 then
    set result = concat(display, " minutes");
  else
    if display < 120 then
      set hr_word = "hour";
    else
      set hr_word = "hours";
    end if;

    if display % 60 = 1 then
      set min_word = "min";
    else
      set min_word = "mins";
    end if;

    if display % 60 = 0 then
      set result = concat(display DIV 60, " ", hr_word);
    else
      set result = concat(display DIV 60, " ", hr_word, " ", display % 60, " ", min_word);
    end if;
  end if;


  return concat(result," late");

end$$

DROP FUNCTION IF EXISTS `getMinutesLateMsg_works`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getMinutesLateMsg_works`(`inMinutes` INT, `inOrgID` VARCHAR(5), `inPractID` VARCHAR(3)) RETURNS char(60) CHARSET latin1
begin
  declare result char(60);
  declare display int;  
  declare hr_word char(5);
  declare min_word char(7);

  if inMinutes = 0 then
    return "on time";
  end if;
  
  select NotificationThreshold, IF(LateToNearest = 0, 5, IFNULL(LateToNearest,5)), IFNULL(LatenessOffset,0), LatenessCeiling
  into @threshold, @latetonearest, @latenessoffset, @latenessceiling
  from practitioners
  where OrgID = inOrgID
  and ID = inPractID;

  if inMinutes < @threshold then
    return "on time";
  end if;

  
  set display = round( inMinutes / @latetonearest, 0) * @latetonearest - @latenessoffset;
  if display < 0 then 
    set display = 0;
  end if;

  if display = 0 then
    return "on time";
  end if;


  if @latenessceiling > 0 and display > @latenessceiling then
    set display = @latenessceiling;
  end if;

  if display < 60 then
    set result = concat(display, " minutes");
  else
    if display < 120 then
      set hr_word = "hour";
    else
      set hr_word = "hours";
    end if;

    if display % 60 = 1 then
      set min_word = "min";
    else
      set min_word = "mins";
    end if;

    if display % 60 = 0 then
      set result = concat(display DIV 60, " ", hr_word);
    else
      set result = concat(display DIV 60, " ", hr_word, " ", display % 60, " ", min_word);
    end if;
  end if;


  return concat(result," late");

end$$

DROP FUNCTION IF EXISTS `getNextPractitionerID2`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getNextPractitionerID2`(`inOrgID` CHAR(6)) RETURNS char(2) CHARSET latin1
    COMMENT 'Increments base26 number from A (1) through YY (650) w. Z = 0'
begin
  declare highest char(2);
  declare vAscii int;
  declare digit1 char(1);
  declare digit2 char(1);
  
  select ID into highest FROM practitioners 
  where OrgID = inOrgID and getIDToNum(ID) = (
    select MAX(getIDToNum(ID))
    from practitioners where OrgID = inOrgID);

  if highest IS NULL then
    return 'A';
  end if;

  if highest = 'Z' then
    return 'AA';
  end if;

  if length(highest) = 1 then
    set vAscii = ascii(highest);
    set vAscii = vAscii + 1;
    return char(vAscii);
  end if;

  -- have two digits
  set digit1 = right(highest,1);
  set digit2 = left(highest,1);
  if digit1 = 'Z' then
      set digit2 = char(ascii(digit2) + 1);
      set digit1 = 'A';
  else
      set digit1 = char(ascii(digit1) + 1);
  end if;

  return concat(digit2,digit1);
end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table 'clinicintegration'
--

DROP TABLE IF EXISTS clinicintegration;
CREATE TABLE IF NOT EXISTS clinicintegration (
  OrgID char(5) NOT NULL,
  ClinicID bigint(20) NOT NULL,
  Instance varchar(30) NOT NULL,
  PMSystem int(11) NOT NULL COMMENT 'Practice Management System',
  ConnectionType varchar(20) NOT NULL COMMENT 'Sql Native Client or DSN',
  ConnectionString varchar(400) NOT NULL,
  DbName varchar(20) NOT NULL,
  UID varchar(20) NOT NULL,
  PWD varchar(20) NOT NULL,
  PollInterval int(11) NOT NULL,
  HLUserID varchar(20) NOT NULL COMMENT 'The Howlate userid to use for connecting.',
  ProcessRecalls tinyint(1) NOT NULL,
  UNIQUE KEY OrgID (OrgID,ClinicID)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table 'clinics'
--

DROP TABLE IF EXISTS clinics;
CREATE TABLE IF NOT EXISTS clinics (
  ClinicID int(6) NOT NULL AUTO_INCREMENT,
  OrgID char(5) NOT NULL,
  ClinicName varchar(40) NOT NULL COMMENT 'Must fit on iPhone screen hence short',
  Phone varchar(25) NOT NULL,
  Address1 varchar(50) NOT NULL,
  Address2 varchar(50) NOT NULL,
  City varchar(50) NOT NULL,
  Zip varchar(6) NOT NULL,
  State varchar(50) NOT NULL,
  Country varchar(50) NOT NULL,
  Timezone varchar(36) NOT NULL DEFAULT 'Australia/Adelaide',
  PatientReply tinyint(1) NOT NULL COMMENT 'A small envelope appears next to Dr permitting a reply to the surgery',
  ReplyRecip varchar(60) NOT NULL COMMENT 'Email address of message recipient',
  SuppressNotifications tinyint(1) NOT NULL COMMENT 'Check if notifications are toi be suppressed',
  AllowMessage tinyint(1) NOT NULL,
  MsgRecip varchar(50) NOT NULL,
  UNIQUE KEY ClinicID (ClinicID),
  KEY Timezone (Timezone),
  KEY TZ (Timezone)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=135 ;

-- --------------------------------------------------------

--
-- Table structure for table 'country'
--

DROP TABLE IF EXISTS country;
CREATE TABLE IF NOT EXISTS country (
  Id int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL,
  MobilePrefix varchar(4) NOT NULL COMMENT 'e.g. 61 for Australia, 1 for USA',
  PRIMARY KEY (Id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table 'devicereg'
--

DROP TABLE IF EXISTS devicereg;
CREATE TABLE IF NOT EXISTS devicereg (
  UDID varchar(40) NOT NULL COMMENT 'Unique Device ID of the smartphone or tablet.',
  OrgID char(5) NOT NULL COMMENT 'The Organisation ID',
  ID char(2) NOT NULL COMMENT 'The Practitioner ID',
  Created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Expires timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  UniqueID bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (UniqueID),
  UNIQUE KEY UDID (UDID,OrgID,ID),
  UNIQUE KEY UDID_2 (UDID,OrgID,ID),
  UNIQUE KEY UniqueID (UniqueID),
  KEY OrgID (OrgID)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Stores which devices have registered interest in which docto' AUTO_INCREMENT=170209 ;

-- --------------------------------------------------------

--
-- Table structure for table 'errorlog'
--

DROP TABLE IF EXISTS errorlog;
CREATE TABLE IF NOT EXISTS errorlog (
  Id bigint(20) NOT NULL AUTO_INCREMENT,
  Created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ErrLevel tinyint(4) NOT NULL COMMENT '1 = E_USER_ERROR,2 = E_USER_WARN,4 = E_USER_NOTICE',
  ErrType int(11) NOT NULL COMMENT '1 = App Error,2 = API Error,4=Data Error, 0 = Other Error',
  `File` varchar(80) NOT NULL COMMENT 'The file the error occured in',
  Line int(5) NOT NULL COMMENT 'The line of the file',
  IPv4 varchar(15) DEFAULT NULL,
  ErrMessage varchar(256) NOT NULL COMMENT 'The text passed to trigger_error()',
  Trace varchar(255) NOT NULL,
  PRIMARY KEY (Id),
  UNIQUE KEY Id (Id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11547 ;

-- --------------------------------------------------------

--
-- Table structure for table 'lates'
--

DROP TABLE IF EXISTS lates;
CREATE TABLE IF NOT EXISTS lates (
  UKey bigint(20) NOT NULL AUTO_INCREMENT,
  OrgID char(5) NOT NULL COMMENT 'The Organisation ID',
  ID char(2) NOT NULL COMMENT 'The practitioner ID',
  Updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When updated',
  Minutes smallint(3) NOT NULL COMMENT 'Minutes late.  Negative is early.',
  RealMinutes smallint(3) NOT NULL COMMENT 'The actual lateness, not what is manually updated',
  Sticky tinyint(1) NOT NULL COMMENT 'If set then automatic updates do not update published lateness.',
  AgentUpdate tinyint(1) NOT NULL COMMENT 'True of being updated by Agent via API',
  PRIMARY KEY (UKey),
  UNIQUE KEY OrgID (OrgID,ID)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Lateness records' AUTO_INCREMENT=60876 ;

-- --------------------------------------------------------

--
-- Table structure for table 'notifqueue'
--

DROP TABLE IF EXISTS notifqueue;
CREATE TABLE IF NOT EXISTS notifqueue (
  UID bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Unique key',
  OrgID char(5) NOT NULL,
  ClinicID char(2) NOT NULL,
  PractitionerID char(2) NOT NULL,
  MobilePhone varchar(20) NOT NULL,
  Created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When created',
  Message varchar(250) NOT NULL COMMENT '250 avail but please use 150 or less',
  `Status` varchar(20) NOT NULL COMMENT '''Queued'',''Sent'',''Received'',''Failed''',
  TestMobile varchar(14) NOT NULL COMMENT 'If nonblank then use this as the destination to SMS not the MobileNumber',
  PRIMARY KEY (UID)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2309 ;

-- --------------------------------------------------------

--
-- Table structure for table 'nums'
--

DROP TABLE IF EXISTS nums;
CREATE TABLE IF NOT EXISTS nums (
  OrgID char(5) NOT NULL,
  num int(11) NOT NULL AUTO_INCREMENT,
  str varchar(20) NOT NULL,
  Practitioner char(2) NOT NULL DEFAULT '0',
  OrgID2 char(5) NOT NULL,
  PRIMARY KEY (num)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=130 ;

--
-- Triggers 'nums'
--
DROP TRIGGER IF EXISTS `trig1`;
DELIMITER //
CREATE TRIGGER `trig1` BEFORE INSERT ON `nums`
 FOR EACH ROW set new.Practitioner = getNextPractitionerID2(new.OrgID),
    new.OrgID2 = new.OrgID
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table 'orgs'
--

DROP TABLE IF EXISTS orgs;
CREATE TABLE IF NOT EXISTS orgs (
  OrgID char(5) NOT NULL COMMENT '4 character alpha ID plus a checksum character',
  OrgName varchar(50) NOT NULL COMMENT 'The registered long name.',
  OrgShortName varchar(24) NOT NULL COMMENT 'Must fit on table header on iPhone without scrolling',
  TaxID varchar(24) NOT NULL COMMENT 'The federal registration ID in the relevant country, e.g. ABN, FID.',
  Subdomain varchar(24) NOT NULL COMMENT 'word prepended to how-late.com e.g. bricc, must contain only valid characters.',
  FQDN varchar(40) NOT NULL COMMENT 'Fully qualified domain name',
  BillingContact varchar(50) NOT NULL COMMENT 'this UserID receives the invoice.',
  Address1 varchar(50) NOT NULL,
  Address2 varchar(50) NOT NULL,
  City varchar(25) NOT NULL,
  State varchar(40) NOT NULL,
  Zip varchar(7) NOT NULL,
  Country varchar(25) NOT NULL,
  Timezone varchar(36) NOT NULL COMMENT 'e.g. Australia/Adelaide',
  UpdIndic int(11) NOT NULL,
  Created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (OrgID),
  UNIQUE KEY OrgID (OrgID),
  UNIQUE KEY OrgID_2 (OrgID),
  UNIQUE KEY Subdomain (Subdomain),
  UNIQUE KEY FQDN (FQDN),
  UNIQUE KEY FQDN_2 (FQDN),
  KEY Subdomain_2 (Subdomain)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table 'orgusers'
--

DROP TABLE IF EXISTS orgusers;
CREATE TABLE IF NOT EXISTS orgusers (
  OrgID char(5) NOT NULL COMMENT 'Link to Orgs Table',
  UserID varchar(50) NOT NULL COMMENT '50 character userid.',
  FullName varchar(50) NOT NULL,
  EmailAddress varchar(50) NOT NULL COMMENT 'Used for signin.',
  XPassword varchar(200) NOT NULL COMMENT 'Encrypted password.',
  SecretQuestion1 varchar(50) NOT NULL COMMENT 'The questions may change.  Store them here',
  SecretAnswer1 varchar(50) NOT NULL COMMENT 'The answer recorded.',
  DateCreated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UID bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (UID)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=97 ;

-- --------------------------------------------------------

--
-- Table structure for table 'placements'
--

DROP TABLE IF EXISTS placements;
CREATE TABLE IF NOT EXISTS placements (
  OrgID char(5) NOT NULL COMMENT 'The Organisation ID',
  ID char(2) NOT NULL,
  ClinicID int(11) NOT NULL COMMENT 'The Clinic ID',
  SurrogKey varchar(7) NOT NULL COMMENT 'Can this surrogate key column be deleted?',
  PRIMARY KEY (OrgID,ID)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This places a practitioner at a clinic';

-- --------------------------------------------------------

--
-- Table structure for table 'pmsystems'
--

DROP TABLE IF EXISTS pmsystems;
CREATE TABLE IF NOT EXISTS pmsystems (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Company varchar(80) NOT NULL,
  `Name` varchar(50) NOT NULL,
  AnnualRevenue varchar(40) NOT NULL,
  Certification varchar(40) NOT NULL,
  Website varchar(80) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Priority int(11) NOT NULL,
  Implemented tinyint(1) NOT NULL,
  SelectSessions varchar(2000) NOT NULL,
  SelectLates varchar(2000) NOT NULL,
  SelectToNotify varchar(2000) NOT NULL,
  Agent32Bit tinyint(1) NOT NULL COMMENT 'Do we need to deploy a 32-bit agent?',
  ConnectionString varchar(400) NOT NULL COMMENT 'Example Connection string',
  ImplemNotes varchar(400) NOT NULL,
  PRIMARY KEY (ID)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=129 ;

-- --------------------------------------------------------

--
-- Table structure for table 'practitioners'
--

DROP TABLE IF EXISTS practitioners;
CREATE TABLE IF NOT EXISTS practitioners (
  OrgID char(5) NOT NULL,
  ID char(2) NOT NULL COMMENT 'Practitioner ID',
  FirstName varchar(50) NOT NULL,
  LastName varchar(50) NOT NULL,
  FullName varchar(80) NOT NULL COMMENT 'E.g. Dr A.J.K. Venkatanarasimharajuvaripeta',
  AbbrevName varchar(20) NOT NULL COMMENT 'For iPhone fit.',
  DateCreated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  SurrogKey bigint(20) NOT NULL AUTO_INCREMENT,
  NotificationThreshold int(11) NOT NULL COMMENT 'Will cause push notifications',
  LateToNearest int(11) NOT NULL COMMENT 'Report to nearest number of minutes.',
  LatenessOffset int(11) NOT NULL COMMENT 'Number of minutes to subtract from the actual lateness for display',
  LatenessCeiling int(11) NOT NULL COMMENT 'Report lateness up to this number of minutes',
  PRIMARY KEY (SurrogKey),
  UNIQUE KEY OrgID (OrgID,ID),
  KEY IntegrKey (OrgID)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3292 ;

--
-- Triggers 'practitioners'
--
DROP TRIGGER IF EXISTS `tr_del_practitioners`;
DELIMITER //
CREATE TRIGGER `tr_del_practitioners` AFTER DELETE ON `practitioners`
 FOR EACH ROW delete from placements 
where OrgID = old.OrgID
and ID = old.ID
//
DELIMITER ;
DROP TRIGGER IF EXISTS `tr_ins_practitioners`;
DELIMITER //
CREATE TRIGGER `tr_ins_practitioners` BEFORE INSERT ON `practitioners`
 FOR EACH ROW if (isnull(new.ID) or new.ID = '') then
  set new.ID = getNextPractitionerID2(new.OrgID);
end if
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table 'resetrequests'
--

DROP TABLE IF EXISTS resetrequests;
CREATE TABLE IF NOT EXISTS resetrequests (
  Token varchar(100) NOT NULL,
  EmailAddress varchar(50) NOT NULL,
  OrgID char(5) NOT NULL,
  UserID varchar(50) NOT NULL,
  DateCreated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Token)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table 'sentsms'
--

DROP TABLE IF EXISTS sentsms;
CREATE TABLE IF NOT EXISTS sentsms (
  ID bigint(20) NOT NULL AUTO_INCREMENT,
  OrgID char(5) NOT NULL,
  Created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  API varchar(18) NOT NULL,
  SessionID varchar(32) NOT NULL,
  MessageID varchar(32) NOT NULL,
  MessageText varchar(255) NOT NULL,
  Destination varchar(14) NOT NULL COMMENT 'Mobile number',
  PRIMARY KEY (ID),
  KEY OrgID (OrgID)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1864 ;

-- --------------------------------------------------------

--
-- Table structure for table 'sessions'
--

DROP TABLE IF EXISTS sessions;
CREATE TABLE IF NOT EXISTS sessions (
  OrgID varchar(5) NOT NULL COMMENT 'Organisation ID',
  ID varchar(2) NOT NULL COMMENT 'PractitionerID',
  `Day` varchar(10) NOT NULL COMMENT 'Monday-Sunday',
  StartTime int(11) NOT NULL COMMENT 'Seconds from Midnight',
  EndTime int(11) NOT NULL COMMENT 'Seconds from Midnight',
  Updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (OrgID,ID,`Day`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table 'timezones'
--

DROP TABLE IF EXISTS timezones;
CREATE TABLE IF NOT EXISTS timezones (
  CodeVal varchar(50) NOT NULL,
  CodeDesc varchar(50) NOT NULL,
  PRIMARY KEY (CodeVal),
  UNIQUE KEY CodeVal (CodeVal)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table 'transactionlog'
--

DROP TABLE IF EXISTS transactionlog;
CREATE TABLE IF NOT EXISTS transactionlog (
  Id bigint(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique Transaction ID',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of record creation',
  TransType char(10) NOT NULL,
  OrgID char(5) DEFAULT NULL,
  ClinicID smallint(6) DEFAULT NULL,
  PractitionerID char(2) DEFAULT NULL,
  UDID varchar(40) DEFAULT NULL,
  Details varchar(1024) DEFAULT NULL,
  IPv4 varchar(15) NOT NULL,
  Late int(11) NOT NULL,
  PRIMARY KEY (Id),
  KEY OrgID (OrgID),
  KEY TransType (TransType)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=752279 ;

-- --------------------------------------------------------

--
-- Table structure for table 'transtype'
--

DROP TABLE IF EXISTS transtype;
CREATE TABLE IF NOT EXISTS transtype (
  TransType char(10) NOT NULL COMMENT 'Transaction types',
  TransDesc varchar(50) NOT NULL COMMENT 'Description',
  PRIMARY KEY (TransType)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


/* views */

-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 21, 2015 at 06:04 PM
-- Server version: 5.5.42-cll
-- PHP Version: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: 'howlate_main'
--

-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwActiveClinics'
--
DROP VIEW IF EXISTS `vwActiveClinics`;
CREATE TABLE `vwActiveClinics` (
`ClinicID` int(6)
,`OrgID` char(5)
,`ClinicName` varchar(40)
,`Phone` varchar(25)
,`Address1` varchar(50)
,`Address2` varchar(50)
,`City` varchar(50)
,`Zip` varchar(6)
,`State` varchar(50)
,`Country` varchar(50)
,`Timezone` varchar(36)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwAssigned'
--
DROP VIEW IF EXISTS `vwAssigned`;
CREATE TABLE `vwAssigned` (
`SurrogKey` bigint(20)
,`Assigned` varchar(40)
,`OrgID` char(5)
,`ID` char(2)
,`ClinicID` int(6)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwClinicIntegration'
--
DROP VIEW IF EXISTS `vwClinicIntegration`;
CREATE TABLE `vwClinicIntegration` (
`OrgID` char(5)
,`ClinicID` bigint(20)
,`Instance` varchar(30)
,`PMSystem` int(11)
,`Name` varchar(50)
,`Agent32Bit` tinyint(1)
,`ConnectionType` varchar(20)
,`ConnectionString` varchar(400)
,`DbName` varchar(20)
,`UID` varchar(20)
,`PWD` varchar(20)
,`PollInterval` int(11)
,`HLUserID` varchar(20)
,`ProcessRecalls` tinyint(1)
,`SelectLates` varchar(2000)
,`SelectSessions` varchar(2000)
,`SelectToNotify` varchar(2000)
,`XPassword` varchar(200)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwLateness'
--
DROP VIEW IF EXISTS `vwLateness`;
CREATE TABLE `vwLateness` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`AbbrevName` varchar(20)
,`DateCreated` timestamp
,`OrgName` varchar(50)
,`ClinicID` int(11)
,`ClinicName` varchar(40)
,`Timezone` varchar(36)
,`MinutesLate` int(6)
,`MinutesLateMsg` char(60)
,`NotificationThreshold` int(11)
,`LateToNearest` int(11)
,`LatenessOffset` int(11)
,`LatenessCeiling` int(11)
,`AllowMessage` tinyint(1)
,`Subdomain` varchar(24)
,`Updated` timestamp
,`Sticky` tinyint(1)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwLateTZ'
--
DROP VIEW IF EXISTS `vwLateTZ`;
CREATE TABLE `vwLateTZ` (
`UKey` bigint(20)
,`OrgID` char(5)
,`ID` char(2)
,`Updated` timestamp
,`Minutes` smallint(3)
,`ClinicName` varchar(40)
,`Timezone` varchar(36)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwMyLates'
--
DROP VIEW IF EXISTS `vwMyLates`;
CREATE TABLE `vwMyLates` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`AbbrevName` varchar(20)
,`DateCreated` timestamp
,`OrgName` varchar(50)
,`ClinicID` int(11)
,`ClinicName` varchar(40)
,`MinutesLate` int(6)
,`MinutesLateMsg` char(60)
,`UDID` varchar(40)
,`Subdomain` varchar(24)
,`AllowMessage` tinyint(1)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwOrgAdmin'
--
DROP VIEW IF EXISTS `vwOrgAdmin`;
CREATE TABLE `vwOrgAdmin` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`AbbrevName` varchar(20)
,`ClinicPlaced` varchar(26)
,`ClinicName` varchar(40)
,`Subdomain` varchar(24)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwOrgUsers'
--
DROP VIEW IF EXISTS `vwOrgUsers`;
CREATE TABLE `vwOrgUsers` (
`DateCreated` timestamp
,`EmailAddress` varchar(50)
,`FullName` varchar(50)
,`OrgID` char(5)
,`SecretAnswer1` varchar(50)
,`SecretQuestion1` varchar(50)
,`UserID` varchar(50)
,`XPassword` varchar(200)
,`FQDN` varchar(40)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwPlacements'
--
DROP VIEW IF EXISTS `vwPlacements`;
CREATE TABLE `vwPlacements` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`AbbrevName` varchar(20)
,`DateCreated` timestamp
,`OrgName` varchar(50)
,`ClinicID` int(11)
,`ClinicName` varchar(40)
,`Timezone` varchar(36)
,`AllowMessage` tinyint(1)
,`Subdomain` varchar(24)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwPractitioners'
--
DROP VIEW IF EXISTS `vwPractitioners`;
CREATE TABLE `vwPractitioners` (
`OrgID` char(5)
,`PractitionerID` char(2)
,`Pin` varchar(8)
,`PractitionerName` varchar(20)
,`FullName` varchar(80)
,`NotificationThreshold` int(11)
,`LateToNearest` int(11)
,`LatenessOffset` int(11)
,`LatenessCeiling` int(11)
,`ClinicName` varchar(40)
,`ClinicID` int(6)
,`OrgName` varchar(24)
,`FQDN` varchar(40)
,`Subdomain` varchar(24)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwPractitioners_prev'
--
DROP VIEW IF EXISTS `vwPractitioners_prev`;
CREATE TABLE `vwPractitioners_prev` (
`OrgID` char(5)
,`PractitionerID` char(2)
,`Pin` varchar(8)
,`PractitionerName` varchar(20)
,`FullName` varchar(80)
,`NotificationThreshold` int(11)
,`LateToNearest` int(11)
,`LatenessOffset` int(11)
,`ClinicName` varchar(40)
,`ClinicID` int(6)
,`OrgName` varchar(24)
,`FQDN` varchar(40)
,`Subdomain` varchar(24)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwSessions'
--
DROP VIEW IF EXISTS `vwSessions`;
CREATE TABLE `vwSessions` (
`OrgID` varchar(5)
,`ID` varchar(2)
,`Day` varchar(10)
,`StartTime` int(11)
,`EndTime` int(11)
,`Timezone` varchar(36)
,`ClinicID` int(6)
,`ClinicName` varchar(40)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwSessionsToday'
--
DROP VIEW IF EXISTS `vwSessionsToday`;
CREATE TABLE `vwSessionsToday` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`dayname(curdate())` varchar(9)
,`StartTime` decimal(14,4)
,`EndTime` decimal(14,4)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwUnrecentAgentUpdates'
--
DROP VIEW IF EXISTS `vwUnrecentAgentUpdates`;
CREATE TABLE `vwUnrecentAgentUpdates` (
`OrgId` char(5)
,`OrgName` varchar(50)
,`MAX(Timestamp)` timestamp
,`MinutesAgo` bigint(21)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view 'vwWeeksLog'
--
DROP VIEW IF EXISTS `vwWeeksLog`;
CREATE TABLE `vwWeeksLog` (
`Id` bigint(11)
,`Timestamp` timestamp
,`TransType` char(10)
,`OrgID` char(5)
,`ClinicID` smallint(6)
,`PractitionerID` char(2)
,`UDID` varchar(40)
,`Details` varchar(1024)
,`IPv4` varchar(15)
);
-- --------------------------------------------------------

--
-- Structure for view 'vwActiveClinics'
--
DROP TABLE IF EXISTS `vwActiveClinics`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwActiveClinics AS select howlate_main.clinics.ClinicID AS ClinicID,howlate_main.clinics.OrgID AS OrgID,howlate_main.clinics.ClinicName AS ClinicName,howlate_main.clinics.Phone AS Phone,howlate_main.clinics.Address1 AS Address1,howlate_main.clinics.Address2 AS Address2,howlate_main.clinics.City AS City,howlate_main.clinics.Zip AS Zip,howlate_main.clinics.State AS State,howlate_main.clinics.Country AS Country,howlate_main.clinics.Timezone AS Timezone from howlate_main.clinics where howlate_main.clinics.ClinicID in (select howlate_main.placements.ClinicID AS ClinicID from howlate_main.placements);

-- --------------------------------------------------------

--
-- Structure for view 'vwAssigned'
--
DROP TABLE IF EXISTS `vwAssigned`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwAssigned AS select pr.SurrogKey AS SurrogKey,ifnull(c.ClinicName,'Not assigned') AS Assigned,pr.OrgID AS OrgID,pr.ID AS ID,c.ClinicID AS ClinicID from ((howlate_main.practitioners pr left join howlate_main.placements p on(((p.OrgID = pr.OrgID) and (p.ID = pr.ID)))) left join howlate_main.clinics c on(((c.OrgID = p.OrgID) and (c.ClinicID = p.ClinicID))));

-- --------------------------------------------------------

--
-- Structure for view 'vwClinicIntegration'
--
DROP TABLE IF EXISTS `vwClinicIntegration`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwClinicIntegration AS select ci.OrgID AS OrgID,ci.ClinicID AS ClinicID,ci.Instance AS Instance,ci.PMSystem AS PMSystem,pms.`Name` AS `Name`,pms.Agent32Bit AS Agent32Bit,ci.ConnectionType AS ConnectionType,ci.ConnectionString AS ConnectionString,ci.DbName AS DbName,ci.UID AS UID,ci.PWD AS PWD,ci.PollInterval AS PollInterval,ci.HLUserID AS HLUserID,ci.ProcessRecalls AS ProcessRecalls,pms.SelectLates AS SelectLates,pms.SelectSessions AS SelectSessions,pms.SelectToNotify AS SelectToNotify,u.XPassword AS XPassword from ((howlate_main.clinicintegration ci left join howlate_main.pmsystems pms on((pms.ID = ci.PMSystem))) left join howlate_main.orgusers u on(((u.OrgID = ci.OrgID) and (u.UserID = ci.HLUserID))));

-- --------------------------------------------------------

--
-- Structure for view 'vwLateness'
--
DROP TABLE IF EXISTS `vwLateness`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwLateness AS select v.OrgID AS OrgID,v.ID AS ID,v.FullName AS FullName,v.AbbrevName AS AbbrevName,v.DateCreated AS DateCreated,v.OrgName AS OrgName,v.ClinicID AS ClinicID,v.ClinicName AS ClinicName,v.Timezone AS Timezone,ifnull(howlate_main.lates.Minutes,0) AS MinutesLate,getMinutesLateMsg(ifnull(howlate_main.lates.Minutes,0),v.OrgID,v.ID) AS MinutesLateMsg,vwPractitioners.NotificationThreshold AS NotificationThreshold,vwPractitioners.LateToNearest AS LateToNearest,vwPractitioners.LatenessOffset AS LatenessOffset,vwPractitioners.LatenessCeiling AS LatenessCeiling,v.AllowMessage AS AllowMessage,v.Subdomain AS Subdomain,howlate_main.lates.Updated AS Updated,howlate_main.lates.Sticky AS Sticky from ((howlate_main.vwPlacements v left join howlate_main.lates on(((howlate_main.lates.OrgID = v.OrgID) and (howlate_main.lates.ID = v.ID)))) join howlate_main.vwPractitioners on(((vwPractitioners.OrgID = v.OrgID) and (vwPractitioners.PractitionerID = v.ID))));

-- --------------------------------------------------------

--
-- Structure for view 'vwLateTZ'
--
DROP TABLE IF EXISTS `vwLateTZ`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwLateTZ AS select l.UKey AS UKey,l.OrgID AS OrgID,l.ID AS ID,l.Updated AS Updated,l.Minutes AS Minutes,c.ClinicName AS ClinicName,ifnull(c.Timezone,o.Timezone) AS Timezone from (((howlate_main.lates l join howlate_main.placements p on(((p.OrgID = l.OrgID) and (p.ID = l.ID)))) join howlate_main.clinics c on((c.ClinicID = p.ClinicID))) join howlate_main.orgs o on((o.OrgID = c.OrgID)));

-- --------------------------------------------------------

--
-- Structure for view 'vwMyLates'
--
DROP TABLE IF EXISTS `vwMyLates`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwMyLates AS select v.OrgID AS OrgID,v.ID AS ID,v.FullName AS FullName,v.AbbrevName AS AbbrevName,v.DateCreated AS DateCreated,v.OrgName AS OrgName,v.ClinicID AS ClinicID,v.ClinicName AS ClinicName,v.MinutesLate AS MinutesLate,getMinutesLateMsg(v.MinutesLate,v.OrgID,v.ID) AS MinutesLateMsg,howlate_main.devicereg.UDID AS UDID,v.Subdomain AS Subdomain,v.AllowMessage AS AllowMessage from (howlate_main.vwLateness v join howlate_main.devicereg on(((v.OrgID = howlate_main.devicereg.OrgID) and (v.ID = howlate_main.devicereg.ID))));

-- --------------------------------------------------------

--
-- Structure for view 'vwOrgAdmin'
--
DROP TABLE IF EXISTS `vwOrgAdmin`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwOrgAdmin AS select p.OrgID AS OrgID,p.ID AS ID,p.FullName AS FullName,p.AbbrevName AS AbbrevName,(case when isnull(pl.ClinicID) then _utf8'(Not assigned to a clinic)' else pl.ClinicID end) AS ClinicPlaced,(case when isnull(c.ClinicName) then '(Not assigned to a clinic)' else c.ClinicName end) AS ClinicName,o.Subdomain AS Subdomain from (((howlate_main.practitioners p join howlate_main.orgs o on((p.OrgID = o.OrgID))) left join howlate_main.placements pl on(((pl.OrgID = p.OrgID) and (pl.ID = p.ID)))) left join howlate_main.clinics c on(((pl.OrgID = c.OrgID) and (pl.ClinicID = c.ClinicID))));

-- --------------------------------------------------------

--
-- Structure for view 'vwOrgUsers'
--
DROP TABLE IF EXISTS `vwOrgUsers`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwOrgUsers AS select u.DateCreated AS DateCreated,u.EmailAddress AS EmailAddress,u.FullName AS FullName,u.OrgID AS OrgID,u.SecretAnswer1 AS SecretAnswer1,u.SecretQuestion1 AS SecretQuestion1,u.UserID AS UserID,u.XPassword AS XPassword,o.FQDN AS FQDN from (howlate_main.orgusers u join howlate_main.orgs o on((u.OrgID = o.OrgID)));

-- --------------------------------------------------------

--
-- Structure for view 'vwPlacements'
--
DROP TABLE IF EXISTS `vwPlacements`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwPlacements AS select prac.OrgID AS OrgID,prac.ID AS ID,prac.FullName AS FullName,prac.AbbrevName AS AbbrevName,prac.DateCreated AS DateCreated,o.OrgName AS OrgName,p.ClinicID AS ClinicID,c.ClinicName AS ClinicName,ifnull(c.Timezone,o.Timezone) AS Timezone,c.AllowMessage AS AllowMessage,o.Subdomain AS Subdomain from (((howlate_main.practitioners prac join howlate_main.orgs o on((prac.OrgID = o.OrgID))) join howlate_main.placements p on(((p.OrgID = prac.OrgID) and (p.ID = prac.ID)))) join howlate_main.clinics c on(((c.OrgID = prac.OrgID) and (c.ClinicID = p.ClinicID))));

-- --------------------------------------------------------

--
-- Structure for view 'vwPractitioners'
--
DROP TABLE IF EXISTS `vwPractitioners`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwPractitioners AS select o.OrgID AS OrgID,p.ID AS PractitionerID,concat(o.OrgID,'.',p.ID) AS Pin,p.AbbrevName AS PractitionerName,p.FullName AS FullName,p.NotificationThreshold AS NotificationThreshold,p.LateToNearest AS LateToNearest,p.LatenessOffset AS LatenessOffset,p.LatenessCeiling AS LatenessCeiling,c.ClinicName AS ClinicName,c.ClinicID AS ClinicID,o.OrgShortName AS OrgName,o.FQDN AS FQDN,o.Subdomain AS Subdomain from (((howlate_main.practitioners p left join howlate_main.placements pl on(((p.ID = pl.ID) and (p.OrgID = pl.OrgID)))) left join howlate_main.clinics c on(((pl.OrgID = c.OrgID) and (pl.ClinicID = c.ClinicID)))) join howlate_main.orgs o on((p.OrgID = o.OrgID)));

-- --------------------------------------------------------

--
-- Structure for view 'vwPractitioners_prev'
--
DROP TABLE IF EXISTS `vwPractitioners_prev`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwPractitioners_prev AS select o.OrgID AS OrgID,p.ID AS PractitionerID,concat(o.OrgID,'.',p.ID) AS Pin,p.AbbrevName AS PractitionerName,p.FullName AS FullName,p.NotificationThreshold AS NotificationThreshold,p.LateToNearest AS LateToNearest,p.LatenessOffset AS LatenessOffset,c.ClinicName AS ClinicName,c.ClinicID AS ClinicID,o.OrgShortName AS OrgName,o.FQDN AS FQDN,o.Subdomain AS Subdomain from (((howlate_main.practitioners p join howlate_main.placements pl on(((p.ID = pl.ID) and (p.OrgID = pl.OrgID)))) join howlate_main.clinics c on(((pl.OrgID = c.OrgID) and (pl.ClinicID = c.ClinicID)))) join howlate_main.orgs o on((c.OrgID = o.OrgID)));

-- --------------------------------------------------------

--
-- Structure for view 'vwSessions'
--
DROP TABLE IF EXISTS `vwSessions`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwSessions AS select s.OrgID AS OrgID,s.ID AS ID,s.`Day` AS `Day`,s.StartTime AS StartTime,s.EndTime AS EndTime,ifnull(c.Timezone,o.Timezone) AS Timezone,c.ClinicID AS ClinicID,c.ClinicName AS ClinicName from (((howlate_main.sessions s join howlate_main.orgs o on((o.OrgID = s.OrgID))) left join howlate_main.placements p on(((p.OrgID = s.OrgID) and (p.ID = s.ID)))) left join howlate_main.clinics c on((c.ClinicID = p.ClinicID)));

-- --------------------------------------------------------

--
-- Structure for view 'vwSessionsToday'
--
DROP TABLE IF EXISTS `vwSessionsToday`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwSessionsToday AS select p.OrgID AS OrgID,p.ID AS ID,p.FullName AS FullName,dayname(curdate()) AS `dayname(curdate())`,(s.StartTime / 3600) AS StartTime,(s.EndTime / 3600) AS EndTime from (howlate_main.practitioners p left join howlate_main.sessions s on(((s.OrgID = p.OrgID) and (s.ID = p.ID)))) where (s.`Day` = convert(dayname(curdate()) using latin1));

-- --------------------------------------------------------

--
-- Structure for view 'vwUnrecentAgentUpdates'
--
DROP TABLE IF EXISTS `vwUnrecentAgentUpdates`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwUnrecentAgentUpdates AS select howlate_main.orgs.OrgID AS OrgId,howlate_main.orgs.OrgName AS OrgName,max(howlate_main.transactionlog.`Timestamp`) AS `MAX(Timestamp)`,timestampdiff(MINUTE,max(howlate_main.transactionlog.`Timestamp`),now()) AS MinutesAgo from (howlate_main.orgs left join howlate_main.transactionlog on((howlate_main.transactionlog.OrgID = howlate_main.orgs.OrgID))) where (howlate_main.transactionlog.TransType in ('LATE_UPD','AGT_START','AGT_STOP','QUE_NOTIF','SESS_UPD')) group by howlate_main.orgs.OrgID,howlate_main.orgs.OrgName having ((max(howlate_main.transactionlog.`Timestamp`) < (curdate() - interval 1 hour)) or isnull(max(howlate_main.transactionlog.`Timestamp`)));

-- --------------------------------------------------------

--
-- Structure for view 'vwWeeksLog'
--
DROP TABLE IF EXISTS `vwWeeksLog`;

CREATE ALGORITHM=UNDEFINED DEFINER=howlate@localhost SQL SECURITY DEFINER VIEW howlate_main.vwWeeksLog AS select howlate_main.transactionlog.Id AS Id,howlate_main.transactionlog.`Timestamp` AS `Timestamp`,howlate_main.transactionlog.TransType AS TransType,howlate_main.transactionlog.OrgID AS OrgID,howlate_main.transactionlog.ClinicID AS ClinicID,howlate_main.transactionlog.PractitionerID AS PractitionerID,howlate_main.transactionlog.UDID AS UDID,howlate_main.transactionlog.Details AS Details,howlate_main.transactionlog.IPv4 AS IPv4 from howlate_main.transactionlog where (howlate_main.transactionlog.`Timestamp` >= (now() - interval 1 week));

