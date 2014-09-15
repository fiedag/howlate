-- phpMyAdmin SQL Dump
-- version 4.1.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 15, 2014 at 12:56 PM
-- Server version: 5.5.37-cll
-- PHP Version: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `fiedlerc_hldev`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `sp_CreateClinicIntegration`$$
CREATE DEFINER=`fiedlerc`@`localhost` PROCEDURE `sp_CreateClinicIntegration`(IN `inOrgID` CHAR(5), IN `inClinicID` BIGINT)
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
CREATE DEFINER=`fiedlerc`@`localhost` PROCEDURE `sp_CreatePractitioner`(IN `inOrgID` CHAR(6), IN `inFullName` VARCHAR(50))
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

    insert into practitioners (OrgID, ID, FirstName, LastName, FullName, AbbrevName, IntegrKey)
    values (inOrgID, nextID, '', '', inFullName, inFullName, '');
    
    SELECT MIN(ClinicID) into @defaultClinic FROM clinics WHERE OrgID = inOrgID;
    
    insert into placements (OrgID, ID, ClinicID) SELECT inOrgID, nextID, @defaultClinic;
    
  end if;
  
END$$

DROP PROCEDURE IF EXISTS `sp_CreatePractitioner2`$$
CREATE DEFINER=`fiedlerc`@`localhost` PROCEDURE `sp_CreatePractitioner2`(IN `inOrgID` CHAR(6), IN `inFullName` VARCHAR(50))
    READS SQL DATA
BEGIN
  declare surr bigint;
  declare integr varchar(50);
  declare vID varchar(2);
  declare nextID varchar(2);
  declare fullName varchar(100);
  declare defaultClinic int;

  select ID into vID
  from practitioners
  where OrgID = inOrgID
  and FullName = inFullName;
  
  if vID is null then
	SELECT 'Does not exist';    
  else
    SELECT 'Does exist';
  end if;
  
END$$

DROP PROCEDURE IF EXISTS `sp_DeleteOrg`$$
CREATE DEFINER=`fiedlerc`@`localhost` PROCEDURE `sp_DeleteOrg`(IN `inOrgID` CHAR(6))
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

--
-- Functions
--
DROP FUNCTION IF EXISTS `getHHMMSS`$$
CREATE DEFINER=`fiedlerc`@`localhost` FUNCTION `getHHMMSS`(`inSecondsSinceMidnight` INT) RETURNS varchar(8) CHARSET latin1
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

DROP FUNCTION IF EXISTS `getHrsMins`$$
CREATE DEFINER=`fiedlerc`@`localhost` FUNCTION `getHrsMins`(`inMinutes` INT) RETURNS char(60) CHARSET latin1
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

DROP FUNCTION IF EXISTS `getHrsMins2`$$
CREATE DEFINER=`fiedlerc`@`localhost` FUNCTION `getHrsMins2`(`inMinutes` INT, `inOrgID` VARCHAR(5), `inPractID` VARCHAR(3)) RETURNS char(60) CHARSET latin1
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
      set min_word = "minute";
    else
      set min_word = "minutes";
    end if;

    if display % 60 = 0 then
      set result = concat(display DIV 60, " ", hr_word);
    else
      set result = concat(display DIV 60, " ", hr_word, " ", display % 60, " ", min_word);
    end if;
  end if;

  return result;

end$$

DROP FUNCTION IF EXISTS `getHrsMins3`$$
CREATE DEFINER=`fiedlerc`@`localhost` FUNCTION `getHrsMins3`(`inMinutes` INT, `inLateToNearest` INT, `inLatenessOffset` INT) RETURNS char(60) CHARSET latin1
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
      set min_word = "minute";
    else
      set min_word = "minutes";
    end if;

    if display % 60 = 0 then
      set result = concat(display DIV 60, " ", hr_word);
    else
      set result = concat(display DIV 60, " ", hr_word, " ", display % 60, " ", min_word);
    end if;
  end if;

  return result;

end$$

DROP FUNCTION IF EXISTS `getMinutesLateMsg`$$
CREATE DEFINER=`fiedlerc`@`localhost` FUNCTION `getMinutesLateMsg`(`inMinutes` INT, `inOrgID` VARCHAR(5), `inPractID` VARCHAR(3)) RETURNS char(60) CHARSET latin1
begin
  declare result char(60);
  declare display int;  
  declare hr_word char(5);
  declare min_word char(7);

  if inMinutes = 0 then
    return "On time";
  end if;
  
  select IF(LateToNearest = 0, 5, IFNULL(LateToNearest,5)), IF(LatenessOffset = 0, 5, IFNULL(LatenessOffset,5))
  into @latetonearest, @latenessoffset
  from practitioners
  where OrgID = inOrgID
  and ID = inPractID;
  
  set display = round( inMinutes / @latetonearest, 0) * @latetonearest - @latenessoffset;
  if display < 0 then 
    set display = 0;
  end if;

  if display = 0 then
    return "On time";
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
      set min_word = "minute";
    else
      set min_word = "minutes";
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
CREATE DEFINER=`fiedlerc`@`localhost` FUNCTION `getNextPractitionerID2`(`inOrgID` CHAR(6)) RETURNS char(2) CHARSET latin1
    COMMENT 'Increments base26 number from A (1) through YY (650) w. Z = 0'
begin
  declare highest char(2);
  declare vAscii int;
  declare digit1 char(1);
  declare digit2 char(1);
  
  select MAX(ID) INTO highest
  from practitioners where OrgID = inOrgID;

  if highest IS NULL then
    return 'A';
  end if;

  if highest = 'Y' then
    return 'AZ';
  end if;

  if length(highest) = 1 then
    set vAscii = ascii(highest);
    set vAscii = vAscii + 1;
    return char(vAscii);
  end if;

  set digit1 = right(highest,1);
  set digit2 = left(highest,1);
  if digit1 = 'Z' then
    set digit1 = 'A';
  elseif digit1 = 'Y' then
      set digit2 = char(ascii(digit2) + 1);
      set digit1 = 'Z';
  else
      set digit1 = char(ascii(digit1) + 1);
  end if;

  return concat(digit2,digit1);
end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `clinicintegration`
--

DROP TABLE IF EXISTS `clinicintegration`;
CREATE TABLE IF NOT EXISTS `clinicintegration` (
  `OrgID` char(5) NOT NULL,
  `ClinicID` bigint(20) NOT NULL,
  `Instance` varchar(30) NOT NULL,
  `DbName` varchar(20) NOT NULL,
  `UID` varchar(20) NOT NULL,
  `PWD` varchar(20) NOT NULL,
  `PollInterval` int(11) NOT NULL,
  `HLUserID` varchar(20) NOT NULL COMMENT 'The Howlate userid to use for connecting.',
  UNIQUE KEY `OrgID` (`OrgID`,`ClinicID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `clinics`
--

DROP TABLE IF EXISTS `clinics`;
CREATE TABLE IF NOT EXISTS `clinics` (
  `ClinicID` int(6) NOT NULL AUTO_INCREMENT,
  `OrgID` char(5) NOT NULL,
  `ClinicName` varchar(40) NOT NULL COMMENT 'Must fit on iPhone screen hence short',
  `Phone` varchar(25) NOT NULL,
  `Address1` varchar(50) NOT NULL,
  `Address2` varchar(50) NOT NULL,
  `City` varchar(50) NOT NULL,
  `Zip` varchar(6) NOT NULL,
  `State` varchar(50) NOT NULL,
  `Country` varchar(50) NOT NULL,
  `Timezone` varchar(36) NOT NULL DEFAULT 'Australia/Adelaide',
  `OpeningHrs` time NOT NULL COMMENT 'HH:MM local opening time',
  `ClosingHrs` time NOT NULL COMMENT 'HH:MM local time the clinic closes',
  `Location` varchar(500) DEFAULT NULL,
  UNIQUE KEY `ClinicID` (`ClinicID`),
  KEY `Timezone` (`Timezone`),
  KEY `TZ` (`Timezone`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Table structure for table `devicereg`
--

DROP TABLE IF EXISTS `devicereg`;
CREATE TABLE IF NOT EXISTS `devicereg` (
  `UDID` varchar(40) NOT NULL COMMENT 'Unique Device ID of the smartphone or tablet.',
  `OrgID` char(5) NOT NULL COMMENT 'The Organisation ID',
  `ID` char(2) NOT NULL COMMENT 'The Practitioner ID',
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Expires` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `UniqueID` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`UniqueID`),
  UNIQUE KEY `UDID` (`UDID`,`OrgID`,`ID`),
  UNIQUE KEY `UDID_2` (`UDID`,`OrgID`,`ID`),
  UNIQUE KEY `UniqueID` (`UniqueID`),
  KEY `OrgID` (`OrgID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Stores which devices have registered interest in which docto' AUTO_INCREMENT=116 ;

-- --------------------------------------------------------

--
-- Table structure for table `errorlog`
--

DROP TABLE IF EXISTS `errorlog`;
CREATE TABLE IF NOT EXISTS `errorlog` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ErrLevel` tinyint(4) NOT NULL COMMENT '1 = E_USER_ERROR,2 = E_USER_WARN,4 = E_USER_NOTICE',
  `ErrType` int(11) NOT NULL COMMENT '1 = App Error,2 = API Error,4=Data Error, 0 = Other Error',
  `File` varchar(80) NOT NULL COMMENT 'The file the error occured in',
  `Line` int(5) NOT NULL COMMENT 'The line of the file',
  `IPv4` varchar(15) DEFAULT NULL,
  `ErrMessage` varchar(256) NOT NULL COMMENT 'The text passed to trigger_error()',
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Id` (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=662 ;

-- --------------------------------------------------------

--
-- Table structure for table `lates`
--

DROP TABLE IF EXISTS `lates`;
CREATE TABLE IF NOT EXISTS `lates` (
  `UKey` bigint(20) NOT NULL AUTO_INCREMENT,
  `OrgID` char(5) NOT NULL COMMENT 'The Organisation ID',
  `ID` char(2) NOT NULL COMMENT 'The practitioner ID',
  `Updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When updated',
  `Minutes` smallint(3) NOT NULL COMMENT 'Minutes late.  Negative is early.',
  PRIMARY KEY (`UKey`),
  UNIQUE KEY `OrgID` (`OrgID`,`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Lateness records' AUTO_INCREMENT=31949 ;

-- --------------------------------------------------------

--
-- Table structure for table `notifqueue`
--

DROP TABLE IF EXISTS `notifqueue`;
CREATE TABLE IF NOT EXISTS `notifqueue` (
  `UID` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Unique key',
  `OrgID` int(5) NOT NULL,
  `ClinicID` char(2) NOT NULL,
  `PractitionerID` char(2) NOT NULL,
  `MobilePhone` varchar(12) NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When created',
  `Message` varchar(250) NOT NULL COMMENT '250 avail but please use 150 or less',
  `Status` varchar(20) NOT NULL COMMENT '''Queued'',''Sent'',''Received'',''Failed''',
  `TestMobile` varchar(14) NOT NULL COMMENT 'If nonblank then use this as the destination to SMS not the MobileNumber',
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `nums`
--

DROP TABLE IF EXISTS `nums`;
CREATE TABLE IF NOT EXISTS `nums` (
  `OrgID` char(5) NOT NULL,
  `num` int(11) NOT NULL AUTO_INCREMENT,
  `str` varchar(20) NOT NULL,
  `Practitioner` char(2) NOT NULL DEFAULT '0',
  `OrgID2` char(5) NOT NULL,
  PRIMARY KEY (`num`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=130 ;

--
-- Triggers `nums`
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
-- Table structure for table `orgs`
--

DROP TABLE IF EXISTS `orgs`;
CREATE TABLE IF NOT EXISTS `orgs` (
  `OrgID` char(5) NOT NULL COMMENT '4 character alpha ID plus a checksum character',
  `OrgName` varchar(50) NOT NULL COMMENT 'The registered long name.',
  `OrgShortName` varchar(24) NOT NULL COMMENT 'Must fit on table header on iPhone without scrolling',
  `TaxID` varchar(24) NOT NULL COMMENT 'The federal registration ID in the relevant country, e.g. ABN, FID.',
  `Subdomain` varchar(24) NOT NULL COMMENT 'word prepended to how-late.com e.g. bricc, must contain only valid characters.',
  `FQDN` varchar(40) NOT NULL COMMENT 'Fully qualified domain name',
  `BillingRef` varchar(18) NOT NULL COMMENT 'Reference to billing system.',
  `Address1` varchar(50) NOT NULL,
  `Address2` varchar(50) NOT NULL,
  `City` varchar(25) NOT NULL,
  `Zip` varchar(7) NOT NULL,
  `Country` varchar(25) NOT NULL,
  `Timezone` varchar(36) NOT NULL COMMENT 'e.g. Australia/Adelaide',
  `UpdIndic` int(11) NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`OrgID`),
  UNIQUE KEY `OrgID` (`OrgID`),
  UNIQUE KEY `OrgID_2` (`OrgID`),
  UNIQUE KEY `Subdomain` (`Subdomain`),
  UNIQUE KEY `FQDN` (`FQDN`),
  UNIQUE KEY `FQDN_2` (`FQDN`),
  KEY `Subdomain_2` (`Subdomain`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `orgusers`
--

DROP TABLE IF EXISTS `orgusers`;
CREATE TABLE IF NOT EXISTS `orgusers` (
  `OrgID` char(5) NOT NULL COMMENT 'Link to Orgs Table',
  `UserID` varchar(18) NOT NULL COMMENT '50 character userid.',
  `FullName` varchar(50) NOT NULL,
  `EmailAddress` varchar(50) NOT NULL COMMENT 'Used for signin.',
  `XPassword` varchar(200) NOT NULL COMMENT 'Encrypted password.',
  `SecretQuestion1` varchar(50) NOT NULL COMMENT 'The questions may change.  Store them here',
  `SecretAnswer1` varchar(50) NOT NULL COMMENT 'The answer recorded.',
  `DateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UID` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Table structure for table `placements`
--

DROP TABLE IF EXISTS `placements`;
CREATE TABLE IF NOT EXISTS `placements` (
  `OrgID` char(5) NOT NULL COMMENT 'The Organisation ID',
  `ID` char(2) NOT NULL,
  `ClinicID` int(11) NOT NULL COMMENT 'The Clinic ID',
  `SurrogKey` varchar(7) NOT NULL COMMENT 'Can this surrogate key column be deleted?',
  PRIMARY KEY (`OrgID`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This places a practitioner at a clinic';

-- --------------------------------------------------------

--
-- Table structure for table `practitioners`
--

DROP TABLE IF EXISTS `practitioners`;
CREATE TABLE IF NOT EXISTS `practitioners` (
  `OrgID` char(5) NOT NULL,
  `ID` char(2) NOT NULL COMMENT 'Practitioner ID',
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `FullName` varchar(80) NOT NULL COMMENT 'E.g. Dr A.J.K. Venkatanarasimharajuvaripeta',
  `AbbrevName` varchar(20) NOT NULL COMMENT 'For iPhone fit.',
  `DateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SurrogKey` bigint(20) NOT NULL AUTO_INCREMENT,
  `IntegrKey` varchar(80) DEFAULT NULL,
  `NotificationThreshold` int(11) NOT NULL COMMENT 'Will cause push notifications',
  `LateToNearest` int(11) NOT NULL COMMENT 'Report to nearest number of minutes.',
  `LatenessOffset` int(11) NOT NULL COMMENT 'Number of minutes to subtract from the actual lateness for display',
  PRIMARY KEY (`SurrogKey`),
  UNIQUE KEY `OrgID` (`OrgID`,`ID`),
  KEY `IntegrKey` (`OrgID`,`IntegrKey`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=237 ;

--
-- Triggers `practitioners`
--
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
-- Table structure for table `resetrequests`
--

DROP TABLE IF EXISTS `resetrequests`;
CREATE TABLE IF NOT EXISTS `resetrequests` (
  `Token` varchar(100) NOT NULL,
  `EmailAddress` varchar(50) NOT NULL,
  `OrgID` char(5) NOT NULL,
  `UserID` varchar(50) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `OrgID` varchar(5) NOT NULL COMMENT 'Organisation ID',
  `ID` varchar(2) NOT NULL COMMENT 'PractitionerID',
  `Day` varchar(10) NOT NULL COMMENT 'Monday-Sunday',
  `StartTime` int(11) NOT NULL COMMENT 'Seconds from Midnight',
  `EndTime` int(11) NOT NULL COMMENT 'Seconds from Midnight',
  PRIMARY KEY (`OrgID`,`ID`,`Day`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `timezones`
--

DROP TABLE IF EXISTS `timezones`;
CREATE TABLE IF NOT EXISTS `timezones` (
  `CodeVal` varchar(50) NOT NULL,
  `CodeDesc` varchar(50) NOT NULL,
  PRIMARY KEY (`CodeVal`),
  UNIQUE KEY `CodeVal` (`CodeVal`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transactionlog`
--

DROP TABLE IF EXISTS `transactionlog`;
CREATE TABLE IF NOT EXISTS `transactionlog` (
  `Id` bigint(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique Transaction ID',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of record creation',
  `TransType` char(10) NOT NULL,
  `OrgID` char(5) DEFAULT NULL,
  `ClinicID` smallint(6) DEFAULT NULL,
  `PractitionerID` char(2) DEFAULT NULL,
  `UDID` varchar(40) DEFAULT NULL,
  `Details` varchar(256) DEFAULT NULL,
  `IPv4` varchar(15) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `OrgID` (`OrgID`),
  KEY `TransType` (`TransType`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=61306 ;

-- --------------------------------------------------------

--
-- Table structure for table `transtype`
--

DROP TABLE IF EXISTS `transtype`;
CREATE TABLE IF NOT EXISTS `transtype` (
  `TransType` char(10) NOT NULL COMMENT 'Transaction types',
  `TransDesc` varchar(50) NOT NULL COMMENT 'Description',
  PRIMARY KEY (`TransType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vwActiveClinics`
--
DROP VIEW IF EXISTS `vwActiveClinics`;
CREATE TABLE IF NOT EXISTS `vwActiveClinics` (
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
,`Location` varchar(500)
,`Timezone` varchar(36)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwAssigned`
--
DROP VIEW IF EXISTS `vwAssigned`;
CREATE TABLE IF NOT EXISTS `vwAssigned` (
`SurrogKey` bigint(20)
,`Assigned` varchar(40)
,`OrgID` char(5)
,`ID` char(2)
,`ClinicID` int(6)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwDisplayLate`
--
DROP VIEW IF EXISTS `vwDisplayLate`;
CREATE TABLE IF NOT EXISTS `vwDisplayLate` (
`OrgID` char(5)
,`ID` char(2)
,`Minutes` smallint(3)
,`MinutesLateMsg` varchar(65)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwErrorTail`
--
DROP VIEW IF EXISTS `vwErrorTail`;
CREATE TABLE IF NOT EXISTS `vwErrorTail` (
`Id` bigint(20)
,`ErrLevel` tinyint(4)
,`ErrType` int(11)
,`File` varchar(80)
,`Line` int(5)
,`IPv4` varchar(15)
,`ErrMessage` varchar(256)
,`Created` timestamp
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwLateness`
--
DROP VIEW IF EXISTS `vwLateness`;
CREATE TABLE IF NOT EXISTS `vwLateness` (
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
,`Subdomain` varchar(24)
,`Updated` timestamp
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwLateTZ`
--
DROP VIEW IF EXISTS `vwLateTZ`;
CREATE TABLE IF NOT EXISTS `vwLateTZ` (
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
-- Stand-in structure for view `vwLogs_AAAHH_LastHour`
--
DROP VIEW IF EXISTS `vwLogs_AAAHH_LastHour`;
CREATE TABLE IF NOT EXISTS `vwLogs_AAAHH_LastHour` (
`Id` bigint(11)
,`Timestamp` timestamp
,`TransType` char(10)
,`OrgID` char(5)
,`ClinicID` smallint(6)
,`PractitionerID` char(2)
,`UDID` varchar(40)
,`Details` varchar(256)
,`IPv4` varchar(15)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwMyLates`
--
DROP VIEW IF EXISTS `vwMyLates`;
CREATE TABLE IF NOT EXISTS `vwMyLates` (
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
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwOrgAdmin`
--
DROP VIEW IF EXISTS `vwOrgAdmin`;
CREATE TABLE IF NOT EXISTS `vwOrgAdmin` (
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
-- Stand-in structure for view `vwOrgUsers`
--
DROP VIEW IF EXISTS `vwOrgUsers`;
CREATE TABLE IF NOT EXISTS `vwOrgUsers` (
`DateCreated` timestamp
,`EmailAddress` varchar(50)
,`FullName` varchar(50)
,`OrgID` char(5)
,`SecretAnswer1` varchar(50)
,`SecretQuestion1` varchar(50)
,`UserID` varchar(18)
,`XPassword` varchar(200)
,`FQDN` varchar(40)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwPlacements`
--
DROP VIEW IF EXISTS `vwPlacements`;
CREATE TABLE IF NOT EXISTS `vwPlacements` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`AbbrevName` varchar(20)
,`DateCreated` timestamp
,`OrgName` varchar(50)
,`ClinicID` int(11)
,`ClinicName` varchar(40)
,`Timezone` varchar(36)
,`Subdomain` varchar(24)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwPractitioners`
--
DROP VIEW IF EXISTS `vwPractitioners`;
CREATE TABLE IF NOT EXISTS `vwPractitioners` (
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
-- Stand-in structure for view `vwSessions`
--
DROP VIEW IF EXISTS `vwSessions`;
CREATE TABLE IF NOT EXISTS `vwSessions` (
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
-- Stand-in structure for view `vwSMS`
--
DROP VIEW IF EXISTS `vwSMS`;
CREATE TABLE IF NOT EXISTS `vwSMS` (
`Id` bigint(11)
,`Timestamp` timestamp
,`TransType` char(10)
,`OrgID` char(5)
,`ClinicID` smallint(6)
,`PractitionerID` char(2)
,`UDID` varchar(40)
,`Details` varchar(256)
,`IPv4` varchar(15)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwTempTest`
--
DROP VIEW IF EXISTS `vwTempTest`;
CREATE TABLE IF NOT EXISTS `vwTempTest` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`LatenessOffset` int(11)
,`LateToNearest` int(11)
,`getHrsMins3(0, LateToNearest, LatenessOffset)` char(60)
,`getHrsMins3(7, LateToNearest, LatenessOffset)` char(60)
,`getHrsMins3(8, LateToNearest, LatenessOffset)` char(60)
,`getHrsMins3(22, LateToNearest, LatenessOffset)` char(60)
,`getHrsMins3(23, LateToNearest, LatenessOffset)` char(60)
,`getHrsMins3(122, LateToNearest, LatenessOffset)` char(60)
,`getHrsMins3(123, LateToNearest, LatenessOffset)` char(60)
,`getHrsMins3(84, LateToNearest, LatenessOffset)` char(60)
,`getHrsMins3(85, LateToNearest, LatenessOffset)` char(60)
);
-- --------------------------------------------------------

--
-- Structure for view `vwActiveClinics`
--
DROP TABLE IF EXISTS `vwActiveClinics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwActiveClinics` AS select `clinics`.`ClinicID` AS `ClinicID`,`clinics`.`OrgID` AS `OrgID`,`clinics`.`ClinicName` AS `ClinicName`,`clinics`.`Phone` AS `Phone`,`clinics`.`Address1` AS `Address1`,`clinics`.`Address2` AS `Address2`,`clinics`.`City` AS `City`,`clinics`.`Zip` AS `Zip`,`clinics`.`State` AS `State`,`clinics`.`Country` AS `Country`,`clinics`.`Location` AS `Location`,`clinics`.`Timezone` AS `Timezone` from `clinics` where `clinics`.`ClinicID` in (select `placements`.`ClinicID` AS `ClinicID` from `placements`);

-- --------------------------------------------------------

--
-- Structure for view `vwAssigned`
--
DROP TABLE IF EXISTS `vwAssigned`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwAssigned` AS select `pr`.`SurrogKey` AS `SurrogKey`,ifnull(`c`.`ClinicName`,'Not assigned') AS `Assigned`,`pr`.`OrgID` AS `OrgID`,`pr`.`ID` AS `ID`,`c`.`ClinicID` AS `ClinicID` from ((`practitioners` `pr` left join `placements` `p` on(((`p`.`OrgID` = `pr`.`OrgID`) and (`p`.`ID` = `pr`.`ID`)))) left join `clinics` `c` on(((`c`.`OrgID` = `p`.`OrgID`) and (`c`.`ClinicID` = `p`.`ClinicID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwDisplayLate`
--
DROP TABLE IF EXISTS `vwDisplayLate`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwDisplayLate` AS select `v`.`OrgID` AS `OrgID`,`v`.`ID` AS `ID`,`v`.`Minutes` AS `Minutes`,(case `v`.`Minutes` when 0 then 'On time' else convert(concat(`getHrsMins2`(`v`.`Minutes`,`v`.`OrgID`,`v`.`ID`),' late') using utf8) end) AS `MinutesLateMsg` from `lates` `v`;

-- --------------------------------------------------------

--
-- Structure for view `vwErrorTail`
--
DROP TABLE IF EXISTS `vwErrorTail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwErrorTail` AS select `errorlog`.`Id` AS `Id`,`errorlog`.`ErrLevel` AS `ErrLevel`,`errorlog`.`ErrType` AS `ErrType`,`errorlog`.`File` AS `File`,`errorlog`.`Line` AS `Line`,`errorlog`.`IPv4` AS `IPv4`,`errorlog`.`ErrMessage` AS `ErrMessage`,`errorlog`.`Created` AS `Created` from `errorlog` order by `errorlog`.`Id` desc limit 0,20;

-- --------------------------------------------------------

--
-- Structure for view `vwLateness`
--
DROP TABLE IF EXISTS `vwLateness`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwLateness` AS select `v`.`OrgID` AS `OrgID`,`v`.`ID` AS `ID`,`v`.`FullName` AS `FullName`,`v`.`AbbrevName` AS `AbbrevName`,`v`.`DateCreated` AS `DateCreated`,`v`.`OrgName` AS `OrgName`,`v`.`ClinicID` AS `ClinicID`,`v`.`ClinicName` AS `ClinicName`,`v`.`Timezone` AS `Timezone`,ifnull(`lates`.`Minutes`,0) AS `MinutesLate`,`getMinutesLateMsg`(ifnull(`lates`.`Minutes`,0),`v`.`OrgID`,`v`.`ID`) AS `MinutesLateMsg`,`v`.`Subdomain` AS `Subdomain`,`lates`.`Updated` AS `Updated` from (`vwPlacements` `v` left join `lates` on(((`lates`.`OrgID` = `v`.`OrgID`) and (`lates`.`ID` = `v`.`ID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwLateTZ`
--
DROP TABLE IF EXISTS `vwLateTZ`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwLateTZ` AS select `l`.`UKey` AS `UKey`,`l`.`OrgID` AS `OrgID`,`l`.`ID` AS `ID`,`l`.`Updated` AS `Updated`,`l`.`Minutes` AS `Minutes`,`c`.`ClinicName` AS `ClinicName`,ifnull(`c`.`Timezone`,`o`.`Timezone`) AS `Timezone` from (((`lates` `l` join `placements` `p` on(((`p`.`OrgID` = `l`.`OrgID`) and (`p`.`ID` = `l`.`ID`)))) join `clinics` `c` on((`c`.`ClinicID` = `p`.`ClinicID`))) join `orgs` `o` on((`o`.`OrgID` = `c`.`OrgID`)));

-- --------------------------------------------------------

--
-- Structure for view `vwLogs_AAAHH_LastHour`
--
DROP TABLE IF EXISTS `vwLogs_AAAHH_LastHour`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwLogs_AAAHH_LastHour` AS select `transactionlog`.`Id` AS `Id`,`transactionlog`.`Timestamp` AS `Timestamp`,`transactionlog`.`TransType` AS `TransType`,`transactionlog`.`OrgID` AS `OrgID`,`transactionlog`.`ClinicID` AS `ClinicID`,`transactionlog`.`PractitionerID` AS `PractitionerID`,`transactionlog`.`UDID` AS `UDID`,`transactionlog`.`Details` AS `Details`,`transactionlog`.`IPv4` AS `IPv4` from `transactionlog` where ((`transactionlog`.`OrgID` = 'AAAHH') and (`transactionlog`.`Timestamp` >= (now() - interval 1 hour)));

-- --------------------------------------------------------

--
-- Structure for view `vwMyLates`
--
DROP TABLE IF EXISTS `vwMyLates`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwMyLates` AS select `v`.`OrgID` AS `OrgID`,`v`.`ID` AS `ID`,`v`.`FullName` AS `FullName`,`v`.`AbbrevName` AS `AbbrevName`,`v`.`DateCreated` AS `DateCreated`,`v`.`OrgName` AS `OrgName`,`v`.`ClinicID` AS `ClinicID`,`v`.`ClinicName` AS `ClinicName`,`v`.`MinutesLate` AS `MinutesLate`,`getMinutesLateMsg`(`v`.`MinutesLate`,`v`.`OrgID`,`v`.`ID`) AS `MinutesLateMsg`,`devicereg`.`UDID` AS `UDID`,`v`.`Subdomain` AS `Subdomain` from (`vwLateness` `v` join `devicereg` on(((`v`.`OrgID` = `devicereg`.`OrgID`) and (`v`.`ID` = `devicereg`.`ID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwOrgAdmin`
--
DROP TABLE IF EXISTS `vwOrgAdmin`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwOrgAdmin` AS select `p`.`OrgID` AS `OrgID`,`p`.`ID` AS `ID`,`p`.`FullName` AS `FullName`,`p`.`AbbrevName` AS `AbbrevName`,(case when isnull(`pl`.`ClinicID`) then _utf8'(Not assigned to a clinic)' else `pl`.`ClinicID` end) AS `ClinicPlaced`,(case when isnull(`c`.`ClinicName`) then '(Not assigned to a clinic)' else `c`.`ClinicName` end) AS `ClinicName`,`o`.`Subdomain` AS `Subdomain` from (((`practitioners` `p` join `orgs` `o` on((`p`.`OrgID` = `o`.`OrgID`))) left join `placements` `pl` on(((`pl`.`OrgID` = `p`.`OrgID`) and (`pl`.`ID` = `p`.`ID`)))) left join `clinics` `c` on(((`pl`.`OrgID` = `c`.`OrgID`) and (`pl`.`ClinicID` = `c`.`ClinicID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwOrgUsers`
--
DROP TABLE IF EXISTS `vwOrgUsers`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwOrgUsers` AS select `u`.`DateCreated` AS `DateCreated`,`u`.`EmailAddress` AS `EmailAddress`,`u`.`FullName` AS `FullName`,`u`.`OrgID` AS `OrgID`,`u`.`SecretAnswer1` AS `SecretAnswer1`,`u`.`SecretQuestion1` AS `SecretQuestion1`,`u`.`UserID` AS `UserID`,`u`.`XPassword` AS `XPassword`,`o`.`FQDN` AS `FQDN` from (`orgusers` `u` join `orgs` `o` on((`u`.`OrgID` = `o`.`OrgID`)));

-- --------------------------------------------------------

--
-- Structure for view `vwPlacements`
--
DROP TABLE IF EXISTS `vwPlacements`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwPlacements` AS select `prac`.`OrgID` AS `OrgID`,`prac`.`ID` AS `ID`,`prac`.`FullName` AS `FullName`,`prac`.`AbbrevName` AS `AbbrevName`,`prac`.`DateCreated` AS `DateCreated`,`o`.`OrgName` AS `OrgName`,`p`.`ClinicID` AS `ClinicID`,`c`.`ClinicName` AS `ClinicName`,ifnull(`c`.`Timezone`,`o`.`Timezone`) AS `Timezone`,`o`.`Subdomain` AS `Subdomain` from (((`practitioners` `prac` join `orgs` `o` on((`prac`.`OrgID` = `o`.`OrgID`))) join `placements` `p` on(((`p`.`OrgID` = `prac`.`OrgID`) and (`p`.`ID` = `prac`.`ID`)))) join `clinics` `c` on(((`c`.`OrgID` = `prac`.`OrgID`) and (`c`.`ClinicID` = `p`.`ClinicID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwPractitioners`
--
DROP TABLE IF EXISTS `vwPractitioners`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwPractitioners` AS select `o`.`OrgID` AS `OrgID`,`p`.`ID` AS `PractitionerID`,concat(`o`.`OrgID`,'.',`p`.`ID`) AS `Pin`,`p`.`AbbrevName` AS `PractitionerName`,`p`.`FullName` AS `FullName`,`p`.`NotificationThreshold` AS `NotificationThreshold`,`p`.`LateToNearest` AS `LateToNearest`,`p`.`LatenessOffset` AS `LatenessOffset`,`c`.`ClinicName` AS `ClinicName`,`c`.`ClinicID` AS `ClinicID`,`o`.`OrgShortName` AS `OrgName`,`o`.`FQDN` AS `FQDN`,`o`.`Subdomain` AS `Subdomain` from (((`practitioners` `p` join `placements` `pl` on(((`p`.`ID` = `pl`.`ID`) and (`p`.`OrgID` = `pl`.`OrgID`)))) join `clinics` `c` on(((`pl`.`OrgID` = `c`.`OrgID`) and (`pl`.`ClinicID` = `c`.`ClinicID`)))) join `orgs` `o` on((`c`.`OrgID` = `o`.`OrgID`)));

-- --------------------------------------------------------

--
-- Structure for view `vwSessions`
--
DROP TABLE IF EXISTS `vwSessions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwSessions` AS select `s`.`OrgID` AS `OrgID`,`s`.`ID` AS `ID`,`s`.`Day` AS `Day`,`s`.`StartTime` AS `StartTime`,`s`.`EndTime` AS `EndTime`,ifnull(`c`.`Timezone`,`o`.`Timezone`) AS `Timezone`,`c`.`ClinicID` AS `ClinicID`,`c`.`ClinicName` AS `ClinicName` from (((`sessions` `s` join `orgs` `o` on((`o`.`OrgID` = `s`.`OrgID`))) left join `placements` `p` on(((`p`.`OrgID` = `s`.`OrgID`) and (`p`.`ID` = `s`.`ID`)))) left join `clinics` `c` on((`c`.`ClinicID` = `p`.`ClinicID`)));

-- --------------------------------------------------------

--
-- Structure for view `vwSMS`
--
DROP TABLE IF EXISTS `vwSMS`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwSMS` AS select `transactionlog`.`Id` AS `Id`,`transactionlog`.`Timestamp` AS `Timestamp`,`transactionlog`.`TransType` AS `TransType`,`transactionlog`.`OrgID` AS `OrgID`,`transactionlog`.`ClinicID` AS `ClinicID`,`transactionlog`.`PractitionerID` AS `PractitionerID`,`transactionlog`.`UDID` AS `UDID`,`transactionlog`.`Details` AS `Details`,`transactionlog`.`IPv4` AS `IPv4` from `transactionlog` where (`transactionlog`.`TransType` = 'DEV_SMS');

-- --------------------------------------------------------

--
-- Structure for view `vwTempTest`
--
DROP TABLE IF EXISTS `vwTempTest`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fiedlerc`@`localhost` SQL SECURITY DEFINER VIEW `vwTempTest` AS select `practitioners`.`OrgID` AS `OrgID`,`practitioners`.`ID` AS `ID`,`practitioners`.`FullName` AS `FullName`,`practitioners`.`LatenessOffset` AS `LatenessOffset`,`practitioners`.`LateToNearest` AS `LateToNearest`,`getHrsMins3`(0,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(0, LateToNearest, LatenessOffset)`,`getHrsMins3`(7,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(7, LateToNearest, LatenessOffset)`,`getHrsMins3`(8,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(8, LateToNearest, LatenessOffset)`,`getHrsMins3`(22,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(22, LateToNearest, LatenessOffset)`,`getHrsMins3`(23,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(23, LateToNearest, LatenessOffset)`,`getHrsMins3`(122,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(122, LateToNearest, LatenessOffset)`,`getHrsMins3`(123,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(123, LateToNearest, LatenessOffset)`,`getHrsMins3`(84,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(84, LateToNearest, LatenessOffset)`,`getHrsMins3`(85,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(85, LateToNearest, LatenessOffset)` from `practitioners`;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
