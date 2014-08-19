
-- phpMyAdmin SQL Dump
-- version 4.1.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 20, 2014 at 02:39 AM
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

$$

--
-- Functions
--
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Table structure for table `devicereg`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Stores which devices have registered interest in which docto' AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Table structure for table `errorlog`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=531 ;

-- --------------------------------------------------------

--
-- Table structure for table `lates`
--

CREATE TABLE IF NOT EXISTS `lates` (
  `OrgID` char(5) NOT NULL COMMENT 'The Organisation ID',
  `ID` char(2) NOT NULL COMMENT 'The practitioner ID',
  `Updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When updated',
  `Minutes` smallint(3) NOT NULL COMMENT 'Minutes late.  Negative is early.',
  PRIMARY KEY (`OrgID`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Lateness records';

-- --------------------------------------------------------

--
-- Table structure for table `orgs`
--

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
  `UpdIndic` int(11) NOT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `placements`
--

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
  PRIMARY KEY (`SurrogKey`),
  UNIQUE KEY `OrgID` (`OrgID`,`ID`),
  KEY `IntegrKey` (`OrgID`,`IntegrKey`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=183 ;

-- --------------------------------------------------------

--
-- Table structure for table `resetrequests`
--

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
-- Table structure for table `timezones`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3941 ;

-- --------------------------------------------------------

--
-- Table structure for table `transtype`
--

CREATE TABLE IF NOT EXISTS `transtype` (
  `TransType` char(10) NOT NULL COMMENT 'Transaction types',
  `TransDesc` varchar(50) NOT NULL COMMENT 'Description',
  PRIMARY KEY (`TransType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `usercodes`
--

CREATE TABLE IF NOT EXISTS `usercodes` (
  `UserCode` varchar(12) NOT NULL COMMENT 'Type of user code',
  `CodeVal` varchar(50) NOT NULL,
  `CodeDesc` varchar(50) NOT NULL,
  UNIQUE KEY `UserCode` (`UserCode`,`CodeVal`),
  UNIQUE KEY `UserCode_2` (`UserCode`,`CodeVal`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vwActiveClinics`
--
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
CREATE TABLE IF NOT EXISTS `vwAssigned` (
`SurrogKey` bigint(20)
,`Assigned` varchar(40)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwErrorTail`
--
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
CREATE TABLE IF NOT EXISTS `vwLateness` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`AbbrevName` varchar(20)
,`DateCreated` timestamp
,`OrgName` varchar(50)
,`ClinicID` int(11)
,`ClinicName` varchar(40)
,`MinutesLate` int(6)
,`Subdomain` varchar(24)
,`Updated` timestamp
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwMyLates`
--
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
CREATE TABLE IF NOT EXISTS `vwPlacements` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`AbbrevName` varchar(20)
,`DateCreated` timestamp
,`OrgName` varchar(50)
,`ClinicID` int(11)
,`ClinicName` varchar(40)
,`Subdomain` varchar(24)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwPractitioners`
--
CREATE TABLE IF NOT EXISTS `vwPractitioners` (
`OrgID` char(5)
,`PractitionerID` char(2)
,`Pin` varchar(8)
,`PractitionerName` varchar(20)
,`FullName` varchar(80)
,`ClinicName` varchar(40)
,`OrgName` varchar(24)
,`FQDN` varchar(40)
,`Subdomain` varchar(24)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwTimezones`
--
CREATE TABLE IF NOT EXISTS `vwTimezones` (
`CodeVal` varchar(50)
,`CodeDesc` varchar(50)
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

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwLateness` AS select `v`.`OrgID` AS `OrgID`,`v`.`ID` AS `ID`,`v`.`FullName` AS `FullName`,`v`.`AbbrevName` AS `AbbrevName`,`v`.`DateCreated` AS `DateCreated`,`v`.`OrgName` AS `OrgName`,`v`.`ClinicID` AS `ClinicID`,`v`.`ClinicName` AS `ClinicName`,ifnull(`lates`.`Minutes`,0) AS `MinutesLate`,`v`.`Subdomain` AS `Subdomain`,`lates`.`Updated` AS `Updated` from (`vwPlacements` `v` left join `lates` on(((`lates`.`OrgID` = `v`.`OrgID`) and (`lates`.`ID` = `v`.`ID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwMyLates`
--
DROP TABLE IF EXISTS `vwMyLates`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwMyLates` AS select `v`.`OrgID` AS `OrgID`,`v`.`ID` AS `ID`,`v`.`FullName` AS `FullName`,`v`.`AbbrevName` AS `AbbrevName`,`v`.`DateCreated` AS `DateCreated`,`v`.`OrgName` AS `OrgName`,`v`.`ClinicID` AS `ClinicID`,`v`.`ClinicName` AS `ClinicName`,`v`.`MinutesLate` AS `MinutesLate`,(case `v`.`MinutesLate` when 0 then _utf8'On time' else convert(concat(`getHrsMins`(`v`.`MinutesLate`),_latin1' late') using utf8) end) AS `MinutesLateMsg`,`devicereg`.`UDID` AS `UDID`,`v`.`Subdomain` AS `Subdomain` from (`vwLateness` `v` join `devicereg` on(((`v`.`OrgID` = `devicereg`.`OrgID`) and (`v`.`ID` = `devicereg`.`ID`))));

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

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwPlacements` AS select `prac`.`OrgID` AS `OrgID`,`prac`.`ID` AS `ID`,`prac`.`FullName` AS `FullName`,`prac`.`AbbrevName` AS `AbbrevName`,`prac`.`DateCreated` AS `DateCreated`,`o`.`OrgName` AS `OrgName`,`p`.`ClinicID` AS `ClinicID`,`c`.`ClinicName` AS `ClinicName`,`o`.`Subdomain` AS `Subdomain` from (((`practitioners` `prac` join `orgs` `o` on((`prac`.`OrgID` = `o`.`OrgID`))) join `placements` `p` on(((`p`.`OrgID` = `prac`.`OrgID`) and (`p`.`ID` = `prac`.`ID`)))) join `clinics` `c` on(((`c`.`OrgID` = `prac`.`OrgID`) and (`c`.`ClinicID` = `p`.`ClinicID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwPractitioners`
--
DROP TABLE IF EXISTS `vwPractitioners`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwPractitioners` AS select `o`.`OrgID` AS `OrgID`,`p`.`ID` AS `PractitionerID`,concat(`o`.`OrgID`,'.',`p`.`ID`) AS `Pin`,`p`.`AbbrevName` AS `PractitionerName`,`p`.`FullName` AS `FullName`,`c`.`ClinicName` AS `ClinicName`,`o`.`OrgShortName` AS `OrgName`,`o`.`FQDN` AS `FQDN`,`o`.`Subdomain` AS `Subdomain` from (((`practitioners` `p` join `placements` `pl` on(((`p`.`ID` = `pl`.`ID`) and (`p`.`OrgID` = `pl`.`OrgID`)))) join `clinics` `c` on(((`pl`.`OrgID` = `c`.`OrgID`) and (`pl`.`ClinicID` = `c`.`ClinicID`)))) join `orgs` `o` on((`c`.`OrgID` = `o`.`OrgID`)));

-- --------------------------------------------------------

--
-- Structure for view `vwTimezones`
--
DROP TABLE IF EXISTS `vwTimezones`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwTimezones` AS select `usercodes`.`CodeVal` AS `CodeVal`,`usercodes`.`CodeDesc` AS `CodeDesc` from `usercodes` where (`usercodes`.`UserCode` = 'TZ');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
