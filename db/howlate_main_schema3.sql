-- phpMyAdmin SQL Dump
-- version 4.1.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 01, 2014 at 01:12 AM
-- Server version: 5.5.37-cll
-- PHP Version: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `howlate_main`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `sp_CreatePractitioner`$$
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_CreatePractitioner`(IN `inOrgID` CHAR(6), IN `inFullName` VARCHAR(50))
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
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `sp_CreatePractitioner2`(IN `inOrgID` CHAR(6), IN `inFullName` VARCHAR(50))
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

--
-- Functions
--
DROP FUNCTION IF EXISTS `fnTempTest`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `fnTempTest`(`inMinutes` INT, `inLateToNearest` INT, `inLatenessOffset` INT) RETURNS varchar(90) CHARSET latin1
begin
  declare result char(60);
  declare display int;
  
  declare hr_word char(5);
  declare min_word char(7);

  set inMinutes = IFNULL(inMinutes,0);
  set inLateToNearest = IFNULL(inLateToNearest,5);
  set inLatenessOffset = IFNULL(inLatenessOffset, 0);
    
  set display = round( inMinutes / inLateToNearest, 0) * inLateToNearest;
  set display = display - inLatenessOffset;
  
  
  

  return concat('m=', inMinutes, ',near=' , inLateToNearest, ',o=', inLatenessOffset, ',d=', display);

end$$

DROP FUNCTION IF EXISTS `getHrsMins`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getHrsMins`(`inMinutes` INT) RETURNS char(60) CHARSET latin1
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
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getHrsMins2`(`inMinutes` INT, `inOrgID` VARCHAR(5), `inPractID` VARCHAR(3)) RETURNS char(60) CHARSET latin1
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
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getHrsMins3`(`inMinutes` INT, `inLateToNearest` INT, `inLatenessOffset` INT) RETURNS char(60) CHARSET latin1
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

DROP FUNCTION IF EXISTS `getNextPractitionerID2`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getNextPractitionerID2`(`inOrgID` CHAR(6)) RETURNS char(2) CHARSET latin1
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

--
-- Dumping data for table `clinics`
--

INSERT INTO `clinics` (`ClinicID`, `OrgID`, `ClinicName`, `Phone`, `Address1`, `Address2`, `City`, `Zip`, `State`, `Country`, `Timezone`, `OpeningHrs`, `ClosingHrs`, `Location`) VALUES
(20, 'AAAEE', 'Fiedler Medical', '873240902', '7 Melrose Ave', '', 'Beulah Park', '5067', 'SA', 'Australia', 'Australia/Adelaide', '00:00:00', '00:00:00', NULL),
(22, 'AAAFF', 'megaclinic', '', '', '', '', '', '', '', 'Australia/Adelaide', '00:00:00', '00:00:00', NULL),
(29, 'AAAHH', 'Hastings Medical Centre', '(02) 6586 1331', '70 High St', '', 'Wauchope', '2446', 'NSW', 'Australia', 'Australia/NSW', '08:00:00', '19:00:00', NULL),
(31, 'AAADD', 'Margate Clinic', '', '', '', 'Margate', '', 'Margate', 'Australia', 'Australia/Adelaide', '00:00:00', '00:00:00', NULL),
(32, 'AAADD', 'Adelaide Test Clinic', '', '', '', '', '', '', '', 'Australia/Adelaide', '00:00:00', '00:00:00', NULL);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Stores which devices have registered interest in which docto' AUTO_INCREMENT=27 ;

--
-- Dumping data for table `devicereg`
--

INSERT INTO `devicereg` (`UDID`, `OrgID`, `ID`, `Created`, `Expires`, `UniqueID`) VALUES
('0417851627', 'AAAHH', 'A', '2014-08-22 06:05:15', '2015-02-21 13:00:00', 18),
('0428864756', 'AAAHH', 'A', '2014-08-27 01:19:28', '2015-02-26 13:00:00', 20),
('0403569377', 'AAAHH', 'A', '2014-08-29 01:30:54', '2015-02-27 13:00:00', 22),
('0403569377', 'AAADD', 'B', '2014-08-31 10:20:31', '2015-02-27 13:00:00', 26);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=551 ;

--
-- Dumping data for table `errorlog`
--

INSERT INTO `errorlog` (`Id`, `ErrLevel`, `ErrType`, `File`, `Line`, `IPv4`, `ErrMessage`, `Created`) VALUES
(118, 127, 0, '/home/howlate/public_html/master/model/howlate_site.class.php', 116, '203.122.224.228', 'Subdomain already exists', '2014-07-09 01:30:23'),
(119, 127, 0, '/home/howlate/public_html/master/model/howlate_site.class.php', 116, '203.122.224.228', 'Subdomain already exists', '2014-07-09 01:32:52'),
(120, 127, 0, '/home/howlate/public_html/master/model/howlate_site.class.php', 117, '203.122.224.228', 'Cpanel Subdomain already exists ', '2014-07-09 01:36:33'),
(121, 127, 0, '/home/howlate/public_html/master/model/howlate_site.class.php', 116, '203.122.224.228', 'Cpanel Subdomain already exists: <b>GET /frontend/x3/subdomain/doadddomain.html?domain=deleteme&rootdomain=how-late.com&dir=public_html%2Fmaster\r\n HTTP/1.0\r\nHost:how-late.com\r\nAuthorization: Basic aG93bGF0ZTozMTM0LTVRXmhQJDE=\r\n\r\n</b>', '2014-07-09 01:47:10'),
(122, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 209, '58.96.104.119', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-07-09 02:44:19'),
(123, 127, 0, '/home/howlate/public_html/master/controller/selfregController.php', 17, '203.122.224.228', 'Program called with incorrect parameters', '2014-07-09 06:56:02'),
(124, 127, 0, '/home/howlate/public_html/master/controller/selfregController.php', 18, '203.122.224.228', 'Program called with incorrect parameters', '2014-07-09 06:57:52'),
(125, 127, 0, '/home/howlate/public_html/master/controller/selfregController.php', 18, '203.122.224.228', 'Program called with incorrect parameters', '2014-07-09 06:57:52'),
(126, 127, 0, '/home/howlate/public_html/master/controller/selfregController.php', 18, '203.122.224.228', 'Program called with incorrect parameters', '2014-07-09 06:57:54'),
(127, 127, 0, '/home/howlate/public_html/master/controller/selfregController.php', 25, '203.122.224.228', 'Program called from incorrect subdomain or with incorrect orgid', '2014-07-09 06:58:20'),
(128, 127, 0, '/home/howlate/public_html/master/controller/selfregController.php', 34, '203.122.224.228', 'This is not a valid practitioner for this organisation', '2014-07-09 07:03:59'),
(129, 127, 0, '/home/howlate/public_html/master/controller/selfregController.php', 18, '203.122.224.228', 'Program called with incorrect parameters', '2014-07-09 07:12:27'),
(130, 127, 0, '/home/howlate/public_html/master/controller/selfregController.php', 18, '203.122.224.228', 'Program called with incorrect parameters', '2014-07-09 07:12:38'),
(131, 127, 0, '/home/howlate/public_html/master/controller/selfregController.php', 17, '203.122.224.228', 'Program called with incorrect parameters', '2014-07-09 07:30:50'),
(132, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 200, '203.122.224.228', 'The device was not registered for information from organisation = AAAFF and ID = A', '2014-07-09 07:37:33'),
(133, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 337, '203.122.212.28', 'The Password change request was not successful. 0', '2014-07-12 11:56:51'),
(134, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 337, '203.122.212.28', 'The Password change request was not successful. 0', '2014-07-12 12:26:23'),
(135, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 337, '203.122.212.28', 'The Password change request was not successful. ', '2014-07-12 12:28:04'),
(136, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 337, '203.122.212.28', 'The Password change request was not successful. ', '2014-07-12 12:28:23'),
(137, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 336, '203.122.212.28', 'The Password change request was not successful. affected rows = 0', '2014-07-12 12:30:36'),
(138, 127, 0, '/home/howlate/public_html/master/api.php', 36, '203.208.65.204', 'Parameter udid must be supplied', '2014-07-17 07:02:48'),
(139, 127, 0, '/home/howlate/public_html/master/api.php', 37, '203.208.65.204', 'Parameter met must be supplied', '2014-07-17 07:03:00'),
(140, 127, 0, '/home/howlate/public_html/master/api.php', 38, '203.208.65.204', 'Parameter ver must be supplied', '2014-07-17 07:03:58'),
(141, 127, 0, '/home/howlate/public_html/master/api.php', 74, '203.208.65.204', 'API Error: method "get_wsvc" is not known', '2014-07-17 07:04:14'),
(142, 127, 0, '/home/howlate/public_html/master/api.php', 267, '203.122.224.228', 'API Error: Method getpract the following mandatory parameters were not supplied: 1', '2014-07-18 01:27:22'),
(143, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 160, '203.122.224.228', 'Data Error: Organisation with IDAAAFF does not exist.', '2014-07-18 01:28:31'),
(144, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 169, '203.122.224.228', 'Data Error: Practitioner with ID A/$metadata does not exist for organisationAAADD', '2014-07-18 01:29:38'),
(145, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 169, '203.122.224.228', 'Data Error: Practitioner with ID A/mex does not exist for organisationAAADD', '2014-07-18 01:29:39'),
(146, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 169, '203.122.224.228', 'Data Error: Practitioner with ID A/$metadata does not exist for organisationAAADD', '2014-07-18 01:30:13'),
(147, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 169, '203.122.224.228', 'Data Error: Practitioner with ID A/mex does not exist for organisationAAADD', '2014-07-18 01:30:14'),
(148, 127, 0, '/home/howlate/public_html/master/api.php', 67, '203.122.224.228', 'API Error: method "getwsvccmds" is not known', '2014-07-18 11:47:33'),
(149, 127, 0, '/home/howlate/public_html/master/api.php', 279, '203.122.224.228', 'API Error: Method getwinsvccmds the following mandatory parameters were not supplied: 1', '2014-07-18 11:47:48'),
(150, 127, 0, '/home/howlate/public_html/master/api.php', 279, '203.122.224.228', 'API Error: Method getwinsvccmds the following mandatory parameters were not supplied: 1', '2014-07-18 11:48:43'),
(151, 127, 0, '/home/howlate/public_html/master/api.php', 279, '203.122.224.228', 'API Error: Method getwinsvccmds the following mandatory parameters were not supplied: 1', '2014-07-18 11:49:08'),
(152, 127, 0, '/home/howlate/public_html/master/api.php', 279, '203.122.224.228', 'API Error: Method getwinsvccmds the following mandatory parameters were not supplied: Array', '2014-07-18 11:49:26'),
(153, 127, 0, '/home/howlate/public_html/master/api.php', 279, '203.122.224.228', 'API Error: Method getwinsvccmds the following mandatory parameters were not supplied: ', '2014-07-18 11:49:45'),
(154, 127, 0, '/home/howlate/public_html/master/api.php', 279, '203.122.224.228', 'API Error: Method getwinsvccmds the following mandatory parameters were not supplied: 1', '2014-07-18 11:50:58'),
(155, 127, 0, '/home/howlate/public_html/master/api.php', 278, '203.122.224.228', 'API Error: Method getwinsvccmds the following mandatory parameters were not supplied: 1', '2014-07-18 11:51:33'),
(156, 127, 0, '/home/howlate/public_html/master/api.php', 278, '203.122.224.228', 'API Error: Method <b>getwinsvccmds</b> the following mandatory parameters were not supplied: 1', '2014-07-18 11:51:58'),
(157, 127, 0, '/home/howlate/public_html/master/api.php', 278, '203.122.224.228', 'API Error: Method <b>getwinsvccmds</b> the following mandatory parameters were not supplied: Array\n(\n    [0] => pin\n)\n', '2014-07-18 11:53:02'),
(158, 127, 0, '/home/howlate/public_html/master/api.php', 278, '203.122.224.228', 'API Error: Method <b>getwinsvccmds</b> the following mandatory parameters were not supplied: Array', '2014-07-18 11:54:01'),
(159, 127, 0, '/home/howlate/public_html/master/api.php', 278, '203.122.224.228', 'API Error: Method <b>getwinsvccmds</b> the following mandatory parameters were not supplied: pin', '2014-07-18 11:55:04'),
(160, 127, 0, '/home/howlate/public_html/master/api.php', 276, '203.122.224.228', 'API Error: Method <b>getwinsvccmds</b> the following mandatory parameters were not supplied: sys', '2014-07-18 11:56:42'),
(161, 127, 0, '/home/howlate/public_html/master/api.php', 28, '203.122.224.228', 'Parameter ver must be supplied', '2014-07-30 06:48:32'),
(162, 127, 0, '/home/howlate/public_html/master/api.php', 67, '203.122.224.228', 'API Error: method "getcountries" is not known', '2014-07-30 06:49:11'),
(163, 127, 0, '/home/howlate/public_html/master/api.php', 67, '203.122.224.228', 'API Error: method "getcountries" is not known', '2014-07-30 06:49:35'),
(164, 127, 0, '/home/howlate/public_html/master/api.php', 283, '203.122.224.228', 'API Error: Method <b>getclinics</b> the following mandatory parameters were not supplied: pin', '2014-07-30 06:50:14'),
(165, 127, 0, '/home/howlate/public_html/master/api.php', 67, '203.122.224.228', 'API Error: method "getcountries" is not known', '2014-07-30 06:51:26'),
(166, 127, 0, '/home/howlate/public_html/master/api.php', 67, '203.122.224.228', 'API Error: method "getcountries" is not known', '2014-07-30 06:51:43'),
(167, 127, 0, '/home/howlate/public_html/master/api.php', 294, '203.122.224.228', 'API Error: Method <b>getpract</b> the following mandatory parameters were not supplied: pin', '2014-07-30 07:16:09'),
(168, 127, 0, '/home/howlate/public_html/master/api.php', 69, '203.122.224.228', 'API Error: method "getpractitioners" is not known', '2014-07-30 07:16:14'),
(169, 127, 0, '/home/howlate/public_html/master/api.php', 69, '203.122.224.228', 'API Error: method "getpractitioner" is not known', '2014-07-30 07:16:19'),
(170, 127, 0, '/home/howlate/public_html/master/api.php', 65, '203.122.224.228', 'API Error: method "getorgs" is not known', '2014-07-30 08:26:41'),
(171, 127, 0, '/home/howlate/public_html/master/api.php', 65, '203.122.224.228', 'API Error: method "getorgs" is not known', '2014-07-30 08:29:58'),
(172, 127, 0, '/home/howlate/public_html/master/api.php', 22, '203.122.224.228', 'Parameter ver must be get or post', '2014-07-31 04:55:18'),
(173, 127, 0, '/home/howlate/public_html/master/api.php', 20, '203.122.224.228', 'Parameter ver must be get or post', '2014-07-31 05:07:18'),
(174, 127, 0, '/home/howlate/public_html/master/api.php', 305, '203.122.224.228', 'API Error: Method <b>get</b> the following mandatory parameters were not supplied: udid', '2014-07-31 05:12:13'),
(175, 127, 0, '/home/howlate/public_html/master/api.php', 305, '203.122.224.228', 'API Error: Method <b>get</b> the following mandatory parameters were not supplied: udid', '2014-07-31 05:12:31'),
(176, 127, 0, '/home/howlate/public_html/master/api.php', 305, '203.122.224.228', 'API Error: Method <b>get</b> the following mandatory parameters were not supplied: udid', '2014-07-31 05:12:43'),
(177, 127, 0, '/home/howlate/public_html/master/api.php', 305, '203.122.224.228', 'API Error: Method <b>get</b> the following mandatory parameters were not supplied: udid', '2014-07-31 05:12:48'),
(178, 127, 0, '/home/howlate/public_html/master/api.php', 18, '203.122.212.28', 'Parameter met (method) must be supplied', '2014-08-01 00:42:58'),
(179, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner with ID  does not exist for organisation', '2014-08-01 00:45:43'),
(180, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner with ID  does not exist for organisation', '2014-08-01 00:46:56'),
(181, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 00:59:52'),
(182, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 01:04:21'),
(183, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 01:06:31'),
(184, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 01:07:01'),
(185, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 01:07:58'),
(186, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 01:09:32'),
(187, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 01:10:06'),
(188, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 01:12:14'),
(189, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 01:13:42'),
(190, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner  does not exist for organisation ', '2014-08-01 01:14:52'),
(191, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner Dr Anthony Albanese does not exist for organisation AAADD', '2014-08-01 03:08:05'),
(192, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner Dr Anthony Albanese does not exist for organisation AAADD', '2014-08-01 03:35:27'),
(193, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner Dr Anthony Albanese does not exist for organisation AAADD', '2014-08-01 03:36:52'),
(194, 127, 0, '/home/howlate/public_html/master/api.php', 310, '203.122.212.28', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: newlate', '2014-08-01 03:36:57'),
(195, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner Dr Anthony Albanese does not exist for organisation AAADD', '2014-08-01 03:41:27'),
(196, 127, 0, '/home/howlate/public_html/master/api.php', 310, '203.122.212.28', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: newlate', '2014-08-01 03:42:01'),
(197, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner Dr Anthony Albanese does not exist for organisation AAADD', '2014-08-01 03:44:53'),
(198, 127, 0, '/home/howlate/public_html/master/api.php', 311, '203.122.212.28', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: newlate', '2014-08-01 03:45:10'),
(199, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner Dr Anthony Albanese does not exist for organisation AAADD', '2014-08-01 03:46:52'),
(200, 127, 0, '/home/howlate/public_html/master/api.php', 311, '203.122.212.28', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: newlate', '2014-08-01 03:47:07'),
(201, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 78, '203.122.212.28', 'Data Error: Practitioner Dr Anthony Albanese does not exist for organisation AAADD', '2014-08-01 04:01:19'),
(202, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgcredentialspractitionernewlate', '2014-08-02 06:36:47'),
(203, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:45:23'),
(204, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:45:30'),
(205, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:45:50'),
(206, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:46:10'),
(207, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:46:30'),
(208, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:46:50'),
(209, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:47:10'),
(210, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:47:30'),
(211, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:47:50'),
(212, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:48:10'),
(213, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:48:30'),
(214, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:48:50'),
(215, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:49:10'),
(216, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:49:30'),
(217, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:49:50'),
(218, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:50:10'),
(219, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:50:30'),
(220, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:50:50'),
(221, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:51:10'),
(222, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:51:30'),
(223, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:51:50'),
(224, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:52:10'),
(225, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:52:30'),
(226, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:52:50'),
(227, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:53:10'),
(228, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:53:30'),
(229, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:53:50'),
(230, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:54:37'),
(231, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:54:50'),
(232, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:55:10'),
(233, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:57:39'),
(234, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:57:56'),
(235, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:58:16'),
(236, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:58:36'),
(237, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:58:56'),
(238, 127, 0, '/home/howlate/public_html/master/api.php', 312, '203.122.224.228', 'API Error: Method <b>upd</b> the following mandatory parameters were not supplied: orgpractitionernewlate', '2014-08-02 07:59:16'),
(239, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 11, '203.122.224.228', 'Parameter met (method) must be supplied', '2014-08-02 08:08:32'),
(240, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 11, '203.122.224.228', 'Parameter met (method) must be supplied', '2014-08-02 08:09:42'),
(241, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 12, '203.122.224.228', 'Parameter ver (get/post) must be supplied', '2014-08-02 08:09:52'),
(242, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 15, '203.122.224.228', 'Parameter ver (get/post) must be supplied', '2014-08-02 08:15:09'),
(243, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 15, '203.122.224.228', 'Parameter ver (get/post) must be supplied', '2014-08-02 08:15:10'),
(244, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 65, '203.122.224.228', 'API Error: method "" is not known', '2014-08-02 08:20:25'),
(245, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 68, '203.122.224.228', 'API Error: method "" is not known', '2014-08-02 08:21:15'),
(246, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 166, '203.122.224.228', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-03 05:06:00'),
(247, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 166, '203.122.224.228', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-03 05:07:42'),
(248, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:41:47'),
(249, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:41:57'),
(250, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:42:07'),
(251, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:42:17'),
(252, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:42:27'),
(253, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:42:37'),
(254, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:42:47'),
(255, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:42:57'),
(256, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:43:07'),
(257, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:43:18'),
(258, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:43:27'),
(259, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:43:37'),
(260, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:43:48'),
(261, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:43:58'),
(262, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:44:07'),
(263, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:44:18'),
(264, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:44:28'),
(265, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:44:38'),
(266, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:44:48'),
(267, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:44:58'),
(268, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:45:08'),
(269, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:45:18'),
(270, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:45:28'),
(271, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:45:38'),
(272, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:45:48'),
(273, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:45:58'),
(274, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:46:08'),
(275, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:46:18'),
(276, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:46:28'),
(277, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:46:38'),
(278, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:46:48'),
(279, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:46:58'),
(280, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:47:08'),
(281, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:47:18'),
(282, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:47:28'),
(283, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:47:38'),
(284, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:47:48'),
(285, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:47:58'),
(286, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:48:08'),
(287, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:48:18'),
(288, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:48:28'),
(289, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:48:38'),
(290, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:48:48'),
(291, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:48:58'),
(292, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:49:08'),
(293, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.224.228', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-03 05:49:18'),
(294, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 273, '203.208.65.204', 'Data Error: User  does not exist for org ', '2014-08-19 06:42:23'),
(295, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 273, '203.208.65.204', 'Data Error: User  does not exist for org ', '2014-08-19 06:44:48'),
(296, 127, 0, '/home/howlate/public_html/master/controller/configfileController.php', 9, '203.208.65.204', 'User session variable not defined.', '2014-08-19 06:46:18'),
(297, 127, 0, '/home/howlate/public_html/master/controller/configfileController.php', 9, '203.208.65.204', 'User session variable not defined.', '2014-08-19 06:46:40'),
(298, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 273, '203.208.65.204', 'Data Error: User alexf does not exist for org ', '2014-08-19 06:47:25'),
(299, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 273, '203.208.65.204', 'Data Error: User alexf does not exist for org ', '2014-08-19 06:48:38'),
(300, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 273, '203.208.65.204', 'Data Error: User alexf does not exist for org ', '2014-08-19 06:48:42'),
(301, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 273, '203.208.65.204', 'Data Error: User alexf does not exist for org ', '2014-08-19 06:51:08'),
(302, 127, 0, '/home/howlate/public_html/master/views/howlateagent_exefile.php', 17, '203.208.65.204', 'File /includes/downloads/HowLateAgent.exe does not exist.', '2014-08-19 07:10:23'),
(303, 127, 0, '/home/howlate/public_html/master/views/howlateagent_exefile.php', 17, '203.208.65.204', 'File /master/includes/downloads/HowLateAgent.exe does not exist.', '2014-08-19 07:10:49'),
(304, 127, 0, '/home/howlate/public_html/master/views/howlateagent_exefile.php', 17, '203.208.65.204', 'File master/includes/downloads/HowLateAgent.exe does not exist.', '2014-08-19 07:10:58'),
(305, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 376, '203.122.224.228', 'The orgs record was not updated, error= ', '2014-08-19 08:00:40'),
(306, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:56:04'),
(307, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:56:23'),
(308, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:56:43'),
(309, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:57:03'),
(310, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:57:23'),
(311, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:57:43'),
(312, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:58:03'),
(313, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:58:23'),
(314, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:58:43'),
(315, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:59:03'),
(316, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:59:23'),
(317, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 12:59:43'),
(318, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:00:03'),
(319, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:00:23'),
(320, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:00:43'),
(321, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:01:03'),
(322, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:01:43'),
(323, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:01:53'),
(324, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:02:03'),
(325, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:02:23'),
(326, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:02:43'),
(327, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:03:03'),
(328, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:03:23'),
(329, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:03:43'),
(330, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:04:03'),
(331, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:04:23'),
(332, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:04:43'),
(333, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:05:03'),
(334, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:05:23'),
(335, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:05:43'),
(336, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:06:03'),
(337, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:06:23'),
(338, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:06:43'),
(339, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:07:03'),
(340, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:07:23'),
(341, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:07:43'),
(342, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:08:03'),
(343, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:08:23'),
(344, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:08:43'),
(345, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:09:03'),
(346, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:09:23'),
(347, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 167, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:09:43'),
(348, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:10:03'),
(349, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:10:23'),
(350, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:10:23'),
(351, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:10:43'),
(352, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:10:43'),
(353, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:11:03'),
(354, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:11:03'),
(355, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:11:23'),
(356, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:11:23'),
(357, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:11:43'),
(358, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:11:43'),
(359, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:12:03'),
(360, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:12:03'),
(361, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:12:23'),
(362, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:12:23'),
(363, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:12:43'),
(364, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:12:43'),
(365, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:13:03'),
(366, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:13:03'),
(367, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:13:23'),
(368, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:13:23'),
(369, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:13:43'),
(370, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:13:43'),
(371, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:14:03'),
(372, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:14:03'),
(373, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:14:23'),
(374, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:14:23'),
(375, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:14:43'),
(376, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:14:43'),
(377, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:15:03'),
(378, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:15:03'),
(379, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:15:23'),
(380, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:15:23'),
(381, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:15:43'),
(382, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:15:44'),
(383, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:16:03'),
(384, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:16:03'),
(385, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:16:23'),
(386, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:16:23'),
(387, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:16:43'),
(388, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:16:43');
INSERT INTO `errorlog` (`Id`, `ErrLevel`, `ErrType`, `File`, `Line`, `IPv4`, `ErrMessage`, `Created`) VALUES
(389, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:17:03'),
(390, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:17:03'),
(391, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:17:23'),
(392, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:17:23'),
(393, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:17:43'),
(394, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:17:43'),
(395, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:18:03'),
(396, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:18:03'),
(397, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:18:23'),
(398, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:18:23'),
(399, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:18:43'),
(400, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:18:43'),
(401, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:19:03'),
(402, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:19:04'),
(403, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:19:23'),
(404, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:19:23'),
(405, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:19:43'),
(406, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:19:43'),
(407, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:20:03'),
(408, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:20:03'),
(409, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:20:23'),
(410, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:20:23'),
(411, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:20:43'),
(412, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:20:44'),
(413, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:21:03'),
(414, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:21:03'),
(415, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:21:23'),
(416, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:21:24'),
(417, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:21:43'),
(418, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:21:43'),
(419, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:22:03'),
(420, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:22:04'),
(421, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:22:23'),
(422, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:22:24'),
(423, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:22:43'),
(424, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:22:44'),
(425, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:23:03'),
(426, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:23:04'),
(427, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:23:23'),
(428, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:23:24'),
(429, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:23:43'),
(430, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:23:44'),
(431, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:24:03'),
(432, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:24:04'),
(433, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:24:23'),
(434, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:24:24'),
(435, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:24:43'),
(436, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:24:44'),
(437, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:25:03'),
(438, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:25:04'),
(439, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:25:23'),
(440, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:25:24'),
(441, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:25:43'),
(442, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:25:44'),
(443, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:26:03'),
(444, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:26:04'),
(445, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:26:24'),
(446, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:26:24'),
(447, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:26:43'),
(448, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:26:44'),
(449, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:27:03'),
(450, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:27:04'),
(451, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:27:23'),
(452, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:27:24'),
(453, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:27:43'),
(454, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:27:44'),
(455, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:28:03'),
(456, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:28:04'),
(457, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:28:23'),
(458, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:28:24'),
(459, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:28:44'),
(460, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:28:44'),
(461, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:29:04'),
(462, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:29:04'),
(463, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:29:24'),
(464, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:29:24'),
(465, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:29:44'),
(466, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:29:44'),
(467, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:30:03'),
(468, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:30:04'),
(469, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:30:24'),
(470, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:30:24'),
(471, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:30:44'),
(472, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:30:44'),
(473, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:31:04'),
(474, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:31:04'),
(475, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:31:24'),
(476, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:31:24'),
(477, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:31:44'),
(478, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:31:44'),
(479, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:32:04'),
(480, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:32:04'),
(481, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:32:24'),
(482, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:32:24'),
(483, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:32:44'),
(484, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:32:44'),
(485, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:33:04'),
(486, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:33:04'),
(487, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:33:24'),
(488, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:33:24'),
(489, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:33:44'),
(490, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 13:33:44'),
(491, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:13:15'),
(492, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:13:27'),
(493, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:13:47'),
(494, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:14:07'),
(495, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:14:27'),
(496, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:14:28'),
(497, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:14:47'),
(498, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:14:48'),
(499, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:15:07'),
(500, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:15:08'),
(501, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:15:27'),
(502, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:15:28'),
(503, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:15:47'),
(504, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:15:48'),
(505, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:16:07'),
(506, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:16:08'),
(507, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:16:28'),
(508, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:16:28'),
(509, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:25:17'),
(510, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:25:37'),
(511, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:25:57'),
(512, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:26:17'),
(513, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:26:37'),
(514, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 165, '203.122.212.28', '# Query Error (1048) Column ''OrgID'' cannot be null', '2014-08-19 14:26:57'),
(515, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 15, '203.122.212.28', 'Parameter met (method) must be supplied', '2014-08-19 15:07:58'),
(516, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 18, '203.122.212.28', 'Parameter ver (get/post) must be supplied', '2014-08-19 15:08:07'),
(517, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 69, '203.122.212.28', 'API Error: method "" is not known', '2014-08-19 15:08:26'),
(518, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 69, '203.122.212.28', 'API Error: method "" is not known', '2014-08-19 15:12:07'),
(519, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 15, '203.122.212.28', 'Parameter met (method) must be supplied', '2014-08-19 15:12:17'),
(520, 127, 0, '/home/howlate/public_html/master/controller/apiController.php', 15, '203.122.212.28', 'Parameter met (method) must be supplied', '2014-08-19 15:12:53'),
(521, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 59, '203.122.212.28', 'API Error: <b>$met</b> - you must supply the $pin parameter <br>', '2014-08-19 15:13:51'),
(522, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 63, '203.122.212.28', 'API Error: <b>$met</b> - you must supply the $pin parameter <br>', '2014-08-19 15:16:37'),
(523, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 60, '203.122.212.28', 'API Error: <b>$met</b> - you must supply the $pin parameter <br>', '2014-08-19 15:18:43'),
(524, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 60, '203.122.212.28', 'API Error: <b>reg</b> - you must supply the  parameter <br>', '2014-08-19 15:19:39'),
(525, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 60, '203.122.212.28', 'API Error: <b>reg</b> - you must supply the pin parameter <br>', '2014-08-19 15:20:10'),
(526, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 60, '203.122.212.28', 'API Error: <b>reg</b> - you must supply the pin parameter <br>', '2014-08-19 15:20:31'),
(527, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 60, '203.122.212.28', 'API Error: <b>reg</b> - you must supply the pin parameter <br>', '2014-08-19 15:21:16'),
(528, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 60, '203.122.212.28', 'API Error: <b>reg</b> - you must supply the pin parameter <br>', '2014-08-19 15:21:18'),
(529, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 63, '203.122.212.28', 'API Error: <b>reg</b> - you must supply the udid parameter <br>', '2014-08-19 15:21:58'),
(530, 127, 0, '/home/howlate/public_html/master/model/howlate_api.class.php', 63, '203.122.212.28', 'API Error: <b>reg</b> - you must supply the udid parameter <br>', '2014-08-19 15:22:42'),
(531, 127, 0, '/home/howlate/public_html/master/api.php', 19, '203.208.65.204', 'Parameter met (method) must be supplied', '2014-08-22 04:58:18'),
(532, 127, 0, '/home/howlate/public_html/master/controller/agentController.php', 55, '203.122.212.28', 'User session variable not defined.', '2014-08-27 23:55:31'),
(533, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:46'),
(534, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:46'),
(535, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:46'),
(536, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:46'),
(537, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:47'),
(538, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:47'),
(539, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:47'),
(540, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:47'),
(541, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:48'),
(542, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 551, '203.122.212.28', '# Query Error (1048) Column ''ID'' cannot be null', '2014-08-30 06:21:48'),
(543, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 374, '203.122.212.28', 'The orgs record was not updated, error= ', '2014-08-31 00:39:52'),
(544, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 375, '203.122.212.28', 'The orgs record was not updated, error= ', '2014-08-31 00:40:33'),
(545, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 375, '203.122.212.28', 'The orgs record was not updated, error= ', '2014-08-31 00:41:36'),
(546, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 375, '203.122.212.28', 'The orgs record was not updated, error= ', '2014-08-31 00:42:18'),
(547, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 374, '203.122.212.28', 'The orgs record was not updated, error= ', '2014-08-31 00:52:29'),
(548, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 374, '203.122.212.28', 'The orgs record was not updated, error= ', '2014-08-31 00:52:48'),
(549, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 374, '203.122.212.28', 'The orgs record was not updated, error= ', '2014-08-31 00:53:32'),
(550, 127, 0, '/home/howlate/public_html/master/model/howlate_db.class.php', 374, '203.122.212.28', 'The orgs record was not updated, error= ', '2014-08-31 00:53:35');

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Lateness records' AUTO_INCREMENT=711 ;

--
-- Dumping data for table `lates`
--

INSERT INTO `lates` (`UKey`, `OrgID`, `ID`, `Updated`, `Minutes`) VALUES
(113, 'AAAHH', 'A', '2014-08-31 10:52:58', 5),
(710, 'AAADD', 'B', '2014-08-31 14:12:37', 18);

-- --------------------------------------------------------

--
-- Table structure for table `nums`
--

DROP TABLE IF EXISTS `nums`;
CREATE TABLE IF NOT EXISTS `nums` (
  `num` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`num`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=118 ;

--
-- Dumping data for table `nums`
--

INSERT INTO `nums` (`num`) VALUES
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9),
(10),
(11),
(12),
(13),
(14),
(15),
(16),
(17),
(18),
(19),
(20),
(21),
(22),
(23),
(24),
(25),
(26),
(27),
(28),
(29),
(30),
(31),
(32),
(33),
(34),
(35),
(36),
(37),
(38),
(39),
(40),
(41),
(42),
(43),
(44),
(45),
(46),
(47),
(48),
(49),
(50),
(51),
(52),
(53),
(54),
(55),
(56),
(57),
(58),
(59),
(60),
(61),
(62),
(63),
(64),
(65),
(66),
(67),
(68),
(69),
(70),
(71),
(72),
(73),
(74),
(75),
(76),
(77),
(78),
(79),
(80),
(81),
(82),
(83),
(84),
(85),
(86),
(87),
(88),
(89),
(90),
(91),
(92),
(93),
(94),
(95),
(96),
(97),
(98),
(99),
(100),
(101),
(102),
(103),
(104),
(105),
(106),
(107),
(108),
(109),
(110),
(111),
(112),
(113),
(114),
(115),
(116),
(117);

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
  PRIMARY KEY (`OrgID`),
  UNIQUE KEY `OrgID` (`OrgID`),
  UNIQUE KEY `OrgID_2` (`OrgID`),
  UNIQUE KEY `Subdomain` (`Subdomain`),
  UNIQUE KEY `FQDN` (`FQDN`),
  UNIQUE KEY `FQDN_2` (`FQDN`),
  KEY `Subdomain_2` (`Subdomain`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orgs`
--

INSERT INTO `orgs` (`OrgID`, `OrgName`, `OrgShortName`, `TaxID`, `Subdomain`, `FQDN`, `BillingRef`, `Address1`, `Address2`, `City`, `Zip`, `Country`, `Timezone`, `UpdIndic`) VALUES
('AAADD', 'Margate Clinic', 'Margate Clinic', '', 'margateclinic', 'margateclinic.how-late.com', '', 'Margate', '', 'Margate', '7777', 'Australia', 'Australia/Hobart                   ', 14),
('AAAEE', 'Fiedler Medical', 'Fiedler Medical', '', 'fiedlermedical', 'fiedlermedical.how-late.com', '', '7 Melrose Ave', '', 'Beulah Park', '5067', 'USA', '', 2),
('AAAFF', 'megaclinic', 'megaclinic', '', 'megaclinic', 'megaclinic.how-late.com', '', '', '', '', '', 'Australia', '', 1),
('AAAGG', 'deletemeMC', 'deletemeMC', '', 'deletememc', 'deletememc.how-late.com', '', '', '', '', '', '', '', 1),
('AAAHH', 'Hastings Medical Centre', 'HMC', '', 'hmc', 'hmc.how-late.com', '', '', '', '', '', 'Australia', 'Australia/NSW                      ', 3);

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
  UNIQUE KEY `OrgID` (`OrgID`,`EmailAddress`),
  UNIQUE KEY `OrgID_2` (`OrgID`,`UserID`),
  UNIQUE KEY `OrgID_3` (`OrgID`,`EmailAddress`),
  UNIQUE KEY `OrgID_4` (`OrgID`,`UserID`),
  UNIQUE KEY `OrgID_5` (`OrgID`,`EmailAddress`),
  KEY `UserID` (`UserID`),
  KEY `EmailAddress` (`EmailAddress`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orgusers`
--

INSERT INTO `orgusers` (`OrgID`, `UserID`, `FullName`, `EmailAddress`, `XPassword`, `SecretQuestion1`, `SecretAnswer1`, `DateCreated`) VALUES
('AAADD', 'alexf', 'Alex Fiedler', 'alex@fiedlerconsulting.com.au', '9cbf8a4dcb8e30682b927f352d6559a0', '', '', '2014-07-13 11:44:41'),
('AAAEE', 'alexf', 'Alex Fiedler', 'alex@fiedlerconsulting.com.au', '9cbf8a4dcb8e30682b927f352d6559a0', '', '', '2014-07-13 11:44:41'),
('AAAFF', 'Alex.Fiedle', '', 'Alex.Fiedler@internode.on.net', '', '', '', '2014-07-29 07:26:13'),
('AAAGG', 'alex.fiedle', '', 'alex.fiedler@solarhome.com.au', '', '', '', '2014-08-20 11:57:43'),
('AAAHH', 'gerard', 'Gerard Lill', 'Gerard@etsau.biz', '9cbf8a4dcb8e30682b927f352d6559a0', '', '', '2014-08-22 04:33:33');

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

--
-- Dumping data for table `placements`
--

INSERT INTO `placements` (`OrgID`, `ID`, `ClinicID`, `SurrogKey`) VALUES
('AAADD', 'A', 31, ''),
('AAADD', 'B', 31, ''),
('AAADD', 'C', 32, ''),
('AAAEE', 'A', 20, ''),
('AAAGG', 'A', 28, ''),
('AAAHH', 'A', 29, ''),
('AAAHH', 'B', 29, ''),
('AAAHH', 'C', 29, ''),
('AAAHH', 'D', 29, ''),
('AAAHH', 'E', 29, ''),
('AAAHH', 'F', 29, ''),
('AAAHH', 'G', 29, ''),
('AAAHH', 'H', 29, '');

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=200 ;

--
-- Dumping data for table `practitioners`
--

INSERT INTO `practitioners` (`OrgID`, `ID`, `FirstName`, `LastName`, `FullName`, `AbbrevName`, `DateCreated`, `SurrogKey`, `IntegrKey`, `NotificationThreshold`, `LateToNearest`, `LatenessOffset`) VALUES
('AAAEE', 'A', 'Alex', 'Fiedler', 'Dr A Fiedler', 'Dr A Fiedler', '0000-00-00 00:00:00', 39, '2233', 0, 0, 0),
('AAAFF', 'A', '', '', 'Alex.Fiedler@internode.on.net', 'Alex.Fiedler@interno', '2014-07-29 07:26:13', 41, NULL, 0, 0, 0),
('AAAGG', 'A', '', '', 'alex.fiedler@solarhome.com.au', 'alex.fiedler@solarho', '2014-08-20 11:57:43', 183, '', 0, 0, 0),
('AAAHH', 'A', '', '', 'Dr Philip Ewart', 'Dr Philip Ewart', '2014-08-29 05:14:46', 193, NULL, 25, 5, 10),
('AAAHH', 'B', '', '', 'Dr Badru Khamis', 'Dr Badru Khamis', '2014-08-29 05:14:47', 194, NULL, 25, 5, 10),
('AAAHH', 'C', '', '', 'Dr Sheik Sajjad Hayder', 'Dr Sheik Sajjad Hayd', '2014-08-29 05:14:47', 195, NULL, 25, 5, 10),
('AAAHH', 'D', '', '', 'Dr Jean Jagger', 'Dr Jean Jagger', '2014-08-29 05:14:48', 196, NULL, 25, 5, 10),
('AAADD', 'A', '', '', 'Dr Anthony Albanese', 'Dr Anthony Albanese', '2014-08-30 06:39:02', 197, '', 25, 5, 10),
('AAADD', 'B', '', '', 'Dr Natasha Litjens', 'Dr Natasha Litjens', '2014-08-30 06:39:04', 198, '', 25, 5, 10),
('AAADD', 'C', 'William', 'Mapother', 'Dr William Mapother', 'Dr W Mapother', '2014-08-30 14:00:00', 199, NULL, 25, 5, 10);

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

--
-- Dumping data for table `resetrequests`
--

INSERT INTO `resetrequests` (`Token`, `EmailAddress`, `OrgID`, `UserID`, `DateCreated`) VALUES
('0a6fab9f35b12823c4b02980557fdadc', 'gerard@etsau.biz', 'AAAHH', 'gerard', '2014-08-27 00:41:22'),
('1d8240cebd5a9ce678b9b3559cc117f1', 'alex.fiedler@internode.on.net', 'AAAHH', 'alex.fiedle', '2014-08-22 04:33:33'),
('2419f53b51c2060e85d3a4d523328c17', 'alex.fiedler@internode.on.net', 'AAAFF', 'Alex.Fiedle', '2014-08-18 05:37:02'),
('4b3ede7cd23ac25e3de13a212b3af335', 'alex.fiedler@internode.on.net', 'AAAFF', 'Alex.Fiedle', '2014-08-18 05:32:07'),
('551ff6f2db84ce74d533b705fcb95b78', 'Alex.Fiedler@internode.on.net', 'AAAFF', 'Alex.Fiedle', '2014-07-29 07:26:13'),
('8746675e12482efd3660b6e7460906a0', 'alex.fiedler@internode.on.net', 'AAAFF', 'Alex.Fiedle', '2014-08-18 05:39:11'),
('8ecf1d8ecfd4d6467deebb9f07ded9a9', 'alex.fiedler@internode.on.net', 'AAAFF', 'Alex.Fiedle', '2014-08-22 04:33:33'),
('a48dd231f6a857097e498655fb8520b3', 'alex@fiedlerconsulting.com.au', 'AAAEE', 'alex@fiedlerconsulting.com.au', '2014-07-13 11:44:41'),
('d31ec54e2add434747ff704a9990ac8b', 'Alex.Fiedler@internode.on.net', 'AAAFF', 'Alex.Fiedle', '2014-08-18 05:20:40'),
('d9b42d2cbc918b369beb59c04d23445d', 'alex.fiedler@solarhome.com.au', 'AAAGG', 'alex.fiedle', '2014-08-20 11:57:43'),
('e27abce63918935af4c2fb667d07c8db', 'alex.fiedler@internode.on.net', 'AAAFF', 'Alex.Fiedle', '2014-08-18 05:42:57');

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`OrgID`, `ID`, `Day`, `StartTime`, `EndTime`) VALUES
('AAADD', 'A', 'Friday    ', 25200, 82800),
('AAADD', 'A', 'Monday    ', 25200, 82800),
('AAADD', 'A', 'Thursday  ', 25200, 82800),
('AAADD', 'A', 'Tuesday   ', 25200, 82800),
('AAADD', 'A', 'Wednesday ', 25200, 82800),
('AAADD', 'B', 'Friday    ', 28800, 82800),
('AAADD', 'B', 'Monday    ', 28800, 82800),
('AAADD', 'B', 'Sunday    ', 25200, 66600),
('AAADD', 'B', 'Thursday  ', 28800, 82800),
('AAADD', 'B', 'Tuesday   ', 28800, 82800),
('AAADD', 'B', 'Wednesday ', 28800, 82800);

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

--
-- Dumping data for table `timezones`
--

INSERT INTO `timezones` (`CodeVal`, `CodeDesc`) VALUES
('Africa/Abidjan', 'Africa/Abidjan                     '),
('Africa/Accra', 'Africa/Accra                       '),
('Africa/Addis_Ababa', 'Africa/Addis_Ababa                 '),
('Africa/Algiers', 'Africa/Algiers                     '),
('Africa/Asmara', 'Africa/Asmara                      '),
('Africa/Asmera', 'Africa/Asmera                      '),
('Africa/Bamako', 'Africa/Bamako                      '),
('Africa/Bangui', 'Africa/Bangui                      '),
('Africa/Banjul', 'Africa/Banjul                      '),
('Africa/Bissau', 'Africa/Bissau                      '),
('Africa/Blantyre', 'Africa/Blantyre                    '),
('Africa/Brazzaville', 'Africa/Brazzaville                 '),
('Africa/Bujumbura', 'Africa/Bujumbura                   '),
('Africa/Cairo', 'Africa/Cairo                       '),
('Africa/Casablanca', 'Africa/Casablanca                  '),
('Africa/Ceuta', 'Africa/Ceuta                       '),
('Africa/Conakry', 'Africa/Conakry                     '),
('Africa/Dakar', 'Africa/Dakar                       '),
('Africa/Dar_es_Salaam', 'Africa/Dar_es_Salaam               '),
('Africa/Djibouti', 'Africa/Djibouti                    '),
('Africa/Douala', 'Africa/Douala                      '),
('Africa/El_Aaiun', 'Africa/El_Aaiun                    '),
('Africa/Freetown', 'Africa/Freetown                    '),
('Africa/Gaborone', 'Africa/Gaborone                    '),
('Africa/Harare', 'Africa/Harare                      '),
('Africa/Johannesburg', 'Africa/Johannesburg                '),
('Africa/Juba', 'Africa/Juba                        '),
('Africa/Kampala', 'Africa/Kampala                     '),
('Africa/Khartoum', 'Africa/Khartoum                    '),
('Africa/Kigali', 'Africa/Kigali                      '),
('Africa/Kinshasa', 'Africa/Kinshasa                    '),
('Africa/Lagos', 'Africa/Lagos                       '),
('Africa/Libreville', 'Africa/Libreville                  '),
('Africa/Lome', 'Africa/Lome                        '),
('Africa/Luanda', 'Africa/Luanda                      '),
('Africa/Lubumbashi', 'Africa/Lubumbashi                  '),
('Africa/Lusaka', 'Africa/Lusaka                      '),
('Africa/Malabo', 'Africa/Malabo                      '),
('Africa/Maputo', 'Africa/Maputo                      '),
('Africa/Maseru', 'Africa/Maseru                      '),
('Africa/Mbabane', 'Africa/Mbabane                     '),
('Africa/Mogadishu', 'Africa/Mogadishu                   '),
('Africa/Monrovia', 'Africa/Monrovia                    '),
('Africa/Nairobi', 'Africa/Nairobi                     '),
('Africa/Ndjamena', 'Africa/Ndjamena                    '),
('Africa/Niamey', 'Africa/Niamey                      '),
('Africa/Nouakchott', 'Africa/Nouakchott                  '),
('Africa/Ouagadougou', 'Africa/Ouagadougou                 '),
('Africa/Porto-Novo', 'Africa/Porto-Novo                  '),
('Africa/Sao_Tome', 'Africa/Sao_Tome                    '),
('Africa/Timbuktu', 'Africa/Timbuktu                    '),
('Africa/Tripoli', 'Africa/Tripoli                     '),
('Africa/Tunis', 'Africa/Tunis                       '),
('Africa/Windhoek', 'Africa/Windhoek                    '),
('America/Adak', 'America/Adak                       '),
('America/Anchorage', 'America/Anchorage                  '),
('America/Anguilla', 'America/Anguilla                   '),
('America/Antigua', 'America/Antigua                    '),
('America/Araguaina', 'America/Araguaina                  '),
('America/Argentina/Buenos_Aires', 'America/Argentina/Buenos_Aires     '),
('America/Argentina/Catamarca', 'America/Argentina/Catamarca        '),
('America/Argentina/ComodRivadavia', 'America/Argentina/ComodRivadavia   '),
('America/Argentina/Cordoba', 'America/Argentina/Cordoba          '),
('America/Argentina/Jujuy', 'America/Argentina/Jujuy            '),
('America/Argentina/La_Rioja', 'America/Argentina/La_Rioja         '),
('America/Argentina/Mendoza', 'America/Argentina/Mendoza          '),
('America/Argentina/Rio_Gallegos', 'America/Argentina/Rio_Gallegos     '),
('America/Argentina/Salta', 'America/Argentina/Salta            '),
('America/Argentina/San_Juan', 'America/Argentina/San_Juan         '),
('America/Argentina/San_Luis', 'America/Argentina/San_Luis         '),
('America/Argentina/Tucuman', 'America/Argentina/Tucuman          '),
('America/Argentina/Ushuaia', 'America/Argentina/Ushuaia          '),
('America/Aruba', 'America/Aruba                      '),
('America/Asuncion', 'America/Asuncion                   '),
('America/Atikokan', 'America/Atikokan                   '),
('America/Atka', 'America/Atka                       '),
('America/Bahia', 'America/Bahia                      '),
('America/Bahia_Banderas', 'America/Bahia_Banderas             '),
('America/Barbados', 'America/Barbados                   '),
('America/Belem', 'America/Belem                      '),
('America/Belize', 'America/Belize                     '),
('America/Blanc-Sablon', 'America/Blanc-Sablon               '),
('America/Boa_Vista', 'America/Boa_Vista                  '),
('America/Bogota', 'America/Bogota                     '),
('America/Boise', 'America/Boise                      '),
('America/Buenos_Aires', 'America/Buenos_Aires               '),
('America/Cambridge_Bay', 'America/Cambridge_Bay              '),
('America/Campo_Grande', 'America/Campo_Grande               '),
('America/Cancun', 'America/Cancun                     '),
('America/Caracas', 'America/Caracas                    '),
('America/Catamarca', 'America/Catamarca                  '),
('America/Cayenne', 'America/Cayenne                    '),
('America/Cayman', 'America/Cayman                     '),
('America/Chicago', 'America/Chicago                    '),
('America/Chihuahua', 'America/Chihuahua                  '),
('America/Coral_Harbour', 'America/Coral_Harbour              '),
('America/Cordoba', 'America/Cordoba                    '),
('America/Costa_Rica', 'America/Costa_Rica                 '),
('America/Creston', 'America/Creston                    '),
('America/Cuiaba', 'America/Cuiaba                     '),
('America/Curacao', 'America/Curacao                    '),
('America/Danmarkshavn', 'America/Danmarkshavn               '),
('America/Dawson', 'America/Dawson                     '),
('America/Dawson_Creek', 'America/Dawson_Creek               '),
('America/Denver', 'America/Denver                     '),
('America/Detroit', 'America/Detroit                    '),
('America/Dominica', 'America/Dominica                   '),
('America/Edmonton', 'America/Edmonton                   '),
('America/Eirunepe', 'America/Eirunepe                   '),
('America/El_Salvador', 'America/El_Salvador                '),
('America/Ensenada', 'America/Ensenada                   '),
('America/Fortaleza', 'America/Fortaleza                  '),
('America/Fort_Wayne', 'America/Fort_Wayne                 '),
('America/Glace_Bay', 'America/Glace_Bay                  '),
('America/Godthab', 'America/Godthab                    '),
('America/Goose_Bay', 'America/Goose_Bay                  '),
('America/Grand_Turk', 'America/Grand_Turk                 '),
('America/Grenada', 'America/Grenada                    '),
('America/Guadeloupe', 'America/Guadeloupe                 '),
('America/Guatemala', 'America/Guatemala                  '),
('America/Guayaquil', 'America/Guayaquil                  '),
('America/Guyana', 'America/Guyana                     '),
('America/Halifax', 'America/Halifax                    '),
('America/Havana', 'America/Havana                     '),
('America/Hermosillo', 'America/Hermosillo                 '),
('America/Indiana/Indianapolis', 'America/Indiana/Indianapolis       '),
('America/Indiana/Knox', 'America/Indiana/Knox               '),
('America/Indiana/Marengo', 'America/Indiana/Marengo            '),
('America/Indiana/Petersburg', 'America/Indiana/Petersburg         '),
('America/Indiana/Tell_City', 'America/Indiana/Tell_City          '),
('America/Indiana/Vevay', 'America/Indiana/Vevay              '),
('America/Indiana/Vincennes', 'America/Indiana/Vincennes          '),
('America/Indiana/Winamac', 'America/Indiana/Winamac            '),
('America/Indianapolis', 'America/Indianapolis               '),
('America/Inuvik', 'America/Inuvik                     '),
('America/Iqaluit', 'America/Iqaluit                    '),
('America/Jamaica', 'America/Jamaica                    '),
('America/Jujuy', 'America/Jujuy                      '),
('America/Juneau', 'America/Juneau                     '),
('America/Kentucky/Louisville', 'America/Kentucky/Louisville        '),
('America/Kentucky/Monticello', 'America/Kentucky/Monticello        '),
('America/Knox_IN', 'America/Knox_IN                    '),
('America/Kralendijk', 'America/Kralendijk                 '),
('America/La_Paz', 'America/La_Paz                     '),
('America/Lima', 'America/Lima                       '),
('America/Los_Angeles', 'America/Los_Angeles                '),
('America/Louisville', 'America/Louisville                 '),
('America/Lower_Princes', 'America/Lower_Princes              '),
('America/Maceio', 'America/Maceio                     '),
('America/Managua', 'America/Managua                    '),
('America/Manaus', 'America/Manaus                     '),
('America/Marigot', 'America/Marigot                    '),
('America/Martinique', 'America/Martinique                 '),
('America/Matamoros', 'America/Matamoros                  '),
('America/Mazatlan', 'America/Mazatlan                   '),
('America/Mendoza', 'America/Mendoza                    '),
('America/Menominee', 'America/Menominee                  '),
('America/Merida', 'America/Merida                     '),
('America/Metlakatla', 'America/Metlakatla                 '),
('America/Mexico_City', 'America/Mexico_City                '),
('America/Miquelon', 'America/Miquelon                   '),
('America/Moncton', 'America/Moncton                    '),
('America/Monterrey', 'America/Monterrey                  '),
('America/Montevideo', 'America/Montevideo                 '),
('America/Montreal', 'America/Montreal                   '),
('America/Montserrat', 'America/Montserrat                 '),
('America/Nassau', 'America/Nassau                     '),
('America/New_York', 'America/New_York                   '),
('America/Nipigon', 'America/Nipigon                    '),
('America/Nome', 'America/Nome                       '),
('America/Noronha', 'America/Noronha                    '),
('America/North_Dakota/Beulah', 'America/North_Dakota/Beulah        '),
('America/North_Dakota/Center', 'America/North_Dakota/Center        '),
('America/North_Dakota/New_Salem', 'America/North_Dakota/New_Salem     '),
('America/Ojinaga', 'America/Ojinaga                    '),
('America/Panama', 'America/Panama                     '),
('America/Pangnirtung', 'America/Pangnirtung                '),
('America/Paramaribo', 'America/Paramaribo                 '),
('America/Phoenix', 'America/Phoenix                    '),
('America/Port-au-Prince', 'America/Port-au-Prince             '),
('America/Porto_Acre', 'America/Porto_Acre                 '),
('America/Porto_Velho', 'America/Porto_Velho                '),
('America/Port_of_Spain', 'America/Port_of_Spain              '),
('America/Puerto_Rico', 'America/Puerto_Rico                '),
('America/Rainy_River', 'America/Rainy_River                '),
('America/Rankin_Inlet', 'America/Rankin_Inlet               '),
('America/Recife', 'America/Recife                     '),
('America/Regina', 'America/Regina                     '),
('America/Resolute', 'America/Resolute                   '),
('America/Rio_Branco', 'America/Rio_Branco                 '),
('America/Rosario', 'America/Rosario                    '),
('America/Santarem', 'America/Santarem                   '),
('America/Santa_Isabel', 'America/Santa_Isabel               '),
('America/Santiago', 'America/Santiago                   '),
('America/Santo_Domingo', 'America/Santo_Domingo              '),
('America/Sao_Paulo', 'America/Sao_Paulo                  '),
('America/Scoresbysund', 'America/Scoresbysund               '),
('America/Shiprock', 'America/Shiprock                   '),
('America/Sitka', 'America/Sitka                      '),
('America/St_Barthelemy', 'America/St_Barthelemy              '),
('America/St_Johns', 'America/St_Johns                   '),
('America/St_Kitts', 'America/St_Kitts                   '),
('America/St_Lucia', 'America/St_Lucia                   '),
('America/St_Thomas', 'America/St_Thomas                  '),
('America/St_Vincent', 'America/St_Vincent                 '),
('America/Swift_Current', 'America/Swift_Current              '),
('America/Tegucigalpa', 'America/Tegucigalpa                '),
('America/Thule', 'America/Thule                      '),
('America/Thunder_Bay', 'America/Thunder_Bay                '),
('America/Tijuana', 'America/Tijuana                    '),
('America/Toronto', 'America/Toronto                    '),
('America/Tortola', 'America/Tortola                    '),
('America/Vancouver', 'America/Vancouver                  '),
('America/Virgin', 'America/Virgin                     '),
('America/Whitehorse', 'America/Whitehorse                 '),
('America/Winnipeg', 'America/Winnipeg                   '),
('America/Yakutat', 'America/Yakutat                    '),
('America/Yellowknife', 'America/Yellowknife                '),
('Antarctica/Casey', 'Antarctica/Casey                   '),
('Antarctica/Davis', 'Antarctica/Davis                   '),
('Antarctica/DumontDUrville', 'Antarctica/DumontDUrville          '),
('Antarctica/Macquarie', 'Antarctica/Macquarie               '),
('Antarctica/Mawson', 'Antarctica/Mawson                  '),
('Antarctica/McMurdo', 'Antarctica/McMurdo                 '),
('Antarctica/Palmer', 'Antarctica/Palmer                  '),
('Antarctica/Rothera', 'Antarctica/Rothera                 '),
('Antarctica/South_Pole', 'Antarctica/South_Pole              '),
('Antarctica/Syowa', 'Antarctica/Syowa                   '),
('Antarctica/Vostok', 'Antarctica/Vostok                  '),
('Arctic/Longyearbyen', 'Arctic/Longyearbyen                '),
('Asia/Aden', 'Asia/Aden                          '),
('Asia/Almaty', 'Asia/Almaty                        '),
('Asia/Amman', 'Asia/Amman                         '),
('Asia/Anadyr', 'Asia/Anadyr                        '),
('Asia/Aqtau', 'Asia/Aqtau                         '),
('Asia/Aqtobe', 'Asia/Aqtobe                        '),
('Asia/Ashgabat', 'Asia/Ashgabat                      '),
('Asia/Ashkhabad', 'Asia/Ashkhabad                     '),
('Asia/Baghdad', 'Asia/Baghdad                       '),
('Asia/Bahrain', 'Asia/Bahrain                       '),
('Asia/Baku', 'Asia/Baku                          '),
('Asia/Bangkok', 'Asia/Bangkok                       '),
('Asia/Beirut', 'Asia/Beirut                        '),
('Asia/Bishkek', 'Asia/Bishkek                       '),
('Asia/Brunei', 'Asia/Brunei                        '),
('Asia/Calcutta', 'Asia/Calcutta                      '),
('Asia/Choibalsan', 'Asia/Choibalsan                    '),
('Asia/Chongqing', 'Asia/Chongqing                     '),
('Asia/Chungking', 'Asia/Chungking                     '),
('Asia/Colombo', 'Asia/Colombo                       '),
('Asia/Dacca', 'Asia/Dacca                         '),
('Asia/Damascus', 'Asia/Damascus                      '),
('Asia/Dhaka', 'Asia/Dhaka                         '),
('Asia/Dili', 'Asia/Dili                          '),
('Asia/Dubai', 'Asia/Dubai                         '),
('Asia/Dushanbe', 'Asia/Dushanbe                      '),
('Asia/Gaza', 'Asia/Gaza                          '),
('Asia/Harbin', 'Asia/Harbin                        '),
('Asia/Hebron', 'Asia/Hebron                        '),
('Asia/Hong_Kong', 'Asia/Hong_Kong                     '),
('Asia/Hovd', 'Asia/Hovd                          '),
('Asia/Ho_Chi_Minh', 'Asia/Ho_Chi_Minh                   '),
('Asia/Irkutsk', 'Asia/Irkutsk                       '),
('Asia/Istanbul', 'Asia/Istanbul                      '),
('Asia/Jakarta', 'Asia/Jakarta                       '),
('Asia/Jayapura', 'Asia/Jayapura                      '),
('Asia/Jerusalem', 'Asia/Jerusalem                     '),
('Asia/Kabul', 'Asia/Kabul                         '),
('Asia/Kamchatka', 'Asia/Kamchatka                     '),
('Asia/Karachi', 'Asia/Karachi                       '),
('Asia/Kashgar', 'Asia/Kashgar                       '),
('Asia/Kathmandu', 'Asia/Kathmandu                     '),
('Asia/Katmandu', 'Asia/Katmandu                      '),
('Asia/Khandyga', 'Asia/Khandyga                      '),
('Asia/Kolkata', 'Asia/Kolkata                       '),
('Asia/Krasnoyarsk', 'Asia/Krasnoyarsk                   '),
('Asia/Kuala_Lumpur', 'Asia/Kuala_Lumpur                  '),
('Asia/Kuching', 'Asia/Kuching                       '),
('Asia/Kuwait', 'Asia/Kuwait                        '),
('Asia/Macao', 'Asia/Macao                         '),
('Asia/Macau', 'Asia/Macau                         '),
('Asia/Magadan', 'Asia/Magadan                       '),
('Asia/Makassar', 'Asia/Makassar                      '),
('Asia/Manila', 'Asia/Manila                        '),
('Asia/Muscat', 'Asia/Muscat                        '),
('Asia/Nicosia', 'Asia/Nicosia                       '),
('Asia/Novokuznetsk', 'Asia/Novokuznetsk                  '),
('Asia/Novosibirsk', 'Asia/Novosibirsk                   '),
('Asia/Omsk', 'Asia/Omsk                          '),
('Asia/Oral', 'Asia/Oral                          '),
('Asia/Phnom_Penh', 'Asia/Phnom_Penh                    '),
('Asia/Pontianak', 'Asia/Pontianak                     '),
('Asia/Pyongyang', 'Asia/Pyongyang                     '),
('Asia/Qatar', 'Asia/Qatar                         '),
('Asia/Qyzylorda', 'Asia/Qyzylorda                     '),
('Asia/Rangoon', 'Asia/Rangoon                       '),
('Asia/Riyadh', 'Asia/Riyadh                        '),
('Asia/Saigon', 'Asia/Saigon                        '),
('Asia/Sakhalin', 'Asia/Sakhalin                      '),
('Asia/Samarkand', 'Asia/Samarkand                     '),
('Asia/Seoul', 'Asia/Seoul                         '),
('Asia/Shanghai', 'Asia/Shanghai                      '),
('Asia/Singapore', 'Asia/Singapore                     '),
('Asia/Taipei', 'Asia/Taipei                        '),
('Asia/Tashkent', 'Asia/Tashkent                      '),
('Asia/Tbilisi', 'Asia/Tbilisi                       '),
('Asia/Tehran', 'Asia/Tehran                        '),
('Asia/Tel_Aviv', 'Asia/Tel_Aviv                      '),
('Asia/Thimbu', 'Asia/Thimbu                        '),
('Asia/Thimphu', 'Asia/Thimphu                       '),
('Asia/Tokyo', 'Asia/Tokyo                         '),
('Asia/Ujung_Pandang', 'Asia/Ujung_Pandang                 '),
('Asia/Ulaanbaatar', 'Asia/Ulaanbaatar                   '),
('Asia/Ulan_Bator', 'Asia/Ulan_Bator                    '),
('Asia/Urumqi', 'Asia/Urumqi                        '),
('Asia/Ust-Nera', 'Asia/Ust-Nera                      '),
('Asia/Vientiane', 'Asia/Vientiane                     '),
('Asia/Vladivostok', 'Asia/Vladivostok                   '),
('Asia/Yakutsk', 'Asia/Yakutsk                       '),
('Asia/Yekaterinburg', 'Asia/Yekaterinburg                 '),
('Asia/Yerevan', 'Asia/Yerevan                       '),
('Atlantic/Azores', 'Atlantic/Azores                    '),
('Atlantic/Bermuda', 'Atlantic/Bermuda                   '),
('Atlantic/Canary', 'Atlantic/Canary                    '),
('Atlantic/Cape_Verde', 'Atlantic/Cape_Verde                '),
('Atlantic/Faeroe', 'Atlantic/Faeroe                    '),
('Atlantic/Faroe', 'Atlantic/Faroe                     '),
('Atlantic/Jan_Mayen', 'Atlantic/Jan_Mayen                 '),
('Atlantic/Madeira', 'Atlantic/Madeira                   '),
('Atlantic/Reykjavik', 'Atlantic/Reykjavik                 '),
('Atlantic/South_Georgia', 'Atlantic/South_Georgia             '),
('Atlantic/Stanley', 'Atlantic/Stanley                   '),
('Atlantic/St_Helena', 'Atlantic/St_Helena                 '),
('Australia/Adelaide', 'Australia/Adelaide'),
('Australia/Brisbane', 'Australia/Brisbane                 '),
('Australia/Broken_Hill', 'Australia/Broken_Hill              '),
('Australia/Canberra', 'Australia/Canberra                 '),
('Australia/Currie', 'Australia/Currie                   '),
('Australia/Darwin', 'Australia/Darwin                   '),
('Australia/Eucla', 'Australia/Eucla                    '),
('Australia/Hobart', 'Australia/Hobart                   '),
('Australia/LHI', 'Australia/LHI                      '),
('Australia/Lindeman', 'Australia/Lindeman                 '),
('Australia/Lord_Howe', 'Australia/Lord_Howe                '),
('Australia/Melbourne', 'Australia/Melbourne                '),
('Australia/North', 'Australia/North                    '),
('Australia/NSW', 'Australia/NSW                      '),
('Australia/Perth', 'Australia/Perth                    '),
('Australia/Queensland', 'Australia/Queensland               '),
('Australia/South', 'Australia/South                    '),
('Australia/Sydney', 'Australia/Sydney                   '),
('Australia/Tasmania', 'Australia/Tasmania                 '),
('Australia/Victoria', 'Australia/Victoria                 '),
('Australia/West', 'Australia/West                     '),
('Australia/Yancowinna', 'Australia/Yancowinna               '),
('Brazil/Acre', 'Brazil/Acre                        '),
('Brazil/DeNoronha', 'Brazil/DeNoronha                   '),
('Brazil/East', 'Brazil/East                        '),
('Brazil/West', 'Brazil/West                        '),
('Canada/Atlantic', 'Canada/Atlantic                    '),
('Canada/Central', 'Canada/Central                     '),
('Canada/East-Saskatchewan', 'Canada/East-Saskatchewan           '),
('Canada/Eastern', 'Canada/Eastern                     '),
('Canada/Mountain', 'Canada/Mountain                    '),
('Canada/Newfoundland', 'Canada/Newfoundland                '),
('Canada/Pacific', 'Canada/Pacific                     '),
('Canada/Saskatchewan', 'Canada/Saskatchewan                '),
('Canada/Yukon', 'Canada/Yukon                       '),
('CET', 'CET                                '),
('Chile/Continental', 'Chile/Continental                  '),
('Chile/EasterIsland', 'Chile/EasterIsland                 '),
('CST6CDT', 'CST6CDT                            '),
('Cuba', 'Cuba                               '),
('EET', 'EET                                '),
('Egypt', 'Egypt                              '),
('Eire', 'Eire                               '),
('Europe/Amsterdam', 'Europe/Amsterdam                   '),
('Europe/Andorra', 'Europe/Andorra                     '),
('Europe/Athens', 'Europe/Athens                      '),
('Europe/Belfast', 'Europe/Belfast                     '),
('Europe/Belgrade', 'Europe/Belgrade                    '),
('Europe/Berlin', 'Europe/Berlin                      '),
('Europe/Bratislava', 'Europe/Bratislava                  '),
('Europe/Brussels', 'Europe/Brussels                    '),
('Europe/Bucharest', 'Europe/Bucharest                   '),
('Europe/Budapest', 'Europe/Budapest                    '),
('Europe/Busingen', 'Europe/Busingen                    '),
('Europe/Chisinau', 'Europe/Chisinau                    '),
('Europe/Copenhagen', 'Europe/Copenhagen                  '),
('Europe/Dublin', 'Europe/Dublin                      '),
('Europe/Gibraltar', 'Europe/Gibraltar                   '),
('Europe/Guernsey', 'Europe/Guernsey                    '),
('Europe/Helsinki', 'Europe/Helsinki                    '),
('Europe/Isle_of_Man', 'Europe/Isle_of_Man                 '),
('Europe/Istanbul', 'Europe/Istanbul                    '),
('Europe/Jersey', 'Europe/Jersey                      '),
('Europe/Kaliningrad', 'Europe/Kaliningrad                 '),
('Europe/Kiev', 'Europe/Kiev                        '),
('Europe/Lisbon', 'Europe/Lisbon                      '),
('Europe/Ljubljana', 'Europe/Ljubljana                   '),
('Europe/London', 'Europe/London                      '),
('Europe/Luxembourg', 'Europe/Luxembourg                  '),
('Europe/Madrid', 'Europe/Madrid                      '),
('Europe/Malta', 'Europe/Malta                       '),
('Europe/Mariehamn', 'Europe/Mariehamn                   '),
('Europe/Minsk', 'Europe/Minsk                       '),
('Europe/Monaco', 'Europe/Monaco                      '),
('Europe/Moscow', 'Europe/Moscow                      '),
('Europe/Nicosia', 'Europe/Nicosia                     '),
('Europe/Oslo', 'Europe/Oslo                        '),
('Europe/Paris', 'Europe/Paris                       '),
('Europe/Podgorica', 'Europe/Podgorica                   '),
('Europe/Prague', 'Europe/Prague                      '),
('Europe/Riga', 'Europe/Riga                        '),
('Europe/Rome', 'Europe/Rome                        '),
('Europe/Samara', 'Europe/Samara                      '),
('Europe/San_Marino', 'Europe/San_Marino                  '),
('Europe/Sarajevo', 'Europe/Sarajevo                    '),
('Europe/Simferopol', 'Europe/Simferopol                  '),
('Europe/Skopje', 'Europe/Skopje                      '),
('Europe/Sofia', 'Europe/Sofia                       '),
('Europe/Stockholm', 'Europe/Stockholm                   '),
('Europe/Tallinn', 'Europe/Tallinn                     '),
('Europe/Tirane', 'Europe/Tirane                      '),
('Europe/Tiraspol', 'Europe/Tiraspol                    '),
('Europe/Uzhgorod', 'Europe/Uzhgorod                    '),
('Europe/Vaduz', 'Europe/Vaduz                       '),
('Europe/Vatican', 'Europe/Vatican                     '),
('Europe/Vienna', 'Europe/Vienna                      '),
('Europe/Vilnius', 'Europe/Vilnius                     '),
('Europe/Volgograd', 'Europe/Volgograd                   '),
('Europe/Warsaw', 'Europe/Warsaw                      '),
('Europe/Zagreb', 'Europe/Zagreb                      '),
('Europe/Zaporozhye', 'Europe/Zaporozhye                  '),
('Europe/Zurich', 'Europe/Zurich                      '),
('Greenwich', 'Greenwich                          '),
('Hongkong', 'Hongkong                           '),
('HST', 'HST                                '),
('Iceland', 'Iceland                            '),
('Indian/Antananarivo', 'Indian/Antananarivo                '),
('Indian/Chagos', 'Indian/Chagos                      '),
('Indian/Christmas', 'Indian/Christmas                   '),
('Indian/Cocos', 'Indian/Cocos                       '),
('Indian/Comoro', 'Indian/Comoro                      '),
('Indian/Kerguelen', 'Indian/Kerguelen                   '),
('Indian/Mahe', 'Indian/Mahe                        '),
('Indian/Maldives', 'Indian/Maldives                    '),
('Indian/Mauritius', 'Indian/Mauritius                   '),
('Indian/Mayotte', 'Indian/Mayotte                     '),
('Indian/Reunion', 'Indian/Reunion                     '),
('Iran', 'Iran                               '),
('Israel', 'Israel                             '),
('Jamaica', 'Jamaica                            '),
('Japan', 'Japan                              '),
('Kwajalein', 'Kwajalein                          '),
('Libya', 'Libya                              '),
('MET', 'MET                                '),
('Mexico/BajaNorte', 'Mexico/BajaNorte                   '),
('Mexico/BajaSur', 'Mexico/BajaSur                     '),
('Mexico/General', 'Mexico/General                     '),
('MST', 'MST                                '),
('MST7MDT', 'MST7MDT                            '),
('Navajo', 'Navajo                             '),
('NZ', 'NZ                                 '),
('NZ-CHAT', 'NZ-CHAT                            '),
('Pacific/Apia', 'Pacific/Apia                       '),
('Pacific/Auckland', 'Pacific/Auckland                   '),
('Pacific/Chatham', 'Pacific/Chatham                    '),
('Pacific/Chuuk', 'Pacific/Chuuk                      '),
('Pacific/Easter', 'Pacific/Easter                     '),
('Pacific/Efate', 'Pacific/Efate                      '),
('Pacific/Enderbury', 'Pacific/Enderbury                  '),
('Pacific/Fakaofo', 'Pacific/Fakaofo                    '),
('Pacific/Fiji', 'Pacific/Fiji                       '),
('Pacific/Funafuti', 'Pacific/Funafuti                   '),
('Pacific/Galapagos', 'Pacific/Galapagos                  '),
('Pacific/Gambier', 'Pacific/Gambier                    '),
('Pacific/Guadalcanal', 'Pacific/Guadalcanal                '),
('Pacific/Guam', 'Pacific/Guam                       '),
('Pacific/Honolulu', 'Pacific/Honolulu                   '),
('Pacific/Johnston', 'Pacific/Johnston                   '),
('Pacific/Kiritimati', 'Pacific/Kiritimati                 '),
('Pacific/Kosrae', 'Pacific/Kosrae                     '),
('Pacific/Kwajalein', 'Pacific/Kwajalein                  '),
('Pacific/Majuro', 'Pacific/Majuro                     '),
('Pacific/Marquesas', 'Pacific/Marquesas                  '),
('Pacific/Midway', 'Pacific/Midway                     '),
('Pacific/Nauru', 'Pacific/Nauru                      '),
('Pacific/Niue', 'Pacific/Niue                       '),
('Pacific/Norfolk', 'Pacific/Norfolk                    '),
('Pacific/Noumea', 'Pacific/Noumea                     '),
('Pacific/Pago_Pago', 'Pacific/Pago_Pago                  '),
('Pacific/Palau', 'Pacific/Palau                      '),
('Pacific/Pitcairn', 'Pacific/Pitcairn                   '),
('Pacific/Pohnpei', 'Pacific/Pohnpei                    '),
('Pacific/Ponape', 'Pacific/Ponape                     '),
('Pacific/Port_Moresby', 'Pacific/Port_Moresby               '),
('Pacific/Rarotonga', 'Pacific/Rarotonga                  '),
('Pacific/Saipan', 'Pacific/Saipan                     '),
('Pacific/Samoa', 'Pacific/Samoa                      '),
('Pacific/Tahiti', 'Pacific/Tahiti                     '),
('Pacific/Tarawa', 'Pacific/Tarawa                     '),
('Pacific/Tongatapu', 'Pacific/Tongatapu                  '),
('Pacific/Truk', 'Pacific/Truk                       '),
('Pacific/Wake', 'Pacific/Wake                       '),
('Pacific/Wallis', 'Pacific/Wallis                     '),
('Pacific/Yap', 'Pacific/Yap                        '),
('Poland', 'Poland                             '),
('Portugal', 'Portugal                           '),
('PRC', 'PRC                                '),
('PST8PDT', 'PST8PDT                            '),
('ROC', 'ROC                                '),
('ROK', 'ROK                                '),
('Singapore', 'Singapore                          '),
('Turkey', 'Turkey                             '),
('UCT', 'UCT                                '),
('Universal', 'Universal                          '),
('US/Alaska', 'US/Alaska                          '),
('US/Aleutian', 'US/Aleutian                        '),
('US/Arizona', 'US/Arizona                         '),
('US/Central', 'US/Central                         '),
('US/East-Indiana', 'US/East-Indiana                    '),
('US/Eastern', 'US/Eastern                         '),
('US/Hawaii', 'US/Hawaii                          '),
('US/Indiana-Starke', 'US/Indiana-Starke                  '),
('US/Michigan', 'US/Michigan                        '),
('US/Mountain', 'US/Mountain                        '),
('US/Pacific', 'US/Pacific                         '),
('US/Pacific-New', 'US/Pacific-New                     '),
('US/Samoa', 'US/Samoa                           '),
('UTC', 'UTC                                '),
('W-SU', 'W-SU                               '),
('WET', 'WET                                '),
('Zulu', 'Zulu                               ');

-- --------------------------------------------------------

--
-- Table structure for table `transactionlog`
--

DROP TABLE IF EXISTS `transactionlog`;
CREATE TABLE IF NOT EXISTS `transactionlog` (
  `Id` bigint(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique Transaction ID',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of record creation',
  `TZ` varchar(36) NOT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25987 ;

--
-- Dumping data for table `transactionlog`
--

INSERT INTO `transactionlog` (`Id`, `Timestamp`, `TZ`, `TransType`, `OrgID`, `ClinicID`, `PractitionerID`, `UDID`, `Details`, `IPv4`) VALUES
(24056, '2014-08-30 07:52:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24057, '2014-08-30 07:52:32', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24058, '2014-08-30 07:52:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24059, '2014-08-30 07:53:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24060, '2014-08-30 07:53:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24061, '2014-08-30 07:53:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24062, '2014-08-30 07:54:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24063, '2014-08-30 07:54:14', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0428864756', 'Lateness got by device 0428864756', '59.167.135.214'),
(24064, '2014-08-30 07:54:30', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24065, '2014-08-30 07:54:39', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(24066, '2014-08-30 07:54:39', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(24067, '2014-08-30 07:54:40', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(24068, '2014-08-30 07:54:40', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(24069, '2014-08-30 07:54:40', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(24070, '2014-08-30 07:54:41', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(24071, '2014-08-30 07:54:41', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(24072, '2014-08-30 07:54:41', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(24073, '2014-08-30 07:54:41', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(24074, '2014-08-30 07:54:42', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(24075, '2014-08-30 07:54:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24076, '2014-08-30 07:55:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24077, '2014-08-30 07:55:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24078, '2014-08-30 07:55:50', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24079, '2014-08-30 07:56:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24080, '2014-08-30 07:56:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24081, '2014-08-30 07:56:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24082, '2014-08-30 07:57:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24083, '2014-08-30 07:57:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24084, '2014-08-30 07:57:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24085, '2014-08-30 07:58:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24086, '2014-08-30 07:58:24', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(24087, '2014-08-30 07:58:24', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(24088, '2014-08-30 07:58:24', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(24089, '2014-08-30 07:58:25', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(24090, '2014-08-30 07:58:25', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(24091, '2014-08-30 07:58:25', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(24092, '2014-08-30 07:58:25', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(24093, '2014-08-30 07:58:26', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(24094, '2014-08-30 07:58:26', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(24095, '2014-08-30 07:58:26', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(24096, '2014-08-30 07:58:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24097, '2014-08-30 07:58:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24098, '2014-08-30 07:59:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24099, '2014-08-30 07:59:30', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24100, '2014-08-30 07:59:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24101, '2014-08-30 08:00:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24102, '2014-08-30 08:00:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24103, '2014-08-30 08:00:38', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(24104, '2014-08-30 08:00:38', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(24105, '2014-08-30 08:00:42', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(24106, '2014-08-30 08:00:42', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(24107, '2014-08-30 08:00:42', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(24108, '2014-08-30 08:00:43', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(24109, '2014-08-30 08:00:43', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(24110, '2014-08-30 08:00:43', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(24111, '2014-08-30 08:00:44', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(24112, '2014-08-30 08:00:44', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(24113, '2014-08-30 08:00:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24114, '2014-08-30 08:01:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24115, '2014-08-30 08:01:30', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24116, '2014-08-30 08:01:50', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24117, '2014-08-30 08:02:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24118, '2014-08-30 08:02:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24119, '2014-08-30 08:02:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24120, '2014-08-30 08:03:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24121, '2014-08-30 08:03:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24122, '2014-08-30 08:03:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24123, '2014-08-30 08:04:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24124, '2014-08-30 08:04:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24125, '2014-08-30 08:04:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24126, '2014-08-30 08:05:09', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(24127, '2014-08-30 08:05:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24128, '2014-08-30 08:05:12', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(24129, '2014-08-30 08:05:12', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(24130, '2014-08-30 08:05:12', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(24131, '2014-08-30 08:05:13', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(24132, '2014-08-30 08:05:13', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(24133, '2014-08-30 08:05:13', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(24134, '2014-08-30 08:05:14', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(24135, '2014-08-30 08:05:14', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(24136, '2014-08-30 08:05:14', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(24137, '2014-08-30 08:05:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24138, '2014-08-30 08:05:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24139, '2014-08-30 08:06:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24140, '2014-08-30 08:06:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24141, '2014-08-30 08:06:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24142, '2014-08-30 08:07:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24143, '2014-08-30 08:07:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24144, '2014-08-30 08:07:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24145, '2014-08-30 08:08:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24146, '2014-08-30 08:08:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24147, '2014-08-30 08:08:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24148, '2014-08-30 08:09:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24149, '2014-08-30 08:09:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24150, '2014-08-30 08:09:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24151, '2014-08-30 08:10:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24152, '2014-08-30 08:10:28', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(24153, '2014-08-30 08:10:28', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(24154, '2014-08-30 08:10:28', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(24155, '2014-08-30 08:10:29', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(24156, '2014-08-30 08:10:29', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(24157, '2014-08-30 08:10:29', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(24158, '2014-08-30 08:10:30', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(24159, '2014-08-30 08:10:30', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(24160, '2014-08-30 08:10:30', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(24161, '2014-08-30 08:10:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24162, '2014-08-30 08:10:31', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(24163, '2014-08-30 08:10:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24164, '2014-08-30 08:11:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24165, '2014-08-30 08:11:30', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24166, '2014-08-30 08:11:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24167, '2014-08-30 08:12:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24168, '2014-08-30 08:12:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24169, '2014-08-30 08:12:50', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24170, '2014-08-30 08:13:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24171, '2014-08-30 08:13:17', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(24172, '2014-08-30 08:13:22', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(24173, '2014-08-30 08:13:24', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(24174, '2014-08-30 08:13:25', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(24175, '2014-08-30 08:13:25', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(24176, '2014-08-30 08:13:26', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(24177, '2014-08-30 08:13:26', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(24178, '2014-08-30 08:13:26', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(24179, '2014-08-30 08:13:26', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(24180, '2014-08-30 08:13:27', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(24181, '2014-08-30 08:13:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24182, '2014-08-30 08:13:50', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24183, '2014-08-30 08:14:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24184, '2014-08-30 08:14:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24185, '2014-08-30 08:14:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24186, '2014-08-30 08:15:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24187, '2014-08-30 08:15:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24188, '2014-08-30 08:15:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24189, '2014-08-30 08:16:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24190, '2014-08-30 08:16:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24191, '2014-08-30 08:16:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24192, '2014-08-30 08:17:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24193, '2014-08-30 08:17:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24194, '2014-08-30 08:17:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24195, '2014-08-30 08:18:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24196, '2014-08-30 08:18:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24197, '2014-08-30 08:18:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24198, '2014-08-30 08:19:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24199, '2014-08-30 08:19:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24200, '2014-08-30 08:19:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24201, '2014-08-30 08:20:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24202, '2014-08-30 08:20:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24203, '2014-08-30 08:20:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24204, '2014-08-30 08:21:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24205, '2014-08-30 08:21:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24206, '2014-08-30 08:21:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24207, '2014-08-30 08:22:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24208, '2014-08-30 08:22:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24209, '2014-08-30 08:22:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24210, '2014-08-30 08:23:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24211, '2014-08-30 08:23:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24212, '2014-08-30 08:23:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24213, '2014-08-30 08:24:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24214, '2014-08-30 08:24:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24215, '2014-08-30 08:24:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24216, '2014-08-30 08:25:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24217, '2014-08-30 08:25:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24218, '2014-08-30 08:25:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24219, '2014-08-30 08:26:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24220, '2014-08-30 08:26:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24221, '2014-08-30 08:26:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24222, '2014-08-30 08:27:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24223, '2014-08-30 08:27:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24224, '2014-08-30 08:27:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24225, '2014-08-30 08:28:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24226, '2014-08-30 08:28:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24227, '2014-08-30 08:28:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24228, '2014-08-30 08:29:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24229, '2014-08-30 08:29:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24230, '2014-08-30 08:29:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24231, '2014-08-30 08:30:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24232, '2014-08-30 08:30:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24233, '2014-08-30 08:30:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24234, '2014-08-30 08:31:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24235, '2014-08-30 08:31:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24236, '2014-08-30 08:31:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24237, '2014-08-30 08:32:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24238, '2014-08-30 08:32:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24239, '2014-08-30 08:32:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24240, '2014-08-30 08:33:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24241, '2014-08-30 08:33:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24242, '2014-08-30 08:33:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24243, '2014-08-30 08:34:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24244, '2014-08-30 08:34:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24245, '2014-08-30 08:34:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24246, '2014-08-30 08:35:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24247, '2014-08-30 08:35:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24248, '2014-08-30 08:35:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24249, '2014-08-30 08:36:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24250, '2014-08-30 08:36:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24251, '2014-08-30 08:36:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24252, '2014-08-30 08:37:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24253, '2014-08-30 08:37:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24254, '2014-08-30 08:37:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24255, '2014-08-30 08:38:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24256, '2014-08-30 08:38:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24257, '2014-08-30 08:38:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24258, '2014-08-30 08:39:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24259, '2014-08-30 08:39:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24260, '2014-08-30 08:39:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24261, '2014-08-30 08:40:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24262, '2014-08-30 08:40:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24263, '2014-08-30 08:40:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24264, '2014-08-30 08:41:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24265, '2014-08-30 08:41:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24266, '2014-08-30 08:41:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24267, '2014-08-30 08:42:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24268, '2014-08-30 08:42:33', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24269, '2014-08-30 08:42:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24270, '2014-08-30 08:43:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24271, '2014-08-30 08:43:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24272, '2014-08-30 08:43:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24273, '2014-08-30 08:44:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24274, '2014-08-30 08:44:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24275, '2014-08-30 08:44:52', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24276, '2014-08-30 08:45:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24277, '2014-08-30 08:45:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24278, '2014-08-30 08:45:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24279, '2014-08-30 08:46:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24280, '2014-08-30 08:46:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24281, '2014-08-30 08:46:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24282, '2014-08-30 08:47:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24283, '2014-08-30 08:47:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24284, '2014-08-30 08:47:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24285, '2014-08-30 08:48:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24286, '2014-08-30 08:48:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24287, '2014-08-30 08:48:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24288, '2014-08-30 08:49:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24289, '2014-08-30 08:49:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24290, '2014-08-30 08:49:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24291, '2014-08-30 08:50:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24292, '2014-08-30 08:50:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24293, '2014-08-30 08:50:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24294, '2014-08-30 08:51:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24295, '2014-08-30 08:51:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24296, '2014-08-30 08:51:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24297, '2014-08-30 08:52:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24298, '2014-08-30 08:52:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24299, '2014-08-30 08:52:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24300, '2014-08-30 08:53:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24301, '2014-08-30 08:53:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24302, '2014-08-30 08:53:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24303, '2014-08-30 08:54:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24304, '2014-08-30 08:54:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24305, '2014-08-30 08:54:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24306, '2014-08-30 08:55:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24307, '2014-08-30 08:55:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24308, '2014-08-30 08:55:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24309, '2014-08-30 08:56:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24310, '2014-08-30 08:56:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24311, '2014-08-30 08:56:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24312, '2014-08-30 08:57:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24313, '2014-08-30 08:57:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24314, '2014-08-30 08:57:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24315, '2014-08-30 08:58:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24316, '2014-08-30 08:58:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24317, '2014-08-30 08:58:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24318, '2014-08-30 08:59:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24319, '2014-08-30 08:59:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24320, '2014-08-30 08:59:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24321, '2014-08-30 09:00:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24322, '2014-08-30 09:00:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24323, '2014-08-30 09:00:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24324, '2014-08-30 09:01:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24325, '2014-08-30 09:01:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24326, '2014-08-30 09:01:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24327, '2014-08-30 09:02:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24328, '2014-08-30 09:02:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24329, '2014-08-30 09:02:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24330, '2014-08-30 09:03:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24331, '2014-08-30 09:03:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24332, '2014-08-30 09:03:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24333, '2014-08-30 09:04:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24334, '2014-08-30 09:04:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24335, '2014-08-30 09:04:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24336, '2014-08-30 09:05:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24337, '2014-08-30 09:05:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24338, '2014-08-30 09:05:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24339, '2014-08-30 09:06:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24340, '2014-08-30 09:06:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24341, '2014-08-30 09:06:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24342, '2014-08-30 09:07:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24343, '2014-08-30 09:07:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24344, '2014-08-30 09:07:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24345, '2014-08-30 09:08:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24346, '2014-08-30 09:08:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24347, '2014-08-30 09:08:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24348, '2014-08-30 09:09:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24349, '2014-08-30 09:09:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24350, '2014-08-30 09:09:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24351, '2014-08-30 09:10:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24352, '2014-08-30 09:10:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24353, '2014-08-30 09:10:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24354, '2014-08-30 09:11:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24355, '2014-08-30 09:11:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24356, '2014-08-30 09:11:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24357, '2014-08-30 09:12:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24358, '2014-08-30 09:12:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24359, '2014-08-30 09:12:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24360, '2014-08-30 09:13:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24361, '2014-08-30 09:13:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24362, '2014-08-30 09:13:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24363, '2014-08-30 09:14:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24364, '2014-08-30 09:14:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24365, '2014-08-30 09:14:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24366, '2014-08-30 09:15:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24367, '2014-08-30 09:15:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24368, '2014-08-30 09:15:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24369, '2014-08-30 09:16:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24370, '2014-08-30 09:16:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24371, '2014-08-30 09:16:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24372, '2014-08-30 09:17:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24373, '2014-08-30 09:17:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24374, '2014-08-30 09:17:52', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24375, '2014-08-30 09:18:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24376, '2014-08-30 09:18:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24377, '2014-08-30 09:18:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24378, '2014-08-30 09:19:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24379, '2014-08-30 09:19:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24380, '2014-08-30 09:19:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24381, '2014-08-30 09:20:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24382, '2014-08-30 09:20:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24383, '2014-08-30 09:20:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24384, '2014-08-30 09:21:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24385, '2014-08-30 09:21:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24386, '2014-08-30 09:21:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24387, '2014-08-30 09:22:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24388, '2014-08-30 09:22:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24389, '2014-08-30 09:22:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24390, '2014-08-30 09:23:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24391, '2014-08-30 09:23:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24392, '2014-08-30 09:23:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24393, '2014-08-30 09:24:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24394, '2014-08-30 09:24:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24395, '2014-08-30 09:24:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24396, '2014-08-30 09:25:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24397, '2014-08-30 09:25:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24398, '2014-08-30 09:25:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24399, '2014-08-30 09:26:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24400, '2014-08-30 09:26:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24401, '2014-08-30 09:26:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24402, '2014-08-30 09:27:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24403, '2014-08-30 09:27:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24404, '2014-08-30 09:27:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64');
INSERT INTO `transactionlog` (`Id`, `Timestamp`, `TZ`, `TransType`, `OrgID`, `ClinicID`, `PractitionerID`, `UDID`, `Details`, `IPv4`) VALUES
(24405, '2014-08-30 09:28:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24406, '2014-08-30 09:28:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24407, '2014-08-30 09:28:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24408, '2014-08-30 09:29:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24409, '2014-08-30 09:29:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24410, '2014-08-30 09:29:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24411, '2014-08-30 09:30:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24412, '2014-08-30 09:30:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24413, '2014-08-30 09:30:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24414, '2014-08-30 09:31:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24415, '2014-08-30 09:31:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24416, '2014-08-30 09:31:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24417, '2014-08-30 09:32:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24418, '2014-08-30 09:32:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24419, '2014-08-30 09:32:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24420, '2014-08-30 09:33:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24421, '2014-08-30 09:33:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24422, '2014-08-30 09:33:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24423, '2014-08-30 09:34:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24424, '2014-08-30 09:34:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24425, '2014-08-30 09:34:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24426, '2014-08-30 09:35:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24427, '2014-08-30 09:35:32', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24428, '2014-08-30 09:35:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24429, '2014-08-30 09:36:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24430, '2014-08-30 09:36:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24431, '2014-08-30 09:36:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24432, '2014-08-30 09:37:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24433, '2014-08-30 09:37:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24434, '2014-08-30 09:37:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24435, '2014-08-30 09:38:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24436, '2014-08-30 09:38:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24437, '2014-08-30 09:38:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24438, '2014-08-30 09:39:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24439, '2014-08-30 09:39:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24440, '2014-08-30 09:39:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24441, '2014-08-30 09:40:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24442, '2014-08-30 09:40:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24443, '2014-08-30 09:40:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24444, '2014-08-30 09:41:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24445, '2014-08-30 09:41:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24446, '2014-08-30 09:41:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24447, '2014-08-30 09:42:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24448, '2014-08-30 09:42:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24449, '2014-08-30 09:42:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24450, '2014-08-30 09:43:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24451, '2014-08-30 09:43:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24452, '2014-08-30 09:43:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24453, '2014-08-30 09:44:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24454, '2014-08-30 09:44:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24455, '2014-08-30 09:44:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24456, '2014-08-30 09:45:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24457, '2014-08-30 09:45:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24458, '2014-08-30 09:45:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24459, '2014-08-30 09:46:14', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24460, '2014-08-30 09:46:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24461, '2014-08-30 09:46:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24462, '2014-08-30 09:47:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24463, '2014-08-30 09:47:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24464, '2014-08-30 09:47:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24465, '2014-08-30 09:48:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24466, '2014-08-30 09:48:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24467, '2014-08-30 09:48:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24468, '2014-08-30 09:49:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24469, '2014-08-30 09:49:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24470, '2014-08-30 09:49:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24471, '2014-08-30 09:50:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24472, '2014-08-30 09:50:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24473, '2014-08-30 09:50:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24474, '2014-08-30 09:51:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24475, '2014-08-30 09:51:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24476, '2014-08-30 09:51:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24477, '2014-08-30 09:52:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24478, '2014-08-30 09:52:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24479, '2014-08-30 09:52:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24480, '2014-08-30 09:53:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24481, '2014-08-30 09:53:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24482, '2014-08-30 09:53:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24483, '2014-08-30 09:54:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24484, '2014-08-30 09:54:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24485, '2014-08-30 09:54:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24486, '2014-08-30 09:55:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24487, '2014-08-30 09:55:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24488, '2014-08-30 09:55:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24489, '2014-08-30 09:56:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24490, '2014-08-30 09:56:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24491, '2014-08-30 09:56:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24492, '2014-08-30 09:57:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24493, '2014-08-30 09:57:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24494, '2014-08-30 09:57:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24495, '2014-08-30 09:58:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24496, '2014-08-30 09:58:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24497, '2014-08-30 09:58:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24498, '2014-08-30 09:59:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24499, '2014-08-30 09:59:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24500, '2014-08-30 09:59:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24501, '2014-08-30 10:00:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24502, '2014-08-30 10:00:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24503, '2014-08-30 10:00:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24504, '2014-08-30 10:01:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24505, '2014-08-30 10:01:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24506, '2014-08-30 10:01:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24507, '2014-08-30 10:02:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24508, '2014-08-30 10:02:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24509, '2014-08-30 10:02:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24510, '2014-08-30 10:03:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24511, '2014-08-30 10:03:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24512, '2014-08-30 10:03:52', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24513, '2014-08-30 10:04:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24514, '2014-08-30 10:04:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24515, '2014-08-30 10:04:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24516, '2014-08-30 10:05:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24517, '2014-08-30 10:05:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24518, '2014-08-30 10:05:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24519, '2014-08-30 10:06:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24520, '2014-08-30 10:06:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24521, '2014-08-30 10:06:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24522, '2014-08-30 10:07:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24523, '2014-08-30 10:07:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24524, '2014-08-30 10:07:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24525, '2014-08-30 10:08:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24526, '2014-08-30 10:08:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24527, '2014-08-30 10:08:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24528, '2014-08-30 10:09:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24529, '2014-08-30 10:09:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24530, '2014-08-30 10:09:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24531, '2014-08-30 10:10:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24532, '2014-08-30 10:10:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24533, '2014-08-30 10:10:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24534, '2014-08-30 10:11:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24535, '2014-08-30 10:11:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24536, '2014-08-30 10:11:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24537, '2014-08-30 10:12:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24538, '2014-08-30 10:12:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24539, '2014-08-30 10:12:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24540, '2014-08-30 10:13:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24541, '2014-08-30 10:13:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24542, '2014-08-30 10:13:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24543, '2014-08-30 10:14:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24544, '2014-08-30 10:14:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24545, '2014-08-30 10:14:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24546, '2014-08-30 10:15:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24547, '2014-08-30 10:15:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24548, '2014-08-30 10:15:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24549, '2014-08-30 10:16:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24550, '2014-08-30 10:16:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24551, '2014-08-30 10:16:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24552, '2014-08-30 10:17:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24553, '2014-08-30 10:17:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24554, '2014-08-30 10:17:52', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24555, '2014-08-30 10:18:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24556, '2014-08-30 10:18:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24557, '2014-08-30 10:18:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24558, '2014-08-30 10:19:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24559, '2014-08-30 10:19:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24560, '2014-08-30 10:19:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24561, '2014-08-30 10:20:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24562, '2014-08-30 10:20:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24563, '2014-08-30 10:20:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24564, '2014-08-30 10:21:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24565, '2014-08-30 10:21:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24566, '2014-08-30 10:21:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24567, '2014-08-30 10:22:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24568, '2014-08-30 10:22:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24569, '2014-08-30 10:22:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24570, '2014-08-30 10:23:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24571, '2014-08-30 10:23:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24572, '2014-08-30 10:23:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24573, '2014-08-30 10:24:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24574, '2014-08-30 10:24:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24575, '2014-08-30 10:24:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24576, '2014-08-30 10:25:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24577, '2014-08-30 10:25:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24578, '2014-08-30 10:25:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24579, '2014-08-30 10:26:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24580, '2014-08-30 10:26:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24581, '2014-08-30 10:26:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24582, '2014-08-30 10:27:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24583, '2014-08-30 10:27:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24584, '2014-08-30 10:27:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24585, '2014-08-30 10:28:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24586, '2014-08-30 10:28:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24587, '2014-08-30 10:28:54', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24588, '2014-08-30 10:29:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24589, '2014-08-30 10:29:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24590, '2014-08-30 10:29:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24591, '2014-08-30 10:30:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24592, '2014-08-30 10:30:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24593, '2014-08-30 10:30:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24594, '2014-08-30 10:31:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24595, '2014-08-30 10:31:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24596, '2014-08-30 10:31:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24597, '2014-08-30 10:32:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24598, '2014-08-30 10:32:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24599, '2014-08-30 10:32:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24600, '2014-08-30 10:33:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24601, '2014-08-30 10:33:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24602, '2014-08-30 10:33:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24603, '2014-08-30 10:34:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24604, '2014-08-30 10:34:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24605, '2014-08-30 10:34:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24606, '2014-08-30 10:35:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24607, '2014-08-30 10:35:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24608, '2014-08-30 10:35:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24609, '2014-08-30 10:36:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24610, '2014-08-30 10:36:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24611, '2014-08-30 10:36:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24612, '2014-08-30 10:37:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24613, '2014-08-30 10:37:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24614, '2014-08-30 10:37:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24615, '2014-08-30 10:38:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24616, '2014-08-30 10:38:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24617, '2014-08-30 10:38:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24618, '2014-08-30 10:39:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24619, '2014-08-30 10:39:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24620, '2014-08-30 10:39:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24621, '2014-08-30 10:40:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24622, '2014-08-30 10:40:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24623, '2014-08-30 10:40:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24624, '2014-08-30 10:41:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24625, '2014-08-30 10:41:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24626, '2014-08-30 10:41:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24627, '2014-08-30 10:42:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24628, '2014-08-30 10:42:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24629, '2014-08-30 10:42:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24630, '2014-08-30 10:43:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24631, '2014-08-30 10:43:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24632, '2014-08-30 10:43:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24633, '2014-08-30 10:44:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24634, '2014-08-30 10:44:32', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24635, '2014-08-30 10:44:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24636, '2014-08-30 10:45:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24637, '2014-08-30 10:45:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24638, '2014-08-30 10:45:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24639, '2014-08-30 10:46:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24640, '2014-08-30 10:46:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24641, '2014-08-30 10:46:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24642, '2014-08-30 10:47:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24643, '2014-08-30 10:47:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24644, '2014-08-30 10:47:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24645, '2014-08-30 10:48:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24646, '2014-08-30 10:48:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24647, '2014-08-30 10:48:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24648, '2014-08-30 10:49:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24649, '2014-08-30 10:49:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24650, '2014-08-30 10:49:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24651, '2014-08-30 10:50:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24652, '2014-08-30 10:50:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24653, '2014-08-30 10:50:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24654, '2014-08-30 10:51:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24655, '2014-08-30 10:51:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24656, '2014-08-30 10:51:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24657, '2014-08-30 10:52:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24658, '2014-08-30 10:52:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24659, '2014-08-30 10:52:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24660, '2014-08-30 10:53:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24661, '2014-08-30 10:53:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24662, '2014-08-30 10:53:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24663, '2014-08-30 10:54:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24664, '2014-08-30 10:54:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24665, '2014-08-30 10:54:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24666, '2014-08-30 10:55:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24667, '2014-08-30 10:55:34', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24668, '2014-08-30 10:55:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24669, '2014-08-30 10:56:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24670, '2014-08-30 10:56:34', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24671, '2014-08-30 10:56:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24672, '2014-08-30 10:57:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24673, '2014-08-30 10:57:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24674, '2014-08-30 10:57:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24675, '2014-08-30 10:58:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24676, '2014-08-30 10:58:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24677, '2014-08-30 10:58:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24678, '2014-08-30 10:59:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24679, '2014-08-30 10:59:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24680, '2014-08-30 10:59:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24681, '2014-08-30 11:00:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24682, '2014-08-30 11:00:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24683, '2014-08-30 11:00:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24684, '2014-08-30 11:01:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24685, '2014-08-30 11:01:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24686, '2014-08-30 11:01:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24687, '2014-08-30 11:02:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24688, '2014-08-30 11:02:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24689, '2014-08-30 11:02:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24690, '2014-08-30 11:03:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24691, '2014-08-30 11:03:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24692, '2014-08-30 11:03:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24693, '2014-08-30 11:04:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24694, '2014-08-30 11:04:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24695, '2014-08-30 11:04:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24696, '2014-08-30 11:05:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24697, '2014-08-30 11:05:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24698, '2014-08-30 11:05:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24699, '2014-08-30 11:06:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24700, '2014-08-30 11:06:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24701, '2014-08-30 11:06:52', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24702, '2014-08-30 11:07:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24703, '2014-08-30 11:07:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24704, '2014-08-30 11:07:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24705, '2014-08-30 11:08:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24706, '2014-08-30 11:08:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24707, '2014-08-30 11:08:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24708, '2014-08-30 11:09:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24709, '2014-08-30 11:09:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24710, '2014-08-30 11:09:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24711, '2014-08-30 11:10:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24712, '2014-08-30 11:10:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24713, '2014-08-30 11:10:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24714, '2014-08-30 11:11:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24715, '2014-08-30 11:11:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24716, '2014-08-30 11:11:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24717, '2014-08-30 11:12:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24718, '2014-08-30 11:12:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24719, '2014-08-30 11:12:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24720, '2014-08-30 11:13:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24721, '2014-08-30 11:13:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24722, '2014-08-30 11:13:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24723, '2014-08-30 11:14:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24724, '2014-08-30 11:14:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24725, '2014-08-30 11:14:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24726, '2014-08-30 11:15:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24727, '2014-08-30 11:15:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24728, '2014-08-30 11:15:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24729, '2014-08-30 11:16:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24730, '2014-08-30 11:16:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24731, '2014-08-30 11:16:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24732, '2014-08-30 11:17:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24733, '2014-08-30 11:17:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24734, '2014-08-30 11:17:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24735, '2014-08-30 11:18:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24736, '2014-08-30 11:18:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24737, '2014-08-30 11:18:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24738, '2014-08-30 11:19:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24739, '2014-08-30 11:19:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24740, '2014-08-30 11:19:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24741, '2014-08-30 11:20:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24742, '2014-08-30 11:20:34', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24743, '2014-08-30 11:20:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24744, '2014-08-30 11:21:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24745, '2014-08-30 11:21:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24746, '2014-08-30 11:21:52', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24747, '2014-08-30 11:22:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24748, '2014-08-30 11:22:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24749, '2014-08-30 11:22:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24750, '2014-08-30 11:23:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24751, '2014-08-30 11:23:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24752, '2014-08-30 11:23:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24753, '2014-08-30 11:24:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24754, '2014-08-30 11:24:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24755, '2014-08-30 11:24:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24756, '2014-08-30 11:25:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24757, '2014-08-30 11:25:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24758, '2014-08-30 11:25:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24759, '2014-08-30 11:26:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24760, '2014-08-30 11:26:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24761, '2014-08-30 11:26:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24762, '2014-08-30 11:27:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24763, '2014-08-30 11:27:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24764, '2014-08-30 11:27:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24765, '2014-08-30 11:28:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24766, '2014-08-30 11:28:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24767, '2014-08-30 11:28:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24768, '2014-08-30 11:29:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24769, '2014-08-30 11:29:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24770, '2014-08-30 11:29:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24771, '2014-08-30 11:30:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24772, '2014-08-30 11:30:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24773, '2014-08-30 11:30:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24774, '2014-08-30 11:31:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24775, '2014-08-30 11:31:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24776, '2014-08-30 11:31:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24777, '2014-08-30 11:32:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24778, '2014-08-30 11:32:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24779, '2014-08-30 11:32:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24780, '2014-08-30 11:33:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24781, '2014-08-30 11:33:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64');
INSERT INTO `transactionlog` (`Id`, `Timestamp`, `TZ`, `TransType`, `OrgID`, `ClinicID`, `PractitionerID`, `UDID`, `Details`, `IPv4`) VALUES
(24782, '2014-08-30 11:33:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24783, '2014-08-30 11:34:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24784, '2014-08-30 11:34:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24785, '2014-08-30 11:34:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24786, '2014-08-30 11:35:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24787, '2014-08-30 11:35:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24788, '2014-08-30 11:35:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24789, '2014-08-30 11:36:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24790, '2014-08-30 11:36:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24791, '2014-08-30 11:36:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24792, '2014-08-30 11:37:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24793, '2014-08-30 11:37:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24794, '2014-08-30 11:37:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24795, '2014-08-30 11:38:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24796, '2014-08-30 11:38:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24797, '2014-08-30 11:38:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24798, '2014-08-30 11:39:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24799, '2014-08-30 11:39:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24800, '2014-08-30 11:39:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24801, '2014-08-30 11:40:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24802, '2014-08-30 11:40:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24803, '2014-08-30 11:40:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24804, '2014-08-30 11:41:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24805, '2014-08-30 11:41:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24806, '2014-08-30 11:41:52', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24807, '2014-08-30 11:42:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24808, '2014-08-30 11:42:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24809, '2014-08-30 11:42:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24810, '2014-08-30 11:43:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24811, '2014-08-30 11:43:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24812, '2014-08-30 11:43:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24813, '2014-08-30 11:44:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24814, '2014-08-30 11:44:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24815, '2014-08-30 11:44:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24816, '2014-08-30 11:45:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24817, '2014-08-30 11:45:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24818, '2014-08-30 11:45:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24819, '2014-08-30 11:46:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24820, '2014-08-30 11:46:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24821, '2014-08-30 11:46:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24822, '2014-08-30 11:47:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24823, '2014-08-30 11:47:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24824, '2014-08-30 11:47:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24825, '2014-08-30 11:48:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24826, '2014-08-30 11:48:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24827, '2014-08-30 11:48:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24828, '2014-08-30 11:49:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24829, '2014-08-30 11:49:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24830, '2014-08-30 11:49:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24831, '2014-08-30 11:50:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24832, '2014-08-30 11:50:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24833, '2014-08-30 11:50:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24834, '2014-08-30 11:51:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24835, '2014-08-30 11:51:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24836, '2014-08-30 11:51:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24837, '2014-08-30 11:52:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24838, '2014-08-30 11:52:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24839, '2014-08-30 11:52:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24840, '2014-08-30 11:53:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24841, '2014-08-30 11:53:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24842, '2014-08-30 11:53:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24843, '2014-08-30 11:54:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24844, '2014-08-30 11:54:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24845, '2014-08-30 11:54:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24846, '2014-08-30 11:55:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24847, '2014-08-30 11:55:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24848, '2014-08-30 11:55:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24849, '2014-08-30 11:56:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24850, '2014-08-30 11:56:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24851, '2014-08-30 11:56:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24852, '2014-08-30 11:57:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24853, '2014-08-30 11:57:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24854, '2014-08-30 11:57:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24855, '2014-08-30 11:58:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24856, '2014-08-30 11:58:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24857, '2014-08-30 11:58:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24858, '2014-08-30 11:59:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24859, '2014-08-30 11:59:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24860, '2014-08-30 11:59:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24861, '2014-08-30 12:00:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24862, '2014-08-30 12:00:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24863, '2014-08-30 12:00:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24864, '2014-08-30 12:01:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24865, '2014-08-30 12:01:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24866, '2014-08-30 12:01:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24867, '2014-08-30 12:02:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24868, '2014-08-30 12:02:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24869, '2014-08-30 12:02:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24870, '2014-08-30 12:03:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24871, '2014-08-30 12:03:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24872, '2014-08-30 12:03:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24873, '2014-08-30 12:04:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24874, '2014-08-30 12:04:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24875, '2014-08-30 12:04:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24876, '2014-08-30 12:05:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24877, '2014-08-30 12:05:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24878, '2014-08-30 12:05:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24879, '2014-08-30 12:06:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24880, '2014-08-30 12:06:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24881, '2014-08-30 12:06:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24882, '2014-08-30 12:07:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24883, '2014-08-30 12:07:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24884, '2014-08-30 12:07:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24885, '2014-08-30 12:08:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24886, '2014-08-30 12:08:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24887, '2014-08-30 12:08:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24888, '2014-08-30 12:09:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24889, '2014-08-30 12:09:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24890, '2014-08-30 12:09:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24891, '2014-08-30 12:10:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24892, '2014-08-30 12:10:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24893, '2014-08-30 12:10:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24894, '2014-08-30 12:11:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24895, '2014-08-30 12:11:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24896, '2014-08-30 12:11:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24897, '2014-08-30 12:12:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24898, '2014-08-30 12:12:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24899, '2014-08-30 12:12:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24900, '2014-08-30 12:13:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24901, '2014-08-30 12:13:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24902, '2014-08-30 12:13:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24903, '2014-08-30 12:14:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24904, '2014-08-30 12:14:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24905, '2014-08-30 12:14:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24906, '2014-08-30 12:15:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24907, '2014-08-30 12:15:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24908, '2014-08-30 12:15:52', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24909, '2014-08-30 12:16:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24910, '2014-08-30 12:16:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24911, '2014-08-30 12:16:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24912, '2014-08-30 12:17:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24913, '2014-08-30 12:17:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24914, '2014-08-30 12:17:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24915, '2014-08-30 12:18:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24916, '2014-08-30 12:18:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24917, '2014-08-30 12:18:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24918, '2014-08-30 12:19:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24919, '2014-08-30 12:19:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24920, '2014-08-30 12:19:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24921, '2014-08-30 12:20:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24922, '2014-08-30 12:20:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24923, '2014-08-30 12:20:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24924, '2014-08-30 12:21:12', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24925, '2014-08-30 12:21:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24926, '2014-08-30 12:21:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24927, '2014-08-30 12:22:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24928, '2014-08-30 12:22:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24929, '2014-08-30 12:22:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24930, '2014-08-30 12:23:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24931, '2014-08-30 12:23:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24932, '2014-08-30 12:23:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24933, '2014-08-30 12:24:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24934, '2014-08-30 12:24:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24935, '2014-08-30 12:24:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24936, '2014-08-30 12:25:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24937, '2014-08-30 12:25:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24938, '2014-08-30 12:25:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24939, '2014-08-30 12:26:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24940, '2014-08-30 12:26:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24941, '2014-08-30 12:26:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24942, '2014-08-30 12:27:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24943, '2014-08-30 12:27:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24944, '2014-08-30 12:27:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24945, '2014-08-30 12:28:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24946, '2014-08-30 12:28:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24947, '2014-08-30 12:28:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24948, '2014-08-30 12:29:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24949, '2014-08-30 12:29:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24950, '2014-08-30 12:29:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24951, '2014-08-30 12:30:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24952, '2014-08-30 12:30:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24953, '2014-08-30 12:30:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24954, '2014-08-30 12:31:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24955, '2014-08-30 12:31:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24956, '2014-08-30 12:31:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24957, '2014-08-30 12:32:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24958, '2014-08-30 12:32:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24959, '2014-08-30 12:32:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24960, '2014-08-30 12:33:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24961, '2014-08-30 12:33:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24962, '2014-08-30 12:33:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24963, '2014-08-30 12:34:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24964, '2014-08-30 12:34:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24965, '2014-08-30 12:34:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24966, '2014-08-30 12:35:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24967, '2014-08-30 12:35:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24968, '2014-08-30 12:35:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24969, '2014-08-30 12:36:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24970, '2014-08-30 12:36:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24971, '2014-08-30 12:36:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24972, '2014-08-30 12:37:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24973, '2014-08-30 12:37:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24974, '2014-08-30 12:37:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24975, '2014-08-30 12:38:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24976, '2014-08-30 12:38:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24977, '2014-08-30 12:38:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24978, '2014-08-30 12:39:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24979, '2014-08-30 12:39:32', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24980, '2014-08-30 12:39:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24981, '2014-08-30 12:40:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24982, '2014-08-30 12:40:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24983, '2014-08-30 12:40:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24984, '2014-08-30 12:41:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24985, '2014-08-30 12:41:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24986, '2014-08-30 12:41:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24987, '2014-08-30 12:42:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24988, '2014-08-30 12:42:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24989, '2014-08-30 12:42:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24990, '2014-08-30 12:43:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24991, '2014-08-30 12:43:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24992, '2014-08-30 12:43:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24993, '2014-08-30 12:44:12', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24994, '2014-08-30 12:44:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24995, '2014-08-30 12:44:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24996, '2014-08-30 12:45:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24997, '2014-08-30 12:45:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24998, '2014-08-30 12:45:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(24999, '2014-08-30 12:46:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25000, '2014-08-30 12:46:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25001, '2014-08-30 12:46:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25002, '2014-08-30 12:47:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25003, '2014-08-30 12:47:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25004, '2014-08-30 12:47:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25005, '2014-08-30 12:48:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25006, '2014-08-30 12:48:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25007, '2014-08-30 12:48:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25008, '2014-08-30 12:49:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25009, '2014-08-30 12:49:32', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25010, '2014-08-30 12:49:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25011, '2014-08-30 12:50:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25012, '2014-08-30 12:50:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25013, '2014-08-30 12:50:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25014, '2014-08-30 12:51:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25015, '2014-08-30 12:51:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25016, '2014-08-30 12:51:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25017, '2014-08-30 12:52:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25018, '2014-08-30 12:52:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25019, '2014-08-30 12:52:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25020, '2014-08-30 12:53:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25021, '2014-08-30 12:53:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25022, '2014-08-30 12:53:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25023, '2014-08-30 12:54:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25024, '2014-08-30 12:54:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25025, '2014-08-30 12:54:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25026, '2014-08-30 12:55:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25027, '2014-08-30 12:55:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25028, '2014-08-30 12:55:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25029, '2014-08-30 12:56:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25030, '2014-08-30 12:56:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25031, '2014-08-30 12:56:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25032, '2014-08-30 12:57:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25033, '2014-08-30 12:57:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25034, '2014-08-30 12:57:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25035, '2014-08-30 12:58:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25036, '2014-08-30 12:58:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25037, '2014-08-30 12:58:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25038, '2014-08-30 12:59:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25039, '2014-08-30 12:59:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25040, '2014-08-30 12:59:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25041, '2014-08-30 13:00:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25042, '2014-08-30 13:00:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25043, '2014-08-30 13:00:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25044, '2014-08-30 13:01:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25045, '2014-08-30 13:01:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25046, '2014-08-30 13:01:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25047, '2014-08-30 13:02:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25048, '2014-08-30 13:02:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25049, '2014-08-30 13:02:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25050, '2014-08-30 13:03:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25051, '2014-08-30 13:03:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25052, '2014-08-30 13:03:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25053, '2014-08-30 13:04:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25054, '2014-08-30 13:04:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25055, '2014-08-30 13:04:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25056, '2014-08-30 13:05:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25057, '2014-08-30 13:05:34', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25058, '2014-08-30 13:05:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25059, '2014-08-30 13:06:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25060, '2014-08-30 13:06:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25061, '2014-08-30 13:06:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25062, '2014-08-30 13:07:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25063, '2014-08-30 13:07:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25064, '2014-08-30 13:07:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25065, '2014-08-30 13:08:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25066, '2014-08-30 13:08:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25067, '2014-08-30 13:08:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25068, '2014-08-30 13:09:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25069, '2014-08-30 13:09:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25070, '2014-08-30 13:09:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25071, '2014-08-30 13:10:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25072, '2014-08-30 13:10:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25073, '2014-08-30 13:10:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25074, '2014-08-30 13:11:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25075, '2014-08-30 13:11:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25076, '2014-08-30 13:11:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25077, '2014-08-30 13:12:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25078, '2014-08-30 13:12:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25079, '2014-08-30 13:12:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25080, '2014-08-30 13:13:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25081, '2014-08-30 13:13:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25082, '2014-08-30 13:13:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25083, '2014-08-30 13:14:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25084, '2014-08-30 13:14:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25085, '2014-08-30 13:14:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25086, '2014-08-30 13:15:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25087, '2014-08-30 13:15:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25088, '2014-08-30 13:15:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25089, '2014-08-30 13:16:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25090, '2014-08-30 13:16:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25091, '2014-08-30 13:16:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25092, '2014-08-30 13:17:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25093, '2014-08-30 13:17:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25094, '2014-08-30 13:17:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25095, '2014-08-30 13:18:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25096, '2014-08-30 13:18:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25097, '2014-08-30 13:18:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25098, '2014-08-30 13:19:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25099, '2014-08-30 13:19:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25100, '2014-08-30 13:19:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25101, '2014-08-30 13:20:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25102, '2014-08-30 13:20:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25103, '2014-08-30 13:20:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25104, '2014-08-30 13:21:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25105, '2014-08-30 13:21:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25106, '2014-08-30 13:21:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25107, '2014-08-30 13:22:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25108, '2014-08-30 13:22:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25109, '2014-08-30 13:22:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25110, '2014-08-30 13:23:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25111, '2014-08-30 13:23:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25112, '2014-08-30 13:23:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25113, '2014-08-30 13:24:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25114, '2014-08-30 13:24:32', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25115, '2014-08-30 13:24:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25116, '2014-08-30 13:25:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25117, '2014-08-30 13:25:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25118, '2014-08-30 13:25:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25119, '2014-08-30 13:26:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25120, '2014-08-30 13:26:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25121, '2014-08-30 13:26:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25122, '2014-08-30 13:27:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25123, '2014-08-30 13:27:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25124, '2014-08-30 13:27:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25125, '2014-08-30 13:28:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25126, '2014-08-30 13:28:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25127, '2014-08-30 13:28:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25128, '2014-08-30 13:29:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25129, '2014-08-30 13:29:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25130, '2014-08-30 13:29:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25131, '2014-08-30 13:30:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25132, '2014-08-30 13:30:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25133, '2014-08-30 13:30:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25134, '2014-08-30 13:31:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25135, '2014-08-30 13:31:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25136, '2014-08-30 13:31:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25137, '2014-08-30 13:32:12', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25138, '2014-08-30 13:32:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25139, '2014-08-30 13:32:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25140, '2014-08-30 13:33:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25141, '2014-08-30 13:33:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25142, '2014-08-30 13:33:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25143, '2014-08-30 13:34:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25144, '2014-08-30 13:34:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25145, '2014-08-30 13:34:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25146, '2014-08-30 13:35:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25147, '2014-08-30 13:35:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25148, '2014-08-30 13:35:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25149, '2014-08-30 13:36:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25150, '2014-08-30 13:36:32', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25151, '2014-08-30 13:36:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25152, '2014-08-30 13:37:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25153, '2014-08-30 13:37:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25154, '2014-08-30 13:37:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25155, '2014-08-30 13:38:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25156, '2014-08-30 13:38:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25157, '2014-08-30 13:38:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25158, '2014-08-30 13:39:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64');
INSERT INTO `transactionlog` (`Id`, `Timestamp`, `TZ`, `TransType`, `OrgID`, `ClinicID`, `PractitionerID`, `UDID`, `Details`, `IPv4`) VALUES
(25159, '2014-08-30 13:39:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25160, '2014-08-30 13:39:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25161, '2014-08-30 13:40:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25162, '2014-08-30 13:40:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25163, '2014-08-30 13:40:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25164, '2014-08-30 13:41:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25165, '2014-08-30 13:41:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25166, '2014-08-30 13:41:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25167, '2014-08-30 13:42:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25168, '2014-08-30 13:42:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25169, '2014-08-30 13:42:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25170, '2014-08-30 13:43:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25171, '2014-08-30 13:43:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25172, '2014-08-30 13:43:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25173, '2014-08-30 13:44:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25174, '2014-08-30 13:44:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25175, '2014-08-30 13:44:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25176, '2014-08-30 13:45:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25177, '2014-08-30 13:45:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25178, '2014-08-30 13:45:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25179, '2014-08-30 13:46:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25180, '2014-08-30 13:46:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25181, '2014-08-30 13:46:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25182, '2014-08-30 13:47:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25183, '2014-08-30 13:47:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25184, '2014-08-30 13:47:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25185, '2014-08-30 13:48:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25186, '2014-08-30 13:48:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25187, '2014-08-30 13:48:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25188, '2014-08-30 13:49:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25189, '2014-08-30 13:49:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25190, '2014-08-30 13:49:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25191, '2014-08-30 13:50:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25192, '2014-08-30 13:50:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25193, '2014-08-30 13:50:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25194, '2014-08-30 13:51:12', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25195, '2014-08-30 13:51:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25196, '2014-08-30 13:51:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25197, '2014-08-30 13:52:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25198, '2014-08-30 13:52:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25199, '2014-08-30 13:52:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25200, '2014-08-30 13:53:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25201, '2014-08-30 13:53:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25202, '2014-08-30 13:53:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25203, '2014-08-30 13:54:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25204, '2014-08-30 13:54:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25205, '2014-08-30 13:54:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25206, '2014-08-30 13:55:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25207, '2014-08-30 13:55:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25208, '2014-08-30 13:55:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25209, '2014-08-30 13:56:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25210, '2014-08-30 13:56:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25211, '2014-08-30 13:56:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25212, '2014-08-30 13:57:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25213, '2014-08-30 13:57:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25214, '2014-08-30 13:57:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25215, '2014-08-30 13:58:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25216, '2014-08-30 13:58:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25217, '2014-08-30 13:58:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25218, '2014-08-30 13:59:11', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25219, '2014-08-30 13:59:31', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25220, '2014-08-30 13:59:51', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'C', NULL, 'Lateness updated to 2', '59.167.170.64'),
(25221, '2014-08-31 07:06:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'C', NULL, 'Lateness updated to 20', '203.122.212.28'),
(25222, '2014-08-31 08:34:19', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(25223, '2014-08-31 08:34:20', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(25224, '2014-08-31 08:34:20', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(25225, '2014-08-31 08:34:20', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(25226, '2014-08-31 08:34:21', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(25227, '2014-08-31 08:34:21', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(25228, '2014-08-31 08:34:21', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(25229, '2014-08-31 08:34:22', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(25230, '2014-08-31 08:34:22', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(25231, '2014-08-31 08:34:23', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(25232, '2014-08-31 09:46:22', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(25233, '2014-08-31 09:46:22', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(25234, '2014-08-31 09:46:23', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(25235, '2014-08-31 09:46:23', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(25236, '2014-08-31 09:46:23', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(25237, '2014-08-31 09:46:24', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(25238, '2014-08-31 09:46:24', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(25239, '2014-08-31 09:46:25', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(25240, '2014-08-31 09:46:25', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(25241, '2014-08-31 09:46:25', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(25242, '2014-08-31 09:46:26', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Sunday               from 25200 to 64800', '203.122.212.28'),
(25243, '2014-08-31 10:18:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25244, '2014-08-31 10:19:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25245, '2014-08-31 10:19:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25246, '2014-08-31 10:19:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25247, '2014-08-31 10:20:02', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25248, '2014-08-31 10:20:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25249, '2014-08-31 10:20:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25250, '2014-08-31 10:20:31', 'Australia/Adelaide', 'DEV_REG', 'AAADD', NULL, 'B', '0403569377', 'Device 0403569377registered pin AAADD.B', '203.122.212.28'),
(25251, '2014-08-31 10:20:33', 'Australia/Adelaide', 'DEV_SMS', 'AAADD', NULL, 'B', '0403569377', 'SMS invited 0403569377 using Clickatell gateway', '203.122.212.28'),
(25252, '2014-08-31 10:20:34', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25253, '2014-08-31 10:20:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25254, '2014-08-31 10:21:06', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25255, '2014-08-31 10:21:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25256, '2014-08-31 10:21:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25257, '2014-08-31 10:21:38', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25258, '2014-08-31 10:21:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25259, '2014-08-31 10:22:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25260, '2014-08-31 10:22:10', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25261, '2014-08-31 10:22:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25262, '2014-08-31 10:22:41', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25263, '2014-08-31 10:22:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25264, '2014-08-31 10:23:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25265, '2014-08-31 10:23:13', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25266, '2014-08-31 10:23:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25267, '2014-08-31 10:23:45', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25268, '2014-08-31 10:23:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25269, '2014-08-31 10:24:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25270, '2014-08-31 10:24:17', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25271, '2014-08-31 10:24:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25272, '2014-08-31 10:24:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25273, '2014-08-31 10:24:49', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25274, '2014-08-31 10:25:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25275, '2014-08-31 10:25:21', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25276, '2014-08-31 10:25:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25277, '2014-08-31 10:25:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25278, '2014-08-31 10:25:52', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25279, '2014-08-31 10:26:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25280, '2014-08-31 10:26:24', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25281, '2014-08-31 10:26:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25282, '2014-08-31 10:26:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25283, '2014-08-31 10:26:55', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25284, '2014-08-31 10:27:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25285, '2014-08-31 10:27:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25286, '2014-08-31 10:27:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25287, '2014-08-31 10:28:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25288, '2014-08-31 10:28:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25289, '2014-08-31 10:28:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25290, '2014-08-31 10:29:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25291, '2014-08-31 10:29:27', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25292, '2014-08-31 10:29:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25293, '2014-08-31 10:30:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25294, '2014-08-31 10:30:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25295, '2014-08-31 10:30:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25296, '2014-08-31 10:31:07', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25297, '2014-08-31 10:31:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25298, '2014-08-31 10:31:47', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25299, '2014-08-31 10:32:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25300, '2014-08-31 10:32:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25301, '2014-08-31 10:32:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25302, '2014-08-31 10:33:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25303, '2014-08-31 10:33:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25304, '2014-08-31 10:33:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25305, '2014-08-31 10:34:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25306, '2014-08-31 10:34:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25307, '2014-08-31 10:34:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25308, '2014-08-31 10:35:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25309, '2014-08-31 10:35:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25310, '2014-08-31 10:35:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25311, '2014-08-31 10:36:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25312, '2014-08-31 10:36:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25313, '2014-08-31 10:36:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25314, '2014-08-31 10:37:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25315, '2014-08-31 10:37:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25316, '2014-08-31 10:37:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25317, '2014-08-31 10:38:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25318, '2014-08-31 10:38:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25319, '2014-08-31 10:38:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25320, '2014-08-31 10:39:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25321, '2014-08-31 10:39:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25322, '2014-08-31 10:39:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25323, '2014-08-31 10:40:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25324, '2014-08-31 10:40:28', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25325, '2014-08-31 10:40:48', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25326, '2014-08-31 10:41:08', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25327, '2014-08-31 10:41:36', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(25328, '2014-08-31 10:41:37', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(25329, '2014-08-31 10:41:37', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(25330, '2014-08-31 10:41:37', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(25331, '2014-08-31 10:41:38', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(25332, '2014-08-31 10:41:38', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(25333, '2014-08-31 10:41:39', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(25334, '2014-08-31 10:41:39', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(25335, '2014-08-31 10:41:39', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(25336, '2014-08-31 10:41:40', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(25337, '2014-08-31 10:41:40', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Sunday               from 25200 to 75600', '203.122.212.28'),
(25338, '2014-08-31 10:41:47', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25339, '2014-08-31 10:42:00', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25340, '2014-08-31 10:42:20', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25341, '2014-08-31 10:42:24', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25342, '2014-08-31 10:42:40', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25343, '2014-08-31 10:43:00', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25344, '2014-08-31 10:43:20', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25345, '2014-08-31 10:43:40', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25346, '2014-08-31 10:43:55', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25347, '2014-08-31 10:44:00', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25348, '2014-08-31 10:44:20', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25349, '2014-08-31 10:44:31', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25350, '2014-08-31 10:44:40', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25351, '2014-08-31 10:45:00', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25352, '2014-08-31 10:45:05', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25353, '2014-08-31 10:45:20', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25354, '2014-08-31 10:45:41', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25355, '2014-08-31 10:45:46', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Monday               from 25200 to 82800', '203.122.212.28'),
(25356, '2014-08-31 10:45:46', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Tuesday              from 25200 to 82800', '203.122.212.28'),
(25357, '2014-08-31 10:45:46', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Wednesday            from 25200 to 82800', '203.122.212.28'),
(25358, '2014-08-31 10:45:47', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Thursday             from 25200 to 82800', '203.122.212.28'),
(25359, '2014-08-31 10:45:47', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'A', NULL, 'Practitioner Dr Anthony Albanese works on Friday               from 25200 to 82800', '203.122.212.28'),
(25360, '2014-08-31 10:45:47', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Monday               from 28800 to 82800', '203.122.212.28'),
(25361, '2014-08-31 10:45:48', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Tuesday              from 28800 to 82800', '203.122.212.28'),
(25362, '2014-08-31 10:45:48', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Wednesday            from 28800 to 82800', '203.122.212.28'),
(25363, '2014-08-31 10:45:49', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Thursday             from 28800 to 82800', '203.122.212.28'),
(25364, '2014-08-31 10:45:49', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Friday               from 28800 to 82800', '203.122.212.28'),
(25365, '2014-08-31 10:45:49', 'Australia/Adelaide', 'SESS_UPD', 'AAADD', NULL, 'B', NULL, 'Practitioner Dr Natasha Litjens works on Sunday               from 25200 to 66600', '203.122.212.28'),
(25366, '2014-08-31 10:46:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25367, '2014-08-31 10:46:18', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25368, '2014-08-31 10:46:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25369, '2014-08-31 10:46:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25370, '2014-08-31 10:47:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25371, '2014-08-31 10:47:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25372, '2014-08-31 10:47:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25373, '2014-08-31 10:48:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25374, '2014-08-31 10:48:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25375, '2014-08-31 10:48:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25376, '2014-08-31 10:49:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25377, '2014-08-31 10:49:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25378, '2014-08-31 10:49:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25379, '2014-08-31 10:50:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25380, '2014-08-31 10:50:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25381, '2014-08-31 10:50:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25382, '2014-08-31 10:51:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25383, '2014-08-31 10:51:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25384, '2014-08-31 10:51:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25385, '2014-08-31 10:52:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25386, '2014-08-31 10:52:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25387, '2014-08-31 10:52:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25388, '2014-08-31 10:52:58', 'Australia/Adelaide', 'LATE_UPD', 'AAAHH', NULL, 'A', NULL, 'Lateness updated to 5', '203.122.212.28'),
(25389, '2014-08-31 10:53:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25390, '2014-08-31 10:53:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25391, '2014-08-31 10:53:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25392, '2014-08-31 10:54:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25393, '2014-08-31 10:54:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25394, '2014-08-31 10:54:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25395, '2014-08-31 10:55:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25396, '2014-08-31 10:55:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25397, '2014-08-31 10:56:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25398, '2014-08-31 10:56:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25399, '2014-08-31 10:57:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25400, '2014-08-31 10:57:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25401, '2014-08-31 10:57:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25402, '2014-08-31 10:58:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25403, '2014-08-31 10:58:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25404, '2014-08-31 10:58:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25405, '2014-08-31 10:59:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25406, '2014-08-31 10:59:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25407, '2014-08-31 10:59:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25408, '2014-08-31 11:00:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25409, '2014-08-31 11:00:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25410, '2014-08-31 11:00:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25411, '2014-08-31 11:01:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25412, '2014-08-31 11:01:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25413, '2014-08-31 11:01:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25414, '2014-08-31 11:02:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25415, '2014-08-31 11:02:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25416, '2014-08-31 11:02:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25417, '2014-08-31 11:03:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25418, '2014-08-31 11:03:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25419, '2014-08-31 11:03:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25420, '2014-08-31 11:04:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25421, '2014-08-31 11:04:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25422, '2014-08-31 11:04:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25423, '2014-08-31 11:05:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25424, '2014-08-31 11:05:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25425, '2014-08-31 11:05:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25426, '2014-08-31 11:05:53', 'Australia/Adelaide', 'LATE_GET', NULL, NULL, NULL, '0403569377', 'Lateness got by device 0403569377', '203.122.212.28'),
(25427, '2014-08-31 11:06:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25428, '2014-08-31 11:06:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25429, '2014-08-31 11:06:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25430, '2014-08-31 11:07:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25431, '2014-08-31 11:07:30', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25432, '2014-08-31 11:07:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25433, '2014-08-31 11:08:10', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25434, '2014-08-31 11:08:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25435, '2014-08-31 11:08:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25436, '2014-08-31 11:09:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25437, '2014-08-31 11:09:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25438, '2014-08-31 11:09:50', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25439, '2014-08-31 11:10:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25440, '2014-08-31 11:10:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25441, '2014-08-31 11:10:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25442, '2014-08-31 11:11:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25443, '2014-08-31 11:11:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25444, '2014-08-31 11:11:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25445, '2014-08-31 11:12:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25446, '2014-08-31 11:12:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25447, '2014-08-31 11:12:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25448, '2014-08-31 11:13:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25449, '2014-08-31 11:13:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25450, '2014-08-31 11:13:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25451, '2014-08-31 11:14:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25452, '2014-08-31 11:14:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25453, '2014-08-31 11:14:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25454, '2014-08-31 11:15:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25455, '2014-08-31 11:15:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25456, '2014-08-31 11:15:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25457, '2014-08-31 11:16:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25458, '2014-08-31 11:16:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25459, '2014-08-31 11:16:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25460, '2014-08-31 11:17:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25461, '2014-08-31 11:17:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25462, '2014-08-31 11:17:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25463, '2014-08-31 11:18:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25464, '2014-08-31 11:18:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25465, '2014-08-31 11:18:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25466, '2014-08-31 11:19:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25467, '2014-08-31 11:19:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25468, '2014-08-31 11:19:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25469, '2014-08-31 11:20:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25470, '2014-08-31 11:20:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25471, '2014-08-31 11:20:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25472, '2014-08-31 11:21:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25473, '2014-08-31 11:21:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25474, '2014-08-31 11:21:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25475, '2014-08-31 11:22:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25476, '2014-08-31 11:22:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25477, '2014-08-31 11:22:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25478, '2014-08-31 11:23:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25479, '2014-08-31 11:23:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25480, '2014-08-31 11:23:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25481, '2014-08-31 11:24:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25482, '2014-08-31 11:24:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25483, '2014-08-31 11:24:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25484, '2014-08-31 11:25:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25485, '2014-08-31 11:25:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25486, '2014-08-31 11:25:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25487, '2014-08-31 11:26:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25488, '2014-08-31 11:26:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25489, '2014-08-31 11:26:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25490, '2014-08-31 11:27:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25491, '2014-08-31 11:27:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25492, '2014-08-31 11:27:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25493, '2014-08-31 11:28:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25494, '2014-08-31 11:28:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25495, '2014-08-31 11:28:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25496, '2014-08-31 11:29:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25497, '2014-08-31 11:29:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25498, '2014-08-31 11:29:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25499, '2014-08-31 11:30:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25500, '2014-08-31 11:30:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25501, '2014-08-31 11:30:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25502, '2014-08-31 11:31:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25503, '2014-08-31 11:31:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25504, '2014-08-31 11:31:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25505, '2014-08-31 11:32:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25506, '2014-08-31 11:32:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25507, '2014-08-31 11:32:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25508, '2014-08-31 11:33:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28');
INSERT INTO `transactionlog` (`Id`, `Timestamp`, `TZ`, `TransType`, `OrgID`, `ClinicID`, `PractitionerID`, `UDID`, `Details`, `IPv4`) VALUES
(25509, '2014-08-31 11:33:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25510, '2014-08-31 11:33:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25511, '2014-08-31 11:34:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25512, '2014-08-31 11:34:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25513, '2014-08-31 11:34:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25514, '2014-08-31 11:35:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25515, '2014-08-31 11:35:31', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25516, '2014-08-31 11:35:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25517, '2014-08-31 11:36:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25518, '2014-08-31 11:36:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25519, '2014-08-31 11:36:51', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25520, '2014-08-31 11:37:11', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25521, '2014-08-31 11:37:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25522, '2014-08-31 11:37:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25523, '2014-08-31 11:38:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25524, '2014-08-31 11:38:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25525, '2014-08-31 11:38:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25526, '2014-08-31 11:39:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25527, '2014-08-31 11:39:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25528, '2014-08-31 11:39:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25529, '2014-08-31 11:40:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25530, '2014-08-31 11:40:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25531, '2014-08-31 11:40:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25532, '2014-08-31 11:41:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25533, '2014-08-31 11:41:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25534, '2014-08-31 11:41:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25535, '2014-08-31 11:42:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25536, '2014-08-31 11:42:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25537, '2014-08-31 11:42:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25538, '2014-08-31 11:43:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25539, '2014-08-31 11:43:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25540, '2014-08-31 11:43:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25541, '2014-08-31 11:44:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25542, '2014-08-31 11:44:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25543, '2014-08-31 11:44:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25544, '2014-08-31 11:45:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25545, '2014-08-31 11:45:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25546, '2014-08-31 11:45:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25547, '2014-08-31 11:46:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25548, '2014-08-31 11:46:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25549, '2014-08-31 11:46:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25550, '2014-08-31 11:47:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25551, '2014-08-31 11:47:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25552, '2014-08-31 11:47:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25553, '2014-08-31 11:48:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25554, '2014-08-31 11:48:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25555, '2014-08-31 11:48:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25556, '2014-08-31 11:49:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25557, '2014-08-31 11:49:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25558, '2014-08-31 11:49:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25559, '2014-08-31 11:50:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25560, '2014-08-31 11:50:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25561, '2014-08-31 11:50:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25562, '2014-08-31 11:51:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25563, '2014-08-31 11:51:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25564, '2014-08-31 11:51:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25565, '2014-08-31 11:52:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25566, '2014-08-31 11:52:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25567, '2014-08-31 11:52:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25568, '2014-08-31 11:53:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25569, '2014-08-31 11:53:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25570, '2014-08-31 11:53:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25571, '2014-08-31 11:54:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25572, '2014-08-31 11:54:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25573, '2014-08-31 11:54:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25574, '2014-08-31 11:55:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25575, '2014-08-31 11:55:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25576, '2014-08-31 11:55:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25577, '2014-08-31 11:56:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25578, '2014-08-31 11:56:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25579, '2014-08-31 11:56:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25580, '2014-08-31 11:57:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25581, '2014-08-31 11:57:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25582, '2014-08-31 11:57:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25583, '2014-08-31 11:58:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25584, '2014-08-31 11:58:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25585, '2014-08-31 11:58:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25586, '2014-08-31 11:59:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25587, '2014-08-31 11:59:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25588, '2014-08-31 11:59:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25589, '2014-08-31 12:00:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25590, '2014-08-31 12:00:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25591, '2014-08-31 12:00:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25592, '2014-08-31 12:01:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25593, '2014-08-31 12:01:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25594, '2014-08-31 12:01:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25595, '2014-08-31 12:02:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25596, '2014-08-31 12:02:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25597, '2014-08-31 12:02:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25598, '2014-08-31 12:03:12', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25599, '2014-08-31 12:03:32', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25600, '2014-08-31 12:03:52', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25601, '2014-08-31 12:04:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25602, '2014-08-31 12:04:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25603, '2014-08-31 12:04:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25604, '2014-08-31 12:05:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25605, '2014-08-31 12:05:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25606, '2014-08-31 12:05:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25607, '2014-08-31 12:06:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25608, '2014-08-31 12:06:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25609, '2014-08-31 12:06:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25610, '2014-08-31 12:07:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25611, '2014-08-31 12:07:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25612, '2014-08-31 12:07:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25613, '2014-08-31 12:08:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25614, '2014-08-31 12:08:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25615, '2014-08-31 12:08:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25616, '2014-08-31 12:09:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25617, '2014-08-31 12:09:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25618, '2014-08-31 12:09:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25619, '2014-08-31 12:10:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25620, '2014-08-31 12:10:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25621, '2014-08-31 12:10:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25622, '2014-08-31 12:11:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25623, '2014-08-31 12:11:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25624, '2014-08-31 12:11:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25625, '2014-08-31 12:12:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25626, '2014-08-31 12:12:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25627, '2014-08-31 12:12:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25628, '2014-08-31 12:13:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25629, '2014-08-31 12:13:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25630, '2014-08-31 12:13:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25631, '2014-08-31 12:14:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25632, '2014-08-31 12:14:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25633, '2014-08-31 12:14:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25634, '2014-08-31 12:15:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25635, '2014-08-31 12:15:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25636, '2014-08-31 12:15:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25637, '2014-08-31 12:16:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25638, '2014-08-31 12:16:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25639, '2014-08-31 12:16:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25640, '2014-08-31 12:17:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25641, '2014-08-31 12:17:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25642, '2014-08-31 12:17:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25643, '2014-08-31 12:18:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25644, '2014-08-31 12:18:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25645, '2014-08-31 12:18:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25646, '2014-08-31 12:19:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25647, '2014-08-31 12:19:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25648, '2014-08-31 12:19:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25649, '2014-08-31 12:20:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25650, '2014-08-31 12:20:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25651, '2014-08-31 12:20:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25652, '2014-08-31 12:21:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25653, '2014-08-31 12:21:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25654, '2014-08-31 12:21:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25655, '2014-08-31 12:22:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25656, '2014-08-31 12:22:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25657, '2014-08-31 12:22:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25658, '2014-08-31 12:23:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25659, '2014-08-31 12:23:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25660, '2014-08-31 12:23:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25661, '2014-08-31 12:24:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25662, '2014-08-31 12:24:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25663, '2014-08-31 12:24:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25664, '2014-08-31 12:25:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25665, '2014-08-31 12:25:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25666, '2014-08-31 12:25:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25667, '2014-08-31 12:26:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25668, '2014-08-31 12:26:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25669, '2014-08-31 12:26:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25670, '2014-08-31 12:27:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25671, '2014-08-31 12:27:33', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25672, '2014-08-31 12:27:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25673, '2014-08-31 12:28:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25674, '2014-08-31 12:28:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25675, '2014-08-31 12:28:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25676, '2014-08-31 12:29:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25677, '2014-08-31 12:29:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25678, '2014-08-31 12:29:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25679, '2014-08-31 12:30:13', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25680, '2014-08-31 12:30:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25681, '2014-08-31 12:30:53', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25682, '2014-08-31 12:31:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25683, '2014-08-31 12:31:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25684, '2014-08-31 12:31:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25685, '2014-08-31 12:32:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25686, '2014-08-31 12:32:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25687, '2014-08-31 12:32:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25688, '2014-08-31 12:33:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25689, '2014-08-31 12:33:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25690, '2014-08-31 12:33:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25691, '2014-08-31 12:34:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25692, '2014-08-31 12:34:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25693, '2014-08-31 12:34:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25694, '2014-08-31 12:35:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25695, '2014-08-31 12:35:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25696, '2014-08-31 12:35:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25697, '2014-08-31 12:36:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25698, '2014-08-31 12:36:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25699, '2014-08-31 12:36:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25700, '2014-08-31 12:37:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25701, '2014-08-31 12:37:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25702, '2014-08-31 12:37:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25703, '2014-08-31 12:38:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25704, '2014-08-31 12:38:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25705, '2014-08-31 12:38:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25706, '2014-08-31 12:39:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25707, '2014-08-31 12:39:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25708, '2014-08-31 12:39:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25709, '2014-08-31 12:40:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25710, '2014-08-31 12:40:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25711, '2014-08-31 12:40:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25712, '2014-08-31 12:41:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25713, '2014-08-31 12:41:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25714, '2014-08-31 12:41:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25715, '2014-08-31 12:42:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25716, '2014-08-31 12:42:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25717, '2014-08-31 12:42:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25718, '2014-08-31 12:43:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25719, '2014-08-31 12:43:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25720, '2014-08-31 12:43:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25721, '2014-08-31 12:44:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25722, '2014-08-31 12:44:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25723, '2014-08-31 12:44:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25724, '2014-08-31 12:45:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25725, '2014-08-31 12:45:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25726, '2014-08-31 12:45:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25727, '2014-08-31 12:46:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25728, '2014-08-31 12:46:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25729, '2014-08-31 12:46:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25730, '2014-08-31 12:47:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25731, '2014-08-31 12:47:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25732, '2014-08-31 12:47:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25733, '2014-08-31 12:48:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25734, '2014-08-31 12:48:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25735, '2014-08-31 12:48:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25736, '2014-08-31 12:49:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25737, '2014-08-31 12:49:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25738, '2014-08-31 12:49:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25739, '2014-08-31 12:50:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25740, '2014-08-31 12:50:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25741, '2014-08-31 12:50:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25742, '2014-08-31 12:51:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25743, '2014-08-31 12:51:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25744, '2014-08-31 12:51:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25745, '2014-08-31 12:52:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25746, '2014-08-31 12:52:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25747, '2014-08-31 12:52:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25748, '2014-08-31 12:53:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25749, '2014-08-31 12:53:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25750, '2014-08-31 12:53:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25751, '2014-08-31 12:54:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25752, '2014-08-31 12:54:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25753, '2014-08-31 12:54:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25754, '2014-08-31 12:55:14', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25755, '2014-08-31 12:55:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25756, '2014-08-31 12:55:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25757, '2014-08-31 12:56:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25758, '2014-08-31 12:56:34', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25759, '2014-08-31 12:56:54', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25760, '2014-08-31 12:57:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25761, '2014-08-31 12:57:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25762, '2014-08-31 12:57:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25763, '2014-08-31 12:58:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25764, '2014-08-31 12:58:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25765, '2014-08-31 12:58:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25766, '2014-08-31 12:59:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25767, '2014-08-31 12:59:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25768, '2014-08-31 12:59:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25769, '2014-08-31 13:00:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25770, '2014-08-31 13:00:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25771, '2014-08-31 13:00:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25772, '2014-08-31 13:01:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25773, '2014-08-31 13:01:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25774, '2014-08-31 13:01:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25775, '2014-08-31 13:02:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25776, '2014-08-31 13:02:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25777, '2014-08-31 13:02:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25778, '2014-08-31 13:03:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25779, '2014-08-31 13:03:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25780, '2014-08-31 13:03:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25781, '2014-08-31 13:04:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25782, '2014-08-31 13:04:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25783, '2014-08-31 13:04:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25784, '2014-08-31 13:05:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25785, '2014-08-31 13:05:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25786, '2014-08-31 13:05:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25787, '2014-08-31 13:06:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25788, '2014-08-31 13:06:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25789, '2014-08-31 13:06:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25790, '2014-08-31 13:07:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25791, '2014-08-31 13:07:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25792, '2014-08-31 13:07:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25793, '2014-08-31 13:08:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25794, '2014-08-31 13:08:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25795, '2014-08-31 13:08:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25796, '2014-08-31 13:09:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25797, '2014-08-31 13:09:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25798, '2014-08-31 13:09:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25799, '2014-08-31 13:10:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25800, '2014-08-31 13:10:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25801, '2014-08-31 13:10:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25802, '2014-08-31 13:11:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25803, '2014-08-31 13:11:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25804, '2014-08-31 13:11:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25805, '2014-08-31 13:12:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25806, '2014-08-31 13:12:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25807, '2014-08-31 13:12:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25808, '2014-08-31 13:13:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25809, '2014-08-31 13:13:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25810, '2014-08-31 13:13:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25811, '2014-08-31 13:14:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25812, '2014-08-31 13:14:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25813, '2014-08-31 13:14:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25814, '2014-08-31 13:15:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25815, '2014-08-31 13:15:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25816, '2014-08-31 13:15:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25817, '2014-08-31 13:16:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25818, '2014-08-31 13:16:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25819, '2014-08-31 13:16:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25820, '2014-08-31 13:17:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25821, '2014-08-31 13:17:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25822, '2014-08-31 13:17:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25823, '2014-08-31 13:18:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25824, '2014-08-31 13:18:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25825, '2014-08-31 13:18:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25826, '2014-08-31 13:19:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25827, '2014-08-31 13:19:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25828, '2014-08-31 13:19:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25829, '2014-08-31 13:20:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25830, '2014-08-31 13:20:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25831, '2014-08-31 13:20:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25832, '2014-08-31 13:21:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25833, '2014-08-31 13:21:35', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25834, '2014-08-31 13:21:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25835, '2014-08-31 13:22:15', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25836, '2014-08-31 13:22:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25837, '2014-08-31 13:22:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25838, '2014-08-31 13:23:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25839, '2014-08-31 13:23:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25840, '2014-08-31 13:23:55', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25841, '2014-08-31 13:24:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25842, '2014-08-31 13:24:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25843, '2014-08-31 13:24:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25844, '2014-08-31 13:25:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25845, '2014-08-31 13:25:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25846, '2014-08-31 13:25:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25847, '2014-08-31 13:26:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25848, '2014-08-31 13:26:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25849, '2014-08-31 13:26:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25850, '2014-08-31 13:27:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25851, '2014-08-31 13:27:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25852, '2014-08-31 13:27:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25853, '2014-08-31 13:28:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25854, '2014-08-31 13:28:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25855, '2014-08-31 13:28:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25856, '2014-08-31 13:29:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25857, '2014-08-31 13:29:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25858, '2014-08-31 13:29:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25859, '2014-08-31 13:30:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25860, '2014-08-31 13:30:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25861, '2014-08-31 13:30:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25862, '2014-08-31 13:31:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25863, '2014-08-31 13:31:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25864, '2014-08-31 13:31:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25865, '2014-08-31 13:32:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25866, '2014-08-31 13:32:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25867, '2014-08-31 13:32:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25868, '2014-08-31 13:33:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25869, '2014-08-31 13:33:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25870, '2014-08-31 13:33:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25871, '2014-08-31 13:34:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25872, '2014-08-31 13:34:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25873, '2014-08-31 13:34:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25874, '2014-08-31 13:35:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25875, '2014-08-31 13:35:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25876, '2014-08-31 13:35:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25877, '2014-08-31 13:36:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25878, '2014-08-31 13:36:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25879, '2014-08-31 13:36:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25880, '2014-08-31 13:37:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28');
INSERT INTO `transactionlog` (`Id`, `Timestamp`, `TZ`, `TransType`, `OrgID`, `ClinicID`, `PractitionerID`, `UDID`, `Details`, `IPv4`) VALUES
(25881, '2014-08-31 13:37:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25882, '2014-08-31 13:37:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25883, '2014-08-31 13:38:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25884, '2014-08-31 13:38:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25885, '2014-08-31 13:38:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25886, '2014-08-31 13:39:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25887, '2014-08-31 13:39:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25888, '2014-08-31 13:39:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25889, '2014-08-31 13:40:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25890, '2014-08-31 13:40:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25891, '2014-08-31 13:40:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25892, '2014-08-31 13:41:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25893, '2014-08-31 13:41:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25894, '2014-08-31 13:41:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25895, '2014-08-31 13:42:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25896, '2014-08-31 13:42:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25897, '2014-08-31 13:42:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25898, '2014-08-31 13:43:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25899, '2014-08-31 13:43:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25900, '2014-08-31 13:43:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25901, '2014-08-31 13:44:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25902, '2014-08-31 13:44:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25903, '2014-08-31 13:44:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25904, '2014-08-31 13:45:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25905, '2014-08-31 13:45:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25906, '2014-08-31 13:45:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25907, '2014-08-31 13:46:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25908, '2014-08-31 13:46:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25909, '2014-08-31 13:46:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25910, '2014-08-31 13:47:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25911, '2014-08-31 13:47:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25912, '2014-08-31 13:47:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25913, '2014-08-31 13:48:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25914, '2014-08-31 13:48:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25915, '2014-08-31 13:48:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25916, '2014-08-31 13:49:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25917, '2014-08-31 13:49:36', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25918, '2014-08-31 13:49:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25919, '2014-08-31 13:50:16', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25920, '2014-08-31 13:50:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25921, '2014-08-31 13:50:56', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25922, '2014-08-31 13:51:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25923, '2014-08-31 13:51:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25924, '2014-08-31 13:51:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25925, '2014-08-31 13:52:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25926, '2014-08-31 13:52:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25927, '2014-08-31 13:52:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25928, '2014-08-31 13:53:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25929, '2014-08-31 13:53:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25930, '2014-08-31 13:53:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25931, '2014-08-31 13:54:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25932, '2014-08-31 13:54:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25933, '2014-08-31 13:54:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25934, '2014-08-31 13:55:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25935, '2014-08-31 13:55:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25936, '2014-08-31 13:55:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25937, '2014-08-31 13:56:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25938, '2014-08-31 13:56:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25939, '2014-08-31 13:56:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25940, '2014-08-31 13:57:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25941, '2014-08-31 13:57:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25942, '2014-08-31 13:57:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25943, '2014-08-31 13:58:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25944, '2014-08-31 13:58:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25945, '2014-08-31 13:58:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25946, '2014-08-31 13:59:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25947, '2014-08-31 13:59:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25948, '2014-08-31 13:59:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25949, '2014-08-31 14:00:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25950, '2014-08-31 14:00:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25951, '2014-08-31 14:00:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25952, '2014-08-31 14:01:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25953, '2014-08-31 14:01:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25954, '2014-08-31 14:01:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25955, '2014-08-31 14:02:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25956, '2014-08-31 14:02:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25957, '2014-08-31 14:02:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25958, '2014-08-31 14:03:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25959, '2014-08-31 14:03:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25960, '2014-08-31 14:03:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25961, '2014-08-31 14:04:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25962, '2014-08-31 14:04:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25963, '2014-08-31 14:04:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25964, '2014-08-31 14:05:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25965, '2014-08-31 14:05:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25966, '2014-08-31 14:05:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25967, '2014-08-31 14:06:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25968, '2014-08-31 14:06:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25969, '2014-08-31 14:06:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25970, '2014-08-31 14:07:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25971, '2014-08-31 14:07:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25972, '2014-08-31 14:07:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25973, '2014-08-31 14:08:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25974, '2014-08-31 14:08:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25975, '2014-08-31 14:08:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25976, '2014-08-31 14:09:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25977, '2014-08-31 14:09:38', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25978, '2014-08-31 14:09:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25979, '2014-08-31 14:10:18', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25980, '2014-08-31 14:10:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25981, '2014-08-31 14:10:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25982, '2014-08-31 14:11:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25983, '2014-08-31 14:11:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25984, '2014-08-31 14:11:57', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25985, '2014-08-31 14:12:17', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28'),
(25986, '2014-08-31 14:12:37', 'Australia/Adelaide', 'LATE_UPD', 'AAADD', NULL, 'B', NULL, 'Lateness updated to 18', '203.122.212.28');

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

--
-- Dumping data for table `transtype`
--

INSERT INTO `transtype` (`TransType`, `TransDesc`) VALUES
('CLIN_ADD', 'Clinic has been added to an organisation'),
('CLIN_ARCH', 'Clinic has been archived (soft-deleted).'),
('CLIN_CHG', 'Clinic details have been changed'),
('CLIN_DEL', 'Clinic has been deleted.'),
('DEV_REG', 'Device has registered a PIN'),
('DEV_UNREG', 'Device has unregistered a PIN'),
('LATE_GET', 'Get lateness information.'),
('LATE_RESET', 'Lateness for a practitioner has auto-reset.'),
('LATE_UPD', 'Lateness has been updated for a practitioner'),
('MISC_MISC', 'Miscellaneous transaction type.'),
('ORG_ADD', 'Organisation has been added.'),
('ORG_CHG', 'Organisation has been changed'),
('ORG_DEL', 'Organisation has been deleted'),
('PRAC_ARCH', 'Practitioner has been archived (soft-deleted)'),
('PRAC_CRE', 'Practitioner has been created in an organisation'),
('PRAC_DEL', 'Practitioner has been deleted.'),
('PRAC_DISP', 'Practitioner no longer placed at a clinic.'),
('PRAC_PLACE', 'Practitioner has been placed with a clinic'),
('USER_ADD', 'User has been added to an organisation'),
('USER_ARCH', 'User has been archived (soft-deleted).'),
('USER_CHG', 'User details have been changed'),
('USER_DNE', 'User does not exist.'),
('USER_PWE', 'User login password error.'),
('USER_SUSP', 'User has been suspended.');

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
,`MinutesLateMsg` varchar(65)
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
,`TZ` varchar(36)
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

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwActiveClinics` AS select `clinics`.`ClinicID` AS `ClinicID`,`clinics`.`OrgID` AS `OrgID`,`clinics`.`ClinicName` AS `ClinicName`,`clinics`.`Phone` AS `Phone`,`clinics`.`Address1` AS `Address1`,`clinics`.`Address2` AS `Address2`,`clinics`.`City` AS `City`,`clinics`.`Zip` AS `Zip`,`clinics`.`State` AS `State`,`clinics`.`Country` AS `Country`,`clinics`.`Location` AS `Location`,`clinics`.`Timezone` AS `Timezone` from `clinics` where `clinics`.`ClinicID` in (select `placements`.`ClinicID` AS `ClinicID` from `placements`);

-- --------------------------------------------------------

--
-- Structure for view `vwAssigned`
--
DROP TABLE IF EXISTS `vwAssigned`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwAssigned` AS select `pr`.`SurrogKey` AS `SurrogKey`,ifnull(`c`.`ClinicName`,_latin1'Not assigned') AS `Assigned` from ((`practitioners` `pr` left join `placements` `p` on(((`p`.`OrgID` = `pr`.`OrgID`) and (`p`.`ID` = `pr`.`ID`)))) left join `clinics` `c` on(((`c`.`OrgID` = `p`.`OrgID`) and (`c`.`ClinicID` = `p`.`ClinicID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwErrorTail`
--
DROP TABLE IF EXISTS `vwErrorTail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwErrorTail` AS select `errorlog`.`Id` AS `Id`,`errorlog`.`ErrLevel` AS `ErrLevel`,`errorlog`.`ErrType` AS `ErrType`,`errorlog`.`File` AS `File`,`errorlog`.`Line` AS `Line`,`errorlog`.`IPv4` AS `IPv4`,`errorlog`.`ErrMessage` AS `ErrMessage`,`errorlog`.`Created` AS `Created` from `errorlog` order by `errorlog`.`Id` desc limit 0,20;

-- --------------------------------------------------------

--
-- Structure for view `vwLateness`
--
DROP TABLE IF EXISTS `vwLateness`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwLateness` AS select `v`.`OrgID` AS `OrgID`,`v`.`ID` AS `ID`,`v`.`FullName` AS `FullName`,`v`.`AbbrevName` AS `AbbrevName`,`v`.`DateCreated` AS `DateCreated`,`v`.`OrgName` AS `OrgName`,`v`.`ClinicID` AS `ClinicID`,`v`.`ClinicName` AS `ClinicName`,`v`.`Timezone` AS `Timezone`,ifnull(`lates`.`Minutes`,0) AS `MinutesLate`,`v`.`Subdomain` AS `Subdomain`,`lates`.`Updated` AS `Updated` from (`vwPlacements` `v` left join `lates` on(((`lates`.`OrgID` = `v`.`OrgID`) and (`lates`.`ID` = `v`.`ID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwLateTZ`
--
DROP TABLE IF EXISTS `vwLateTZ`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwLateTZ` AS select `l`.`UKey` AS `UKey`,`l`.`OrgID` AS `OrgID`,`l`.`ID` AS `ID`,`l`.`Updated` AS `Updated`,`l`.`Minutes` AS `Minutes`,`c`.`ClinicName` AS `ClinicName`,ifnull(`c`.`Timezone`,`o`.`Timezone`) AS `Timezone` from (((`lates` `l` left join `placements` `p` on(((`p`.`OrgID` = `l`.`OrgID`) and (`p`.`ID` = `l`.`ID`)))) left join `clinics` `c` on((`c`.`ClinicID` = `p`.`ClinicID`))) left join `orgs` `o` on((`o`.`OrgID` = `c`.`OrgID`)));

-- --------------------------------------------------------

--
-- Structure for view `vwMyLates`
--
DROP TABLE IF EXISTS `vwMyLates`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwMyLates` AS select `v`.`OrgID` AS `OrgID`,`v`.`ID` AS `ID`,`v`.`FullName` AS `FullName`,`v`.`AbbrevName` AS `AbbrevName`,`v`.`DateCreated` AS `DateCreated`,`v`.`OrgName` AS `OrgName`,`v`.`ClinicID` AS `ClinicID`,`v`.`ClinicName` AS `ClinicName`,`v`.`MinutesLate` AS `MinutesLate`,(case `v`.`MinutesLate` when 0 then 'On time' else convert(concat(`getHrsMins2`(`v`.`MinutesLate`,`v`.`OrgID`,`v`.`ID`),' late') using utf8) end) AS `MinutesLateMsg`,`devicereg`.`UDID` AS `UDID`,`v`.`Subdomain` AS `Subdomain` from (`vwLateness` `v` join `devicereg` on(((`v`.`OrgID` = `devicereg`.`OrgID`) and (`v`.`ID` = `devicereg`.`ID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwOrgAdmin`
--
DROP TABLE IF EXISTS `vwOrgAdmin`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwOrgAdmin` AS select `p`.`OrgID` AS `OrgID`,`p`.`ID` AS `ID`,`p`.`FullName` AS `FullName`,`p`.`AbbrevName` AS `AbbrevName`,(case when isnull(`pl`.`ClinicID`) then _utf8'(Not assigned to a clinic)' else `pl`.`ClinicID` end) AS `ClinicPlaced`,(case when isnull(`c`.`ClinicName`) then '(Not assigned to a clinic)' else `c`.`ClinicName` end) AS `ClinicName`,`o`.`Subdomain` AS `Subdomain` from (((`practitioners` `p` join `orgs` `o` on((`p`.`OrgID` = `o`.`OrgID`))) left join `placements` `pl` on(((`pl`.`OrgID` = `p`.`OrgID`) and (`pl`.`ID` = `p`.`ID`)))) left join `clinics` `c` on(((`pl`.`OrgID` = `c`.`OrgID`) and (`pl`.`ClinicID` = `c`.`ClinicID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwOrgUsers`
--
DROP TABLE IF EXISTS `vwOrgUsers`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwOrgUsers` AS select `u`.`DateCreated` AS `DateCreated`,`u`.`EmailAddress` AS `EmailAddress`,`u`.`FullName` AS `FullName`,`u`.`OrgID` AS `OrgID`,`u`.`SecretAnswer1` AS `SecretAnswer1`,`u`.`SecretQuestion1` AS `SecretQuestion1`,`u`.`UserID` AS `UserID`,`u`.`XPassword` AS `XPassword`,`o`.`FQDN` AS `FQDN` from (`orgusers` `u` join `orgs` `o` on((`u`.`OrgID` = `o`.`OrgID`)));

-- --------------------------------------------------------

--
-- Structure for view `vwPlacements`
--
DROP TABLE IF EXISTS `vwPlacements`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwPlacements` AS select `prac`.`OrgID` AS `OrgID`,`prac`.`ID` AS `ID`,`prac`.`FullName` AS `FullName`,`prac`.`AbbrevName` AS `AbbrevName`,`prac`.`DateCreated` AS `DateCreated`,`o`.`OrgName` AS `OrgName`,`p`.`ClinicID` AS `ClinicID`,`c`.`ClinicName` AS `ClinicName`,ifnull(`c`.`Timezone`,`o`.`Timezone`) AS `Timezone`,`o`.`Subdomain` AS `Subdomain` from (((`practitioners` `prac` join `orgs` `o` on((`prac`.`OrgID` = `o`.`OrgID`))) join `placements` `p` on(((`p`.`OrgID` = `prac`.`OrgID`) and (`p`.`ID` = `prac`.`ID`)))) join `clinics` `c` on(((`c`.`OrgID` = `prac`.`OrgID`) and (`c`.`ClinicID` = `p`.`ClinicID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwPractitioners`
--
DROP TABLE IF EXISTS `vwPractitioners`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwPractitioners` AS select `o`.`OrgID` AS `OrgID`,`p`.`ID` AS `PractitionerID`,concat(`o`.`OrgID`,'.',`p`.`ID`) AS `Pin`,`p`.`AbbrevName` AS `PractitionerName`,`p`.`FullName` AS `FullName`,`p`.`NotificationThreshold` AS `NotificationThreshold`,`p`.`LateToNearest` AS `LateToNearest`,`p`.`LatenessOffset` AS `LatenessOffset`,`c`.`ClinicName` AS `ClinicName`,`o`.`OrgShortName` AS `OrgName`,`o`.`FQDN` AS `FQDN`,`o`.`Subdomain` AS `Subdomain` from (((`practitioners` `p` join `placements` `pl` on(((`p`.`ID` = `pl`.`ID`) and (`p`.`OrgID` = `pl`.`OrgID`)))) join `clinics` `c` on(((`pl`.`OrgID` = `c`.`OrgID`) and (`pl`.`ClinicID` = `c`.`ClinicID`)))) join `orgs` `o` on((`c`.`OrgID` = `o`.`OrgID`)));

-- --------------------------------------------------------

--
-- Structure for view `vwSessions`
--
DROP TABLE IF EXISTS `vwSessions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwSessions` AS select `s`.`OrgID` AS `OrgID`,`s`.`ID` AS `ID`,`s`.`Day` AS `Day`,`s`.`StartTime` AS `StartTime`,`s`.`EndTime` AS `EndTime`,ifnull(`c`.`Timezone`,`o`.`Timezone`) AS `Timezone`,`c`.`ClinicID` AS `ClinicID`,`c`.`ClinicName` AS `ClinicName` from (((`sessions` `s` join `orgs` `o` on((`o`.`OrgID` = `s`.`OrgID`))) left join `placements` `p` on(((`p`.`OrgID` = `s`.`OrgID`) and (`p`.`ID` = `s`.`ID`)))) left join `clinics` `c` on((`c`.`ClinicID` = `p`.`ClinicID`)));

-- --------------------------------------------------------

--
-- Structure for view `vwSMS`
--
DROP TABLE IF EXISTS `vwSMS`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwSMS` AS select `transactionlog`.`Id` AS `Id`,`transactionlog`.`Timestamp` AS `Timestamp`,`transactionlog`.`TZ` AS `TZ`,`transactionlog`.`TransType` AS `TransType`,`transactionlog`.`OrgID` AS `OrgID`,`transactionlog`.`ClinicID` AS `ClinicID`,`transactionlog`.`PractitionerID` AS `PractitionerID`,`transactionlog`.`UDID` AS `UDID`,`transactionlog`.`Details` AS `Details`,`transactionlog`.`IPv4` AS `IPv4` from `transactionlog` where (`transactionlog`.`TransType` = 'DEV_SMS');

-- --------------------------------------------------------

--
-- Structure for view `vwTempTest`
--
DROP TABLE IF EXISTS `vwTempTest`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwTempTest` AS select `practitioners`.`OrgID` AS `OrgID`,`practitioners`.`ID` AS `ID`,`practitioners`.`FullName` AS `FullName`,`practitioners`.`LatenessOffset` AS `LatenessOffset`,`practitioners`.`LateToNearest` AS `LateToNearest`,`getHrsMins3`(0,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(0, LateToNearest, LatenessOffset)`,`getHrsMins3`(7,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(7, LateToNearest, LatenessOffset)`,`getHrsMins3`(8,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(8, LateToNearest, LatenessOffset)`,`getHrsMins3`(22,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(22, LateToNearest, LatenessOffset)`,`getHrsMins3`(23,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(23, LateToNearest, LatenessOffset)`,`getHrsMins3`(122,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(122, LateToNearest, LatenessOffset)`,`getHrsMins3`(123,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(123, LateToNearest, LatenessOffset)`,`getHrsMins3`(84,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(84, LateToNearest, LatenessOffset)`,`getHrsMins3`(85,`practitioners`.`LateToNearest`,`practitioners`.`LatenessOffset`) AS `getHrsMins3(85, LateToNearest, LatenessOffset)` from `practitioners`;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
