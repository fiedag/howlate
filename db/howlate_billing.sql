-- phpMyAdmin SQL Dump
-- version 4.1.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 01, 2014 at 10:53 PM
-- Server version: 5.5.37-cll
-- PHP Version: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `howlate_billing`
--
CREATE DATABASE IF NOT EXISTS `howlate_billing` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `howlate_billing`;

DELIMITER $$
--
-- Functions
--
DROP FUNCTION IF EXISTS `getLastSnapshotDate`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getLastSnapshotDate`(`inOrgID` CHAR(5)) RETURNS timestamp
begin
  declare result timestamp;
  
  select IFNULL(max(Created),'2014-01-01') into result
  from snapshot
  where OrgID = inOrgID;
  

  return result;
end$$

DROP FUNCTION IF EXISTS `getNextBillingDate`$$
CREATE DEFINER=`howlate`@`localhost` FUNCTION `getNextBillingDate`(`inOrgID` CHAR(5)) RETURNS timestamp
begin

  declare billingStart timestamp;
  declare lastPeriodEnd timestamp;
  
  select Created into billingStart
  from howlate_main.orgs
  where OrgID = inOrgID;
  
  select IFNULL(max(PeriodEnd),'2014-01-01') into lastPeriodEnd 
  from invchead
  where OrgID = inOrgID;
  
  set lastPeriodEnd = GREATEST(lastPeriodEnd, billingStart);
  
  return lastPeriodEnd + interval 1 month;
  
end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `invchead`
--

DROP TABLE IF EXISTS `invchead`;
CREATE TABLE IF NOT EXISTS `invchead` (
  `InvoiceNum` bigint(20) NOT NULL AUTO_INCREMENT,
  `PeriodStart` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `PeriodEnd` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `OrgID` char(5) NOT NULL,
  PRIMARY KEY (`InvoiceNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pricing`
--

DROP TABLE IF EXISTS `pricing`;
CREATE TABLE IF NOT EXISTS `pricing` (
  `PK` int(11) NOT NULL AUTO_INCREMENT,
  `CountryCode` char(2) CHARACTER SET latin1 NOT NULL,
  `Description` varchar(50) CHARACTER SET latin1 NOT NULL,
  `1 Clinic` varchar(10) COLLATE latin1_general_ci NOT NULL,
  `Up to 4 Clinics` varchar(10) COLLATE latin1_general_ci NOT NULL,
  `5+ Clinics` varchar(10) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`PK`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `pricing`
--

INSERT INTO `pricing` (`PK`, `CountryCode`, `Description`, `1 Clinic`, `Up to 4 Clinics`, `5+ Clinics`) VALUES
(2, 'EN', 'Single Practitioner', 'FREE', 'N/A', 'N/A'),
(3, 'EN', 'Small Clinics (2-4 practitioners)', '$80', '$60', '$48'),
(4, 'EN', 'Large Clinics (5-19 practitioners)', '$120', '$92', '$72'),
(5, 'EN', 'Superclinics (20+ practitioners)', '$160', '$120', '$96');

-- --------------------------------------------------------

--
-- Table structure for table `snapshot`
--

DROP TABLE IF EXISTS `snapshot`;
CREATE TABLE IF NOT EXISTS `snapshot` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `OrgID` char(5) NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `FreeClinics` int(11) NOT NULL,
  `SmallClinics` int(11) NOT NULL,
  `LargeClinics` int(11) NOT NULL,
  `SuperClinics` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vwClinicPlacements`
--
DROP VIEW IF EXISTS `vwClinicPlacements`;
CREATE TABLE IF NOT EXISTS `vwClinicPlacements` (
`OrgID` char(5)
,`ID` char(2)
,`ClinicID` int(6)
,`ClinicName` varchar(40)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwClinicSizes`
--
DROP VIEW IF EXISTS `vwClinicSizes`;
CREATE TABLE IF NOT EXISTS `vwClinicSizes` (
`OrgID` char(5)
,`ClinicName` varchar(40)
,`PractitionerCount` bigint(21)
,`ClinicSize` varchar(10)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwOrgBillDue`
--
DROP VIEW IF EXISTS `vwOrgBillDue`;
CREATE TABLE IF NOT EXISTS `vwOrgBillDue` (
`OrgID` char(5)
,`NextBillingDay` timestamp
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwOrgNextBillingDay`
--
DROP VIEW IF EXISTS `vwOrgNextBillingDay`;
CREATE TABLE IF NOT EXISTS `vwOrgNextBillingDay` (
`OrgID` char(5)
,`NextBillingDay` timestamp
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `vwTodaysSMS`
--
DROP VIEW IF EXISTS `vwTodaysSMS`;
CREATE TABLE IF NOT EXISTS `vwTodaysSMS` (
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
-- Structure for view `vwClinicPlacements`
--
DROP TABLE IF EXISTS `vwClinicPlacements`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwClinicPlacements` AS select `p`.`OrgID` AS `OrgID`,`p`.`ID` AS `ID`,`p`.`ClinicID` AS `ClinicID`,`p`.`Assigned` AS `ClinicName` from `howlate_main`.`vwAssigned` `p`;

-- --------------------------------------------------------

--
-- Structure for view `vwClinicSizes`
--
DROP TABLE IF EXISTS `vwClinicSizes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwClinicSizes` AS select `vwClinicPlacements`.`OrgID` AS `OrgID`,`vwClinicPlacements`.`ClinicName` AS `ClinicName`,count(0) AS `PractitionerCount`,(case when ((count(0) = 1) and (`vwClinicPlacements`.`ClinicID` is not null)) then 'Free' when ((count(0) >= 2) and (count(0) <= 4) and (`vwClinicPlacements`.`ClinicID` is not null)) then 'Small' when ((count(0) >= 5) and (count(0) <= 19) and (`vwClinicPlacements`.`ClinicID` is not null)) then 'Large' when ((count(0) >= 20) and (`vwClinicPlacements`.`ClinicID` is not null)) then 'Super' else 'Unassigned' end) AS `ClinicSize` from `vwClinicPlacements` group by `vwClinicPlacements`.`OrgID`,`vwClinicPlacements`.`ClinicID`;

-- --------------------------------------------------------

--
-- Structure for view `vwOrgBillDue`
--
DROP TABLE IF EXISTS `vwOrgBillDue`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwOrgBillDue` AS select `vwOrgNextBillingDay`.`OrgID` AS `OrgID`,`vwOrgNextBillingDay`.`NextBillingDay` AS `NextBillingDay` from `vwOrgNextBillingDay` where (`vwOrgNextBillingDay`.`NextBillingDay` <= now());

-- --------------------------------------------------------

--
-- Structure for view `vwOrgNextBillingDay`
--
DROP TABLE IF EXISTS `vwOrgNextBillingDay`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwOrgNextBillingDay` AS select `howlate_main`.`orgs`.`OrgID` AS `OrgID`,`getNextBillingDate`(`howlate_main`.`orgs`.`OrgID`) AS `NextBillingDay` from `howlate_main`.`orgs`;

-- --------------------------------------------------------

--
-- Structure for view `vwTodaysSMS`
--
DROP TABLE IF EXISTS `vwTodaysSMS`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwTodaysSMS` AS select `howlate_main`.`transactionlog`.`Id` AS `Id`,`howlate_main`.`transactionlog`.`Timestamp` AS `Timestamp`,`howlate_main`.`transactionlog`.`TZ` AS `TZ`,`howlate_main`.`transactionlog`.`TransType` AS `TransType`,`howlate_main`.`transactionlog`.`OrgID` AS `OrgID`,`howlate_main`.`transactionlog`.`ClinicID` AS `ClinicID`,`howlate_main`.`transactionlog`.`PractitionerID` AS `PractitionerID`,`howlate_main`.`transactionlog`.`UDID` AS `UDID`,`howlate_main`.`transactionlog`.`Details` AS `Details`,`howlate_main`.`transactionlog`.`IPv4` AS `IPv4` from `howlate_main`.`transactionlog` where ((`howlate_main`.`transactionlog`.`TransType` = 'DEV_SMS') and (`howlate_main`.`transactionlog`.`Timestamp` >= curdate()) and (`howlate_main`.`transactionlog`.`Timestamp` < (curdate() + interval 1 day)));

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
