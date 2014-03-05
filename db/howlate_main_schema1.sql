-- phpMyAdmin SQL Dump
-- version 4.0.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 04, 2014 at 09:39 PM
-- Server version: 5.0.96-community
-- PHP Version: 5.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `howlate_main`
--
CREATE DATABASE IF NOT EXISTS `howlate_main` DEFAULT CHARACTER SET latin1 COLLATE latin1_general_ci;
USE `howlate_main`;

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`howlate`@`localhost` PROCEDURE `getNextOrgID`(OUT `NextOrgID` CHAR(5))
begin
  declare LastOrgID char(5);

  select LastOrgID = max(OrgID) 
  from orgs;

  set NextOrgID = 'AAAEE';

end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `clinics`
--

CREATE TABLE IF NOT EXISTS `clinics` (
  `OrgID` char(5) collate latin1_general_ci NOT NULL,
  `ClinicID` int(11) NOT NULL,
  `ClinicName` varchar(40) collate latin1_general_ci NOT NULL COMMENT 'Must fit on iPhone screen hence short',
  `Location` geometry default NULL,
  UNIQUE KEY `ClinicID` (`ClinicID`,`OrgID`),
  UNIQUE KEY `ClinicID_2` (`ClinicID`,`OrgID`),
  KEY `OrgID` (`OrgID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `clinics`
--

INSERT INTO `clinics` (`OrgID`, `ClinicID`, `ClinicName`, `Location`) VALUES
('AAABB', 1, 'Health Alliance Campbelltown', NULL),
('AAACC', 1, 'Medical HQ Beulah Park', NULL),
('AAABB', 2, 'Health Alliance Newton', NULL),
('AAACC', 2, 'Medical HQ Athelstone', NULL),
('AAACC', 3, 'Medical HQ Maitland', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `devicereg`
--

CREATE TABLE IF NOT EXISTS `devicereg` (
  `UDID` varchar(40) collate latin1_general_ci NOT NULL COMMENT 'Unique Device ID of the smartphone or tablet.',
  `OrgID` char(5) collate latin1_general_ci NOT NULL COMMENT 'The Organisation ID',
  `ID` char(2) collate latin1_general_ci NOT NULL COMMENT 'The Practitioner ID',
  `Created` date NOT NULL,
  PRIMARY KEY  (`UDID`,`OrgID`,`ID`),
  UNIQUE KEY `UDID` (`UDID`,`OrgID`,`ID`),
  KEY `OrgID` (`OrgID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Stores which devices have registered interest in which docto';

--
-- Dumping data for table `devicereg`
--

INSERT INTO `devicereg` (`UDID`, `OrgID`, `ID`, `Created`) VALUES
('1234567890123456789012345678901234567894', 'AAABB', 'C', '0000-00-00'),
('1234567890123456789012345678901234567894', 'AAABB', 'F', '0000-00-00'),
('1234567890123456789012345678901234567894', 'AAACC', 'A', '0000-00-00'),
('1234567890123456789012345678901234567894', 'AAACC', 'F', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `lates`
--

CREATE TABLE IF NOT EXISTS `lates` (
  `OrgID` char(5) collate latin1_general_ci NOT NULL COMMENT 'The Organisation ID',
  `ID` char(2) collate latin1_general_ci NOT NULL COMMENT 'The practitioner ID',
  `Updated` datetime NOT NULL COMMENT 'When updated',
  `Minutes` smallint(3) NOT NULL COMMENT 'Minutes late.  Negative is early.',
  PRIMARY KEY  (`OrgID`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Lateness records';

--
-- Dumping data for table `lates`
--

INSERT INTO `lates` (`OrgID`, `ID`, `Updated`, `Minutes`) VALUES
('AAABB', 'A', '2014-03-03 00:00:00', 50),
('AAACC', 'A', '2014-03-04 09:00:00', 15);

-- --------------------------------------------------------

--
-- Table structure for table `orgs`
--

CREATE TABLE IF NOT EXISTS `orgs` (
  `OrgID` char(5) collate latin1_general_ci NOT NULL COMMENT '4 character alpha ID',
  `OrgName` varchar(50) collate latin1_general_ci NOT NULL COMMENT 'The registered long name.',
  `OrgShortName` varchar(24) collate latin1_general_ci NOT NULL COMMENT 'Must fit on table header on iPhone without scrolling',
  `TaxID` varchar(24) collate latin1_general_ci NOT NULL COMMENT 'The federal registration ID in the relevant country, e.g. ABN, FID.',
  `Subdomain` varchar(24) collate latin1_general_ci NOT NULL COMMENT 'word prepended to how-late.com e.g. bricc, must contain only valid characters.',
  `FQDN` varchar(40) collate latin1_general_ci NOT NULL COMMENT 'Fully qualified domain name',
  `BillingRef` varchar(18) collate latin1_general_ci NOT NULL COMMENT 'Reference to billing system.',
  PRIMARY KEY  (`OrgID`),
  UNIQUE KEY `OrgID` (`OrgID`),
  UNIQUE KEY `OrgID_2` (`OrgID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `orgs`
--

INSERT INTO `orgs` (`OrgID`, `OrgName`, `OrgShortName`, `TaxID`, `Subdomain`, `FQDN`, `BillingRef`) VALUES
('AAABB', 'Health Alliance SA', 'Health Alliance', '', 'ha.sa.', 'ha.sa.how-late.com', '4'),
('AAACC', 'Medical HQ SA', 'Medical HQ', '', 'mhq.sa', 'mhq.sa.how-late.com', '5');

-- --------------------------------------------------------

--
-- Table structure for table `orgusers`
--

CREATE TABLE IF NOT EXISTS `orgusers` (
  `OrgID` char(5) collate latin1_general_ci NOT NULL COMMENT 'Link to Orgs Table',
  `UserID` varchar(18) collate latin1_general_ci NOT NULL COMMENT '18 character userid.',
  `EmailAddress` varchar(50) collate latin1_general_ci NOT NULL COMMENT 'Used for signin.',
  `XPassword` varchar(100) collate latin1_general_ci NOT NULL COMMENT 'Encrypted password.',
  `SecretQuestion1` varchar(50) collate latin1_general_ci NOT NULL COMMENT 'The questions may change.  Store them here',
  `SecretAnswer1` varchar(50) collate latin1_general_ci NOT NULL COMMENT 'The answer recorded.',
  `FullName` varchar(50) collate latin1_general_ci NOT NULL,
  `DateCreated` date NOT NULL,
  UNIQUE KEY `OrgID` (`OrgID`,`EmailAddress`),
  UNIQUE KEY `OrgID_2` (`OrgID`,`UserID`),
  UNIQUE KEY `OrgID_3` (`OrgID`,`EmailAddress`),
  KEY `UserID` (`UserID`),
  KEY `EmailAddress` (`EmailAddress`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `orgusers`
--

INSERT INTO `orgusers` (`OrgID`, `UserID`, `EmailAddress`, `XPassword`, `SecretQuestion1`, `SecretAnswer1`, `FullName`, `DateCreated`) VALUES
('AAABB', '', 'alex.fiedler@internode.on.net', '364fc2dc1dc72d787419541fdc96e41f996775b2', 'Mothers maiden name', 'mahrla', 'Alex Fiedler', '2014-02-25');

-- --------------------------------------------------------

--
-- Table structure for table `placements`
--

CREATE TABLE IF NOT EXISTS `placements` (
  `OrgID` char(5) collate latin1_general_ci NOT NULL COMMENT 'The Organisation ID',
  `ID` char(2) collate latin1_general_ci NOT NULL,
  `ClinicID` int(11) NOT NULL COMMENT 'The Clinic ID',
  PRIMARY KEY  (`OrgID`,`ID`),
  UNIQUE KEY `OrgID` (`OrgID`,`ID`),
  UNIQUE KEY `OrgID_2` (`OrgID`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='This places a practitioner at a clinic';

--
-- Dumping data for table `placements`
--

INSERT INTO `placements` (`OrgID`, `ID`, `ClinicID`) VALUES
('AAABB', 'A', 1),
('AAABB', 'B', 2),
('AAACC', 'A', 1),
('AAACC', 'B', 1),
('AAACC', 'C', 2);

-- --------------------------------------------------------

--
-- Table structure for table `practitioners`
--

CREATE TABLE IF NOT EXISTS `practitioners` (
  `OrgID` char(5) collate latin1_general_ci NOT NULL,
  `ID` char(2) collate latin1_general_ci NOT NULL COMMENT 'Practitioner ID',
  `FullName` varchar(80) collate latin1_general_ci NOT NULL COMMENT 'E.g. Dr A.J.K. Venkatanarasimharajuvaripeta',
  `AbbrevName` varchar(20) collate latin1_general_ci NOT NULL COMMENT 'For iPhone fit.',
  `DateCreated` date NOT NULL,
  PRIMARY KEY  (`OrgID`,`ID`),
  UNIQUE KEY `OrgID` (`OrgID`,`ID`),
  UNIQUE KEY `OrgID_2` (`OrgID`,`ID`),
  UNIQUE KEY `OrgID_3` (`OrgID`,`ID`),
  KEY `OrgID_4` (`OrgID`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `practitioners`
--

INSERT INTO `practitioners` (`OrgID`, `ID`, `FullName`, `AbbrevName`, `DateCreated`) VALUES
('AAABB', 'A', 'Dr Anna Schettini', 'Dr Anna Schettini', '2014-02-28'),
('AAABB', 'B', 'Dr Alvin Chua', 'Dr Alvin Chua', '2014-02-28'),
('AAABB', 'C', 'Dr Amir Robin - Karas', 'Dr A Robin-Karas', '2014-02-28'),
('AAABB', 'D', 'Dr. Karina Jaeschke', 'Dr. Karina Jaeschke', '2014-02-28'),
('AAABB', 'E', 'Dr. Tram Le', 'Dr. Tram Le', '2014-02-28'),
('AAABB', 'F', 'Dr Alison Edgecomb', 'Dr A Edgecomb', '2014-02-28'),
('AAACC', 'A', 'Dr Rodney Pearce MBBS FAMA Order of Australia (AM)', 'Dr R Pearce', '2014-02-28'),
('AAACC', 'B', 'Dr Chris Lloyd MA (Cam) MBChB (Hons) DRCOG FRACGP', 'Dr C Lloyd', '2014-02-28'),
('AAACC', 'C', 'Dr Don Cameron BSc (Hons) BMBS PhD FRACGP', 'Dr D Cameron', '2014-02-28'),
('AAACC', 'D', 'Dr Anne Awwad MBBS', 'Dr Anne Awwad', '2014-02-28'),
('AAACC', 'E', 'Dr Huguette Rignanese', 'Dr H Rignanese', '2014-02-28'),
('AAACC', 'F', 'Dr Amy Chong', 'Dr A Chong', '2014-02-28'),
('AAACC', 'G', 'Dr Vicki Pese', 'Dr V Pese', '2014-02-28'),
('AAACC', 'H', 'Dr Martyn Thomas', 'Dr M Thomas', '2014-02-28'),
('AAACC', 'J', 'Dr Sebastian Rees', 'Dr S Rees', '2014-02-28');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vwLateness`
--
CREATE TABLE IF NOT EXISTS `vwLateness` (
`OrgID` char(5)
,`ID` char(2)
,`FullName` varchar(80)
,`AbbrevName` varchar(20)
,`DateCreated` date
,`OrgName` varchar(50)
,`ClinicID` int(11)
,`ClinicName` varchar(40)
,`MinutesLate` int(6)
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
,`DateCreated` date
,`OrgName` varchar(50)
,`ClinicID` int(11)
,`ClinicName` varchar(40)
,`MinutesLate` int(6)
,`UDID` varchar(40)
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
,`DateCreated` date
,`OrgName` varchar(50)
,`ClinicID` int(11)
,`ClinicName` varchar(40)
);
-- --------------------------------------------------------

--
-- Structure for view `vwLateness`
--
DROP TABLE IF EXISTS `vwLateness`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwLateness` AS select `v`.`OrgID` AS `OrgID`,`v`.`ID` AS `ID`,`v`.`FullName` AS `FullName`,`v`.`AbbrevName` AS `AbbrevName`,`v`.`DateCreated` AS `DateCreated`,`v`.`OrgName` AS `OrgName`,`v`.`ClinicID` AS `ClinicID`,`v`.`ClinicName` AS `ClinicName`,ifnull(`lates`.`Minutes`,0) AS `MinutesLate` from (`vwPlacements` `v` left join `lates` on(((`lates`.`OrgID` = `v`.`OrgID`) and (`lates`.`ID` = `v`.`ID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwMyLates`
--
DROP TABLE IF EXISTS `vwMyLates`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwMyLates` AS select `v`.`OrgID` AS `OrgID`,`v`.`ID` AS `ID`,`v`.`FullName` AS `FullName`,`v`.`AbbrevName` AS `AbbrevName`,`v`.`DateCreated` AS `DateCreated`,`v`.`OrgName` AS `OrgName`,`v`.`ClinicID` AS `ClinicID`,`v`.`ClinicName` AS `ClinicName`,`v`.`MinutesLate` AS `MinutesLate`,`devicereg`.`UDID` AS `UDID` from (`vwLateness` `v` join `devicereg` on(((`v`.`OrgID` = `devicereg`.`OrgID`) and (`v`.`ID` = `devicereg`.`ID`))));

-- --------------------------------------------------------

--
-- Structure for view `vwPlacements`
--
DROP TABLE IF EXISTS `vwPlacements`;

CREATE ALGORITHM=UNDEFINED DEFINER=`howlate`@`localhost` SQL SECURITY DEFINER VIEW `vwPlacements` AS select `prac`.`OrgID` AS `OrgID`,`prac`.`ID` AS `ID`,`prac`.`FullName` AS `FullName`,`prac`.`AbbrevName` AS `AbbrevName`,`prac`.`DateCreated` AS `DateCreated`,`o`.`OrgName` AS `OrgName`,`p`.`ClinicID` AS `ClinicID`,`c`.`ClinicName` AS `ClinicName` from (((`practitioners` `prac` join `orgs` `o` on((`prac`.`OrgID` = `o`.`OrgID`))) join `placements` `p` on(((`p`.`OrgID` = `prac`.`OrgID`) and (`p`.`ID` = `prac`.`ID`)))) join `clinics` `c` on(((`c`.`OrgID` = `prac`.`OrgID`) and (`c`.`ClinicID` = `p`.`ClinicID`))));

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clinics`
--
ALTER TABLE `clinics`
  ADD CONSTRAINT `clinics_ibfk_1` FOREIGN KEY (`OrgID`) REFERENCES `orgs` (`OrgID`);

--
-- Constraints for table `devicereg`
--
ALTER TABLE `devicereg`
  ADD CONSTRAINT `devicereg_ibfk_1` FOREIGN KEY (`OrgID`) REFERENCES `orgs` (`OrgID`);

--
-- Constraints for table `lates`
--
ALTER TABLE `lates`
  ADD CONSTRAINT `lates_ibfk_1` FOREIGN KEY (`OrgID`) REFERENCES `orgs` (`OrgID`);

--
-- Constraints for table `orgusers`
--
ALTER TABLE `orgusers`
  ADD CONSTRAINT `orgusers_ibfk_1` FOREIGN KEY (`OrgID`) REFERENCES `orgs` (`OrgID`);

--
-- Constraints for table `placements`
--
ALTER TABLE `placements`
  ADD CONSTRAINT `placements_ibfk_1` FOREIGN KEY (`OrgID`) REFERENCES `orgs` (`OrgID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
