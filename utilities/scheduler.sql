-- MySQL dump 10.13  Distrib 5.1.72, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: devsked
-- ------------------------------------------------------
-- Server version	5.1.72-2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `class`
--

DROP TABLE IF EXISTS `class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='classes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class`
--

LOCK TABLES `class` WRITE;
/*!40000 ALTER TABLE `class` DISABLE KEYS */;
INSERT INTO `class` VALUES (7,'Group Class 1','2013-11-10','2014-01-31');
/*!40000 ALTER TABLE `class` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `number_participants` int(11) unsigned NOT NULL,
  `et_id` int(10) unsigned NOT NULL,
  `duration` int(5) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event`
--

LOCK TABLES `event` WRITE;
/*!40000 ALTER TABLE `event` DISABLE KEYS */;
/*!40000 ALTER TABLE `event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_daytime`
--

DROP TABLE IF EXISTS `event_daytime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_daytime` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `daytime` datetime NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='This is the day and time of a given event so we can show it ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_daytime`
--

LOCK TABLES `event_daytime` WRITE;
/*!40000 ALTER TABLE `event_daytime` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_daytime` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_participant`
--

DROP TABLE IF EXISTS `event_participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_participant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `participant_id` varchar(45) NOT NULL,
  `status_id` int(10) unsigned NOT NULL DEFAULT '1',
  `ep_meta` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='ties the participant to an event';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_participant`
--

LOCK TABLES `event_participant` WRITE;
/*!40000 ALTER TABLE `event_participant` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_participant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_participant_status`
--

DROP TABLE IF EXISTS `event_participant_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_participant_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` char(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='status descriptor';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_participant_status`
--

LOCK TABLES `event_participant_status` WRITE;
/*!40000 ALTER TABLE `event_participant_status` DISABLE KEYS */;
INSERT INTO `event_participant_status` VALUES (1,'Unconfirmed'),(2,'Confirmed');
/*!40000 ALTER TABLE `event_participant_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_type`
--

DROP TABLE IF EXISTS `event_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `et_code` char(10) DEFAULT NULL,
  `et_name` char(255) NOT NULL,
  `et_desc` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='This is the event type (the type of lesson for example)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_type`
--

LOCK TABLES `event_type` WRITE;
/*!40000 ALTER TABLE `event_type` DISABLE KEYS */;
INSERT INTO `event_type` VALUES (20,'DE_1','Default Event','This is a typical event that you can schedule');
/*!40000 ALTER TABLE `event_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leader`
--

DROP TABLE IF EXISTS `leader`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leader` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(45) NOT NULL,
  `lname` varchar(45) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='leads events';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leader`
--

LOCK TABLES `leader` WRITE;
/*!40000 ALTER TABLE `leader` DISABLE KEYS */;
INSERT INTO `leader` VALUES (13,'Pat','Smith','psmith@swiftscheduler.com');
/*!40000 ALTER TABLE `leader` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='this is where the event happens';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
INSERT INTO `location` VALUES (1,'Round Pool'),(2,'Lap Pool');
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` char(255) NOT NULL,
  `lname` char(255) NOT NULL,
  `email` varchar(45) NOT NULL,
  `password` char(255) NOT NULL,
  `log_level` tinyint(4) NOT NULL DEFAULT '0',
  `login_hash` char(255) DEFAULT NULL,
  `login_session` char(32) DEFAULT NULL,
  `last_log` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='people who can log into the system to add participants to cl';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login`
--

LOCK TABLES `login` WRITE;
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
INSERT INTO `login` VALUES (3487,'Troy','Vitullo','troy@troyvit.com','e99a18c428cb38d5f260853678922e03',3,NULL,NULL,'2013-11-24 22:51:01');
/*!40000 ALTER TABLE `login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_level`
--

DROP TABLE IF EXISTS `login_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login_level` char(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_level`
--

LOCK TABLES `login_level` WRITE;
/*!40000 ALTER TABLE `login_level` DISABLE KEYS */;
INSERT INTO `login_level` VALUES (1,'login'),(2,'leader'),(3,'admin');
/*!40000 ALTER TABLE `login_level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_participant`
--

DROP TABLE IF EXISTS `login_participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_participant` (
  `login_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='ties a login to one or more participants';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_participant`
--

LOCK TABLES `login_participant` WRITE;
/*!40000 ALTER TABLE `login_participant` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_participant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participant`
--

DROP TABLE IF EXISTS `participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `participant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` char(255) NOT NULL,
  `lname` char(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `p_meta` text COMMENT 'jesus you will regret that one',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='these are the ones who will be at the event';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participant`
--

LOCK TABLES `participant` WRITE;
/*!40000 ALTER TABLE `participant` DISABLE KEYS */;
/*!40000 ALTER TABLE `participant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `private_event`
--

DROP TABLE IF EXISTS `private_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `private_event` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `duration` int(5) unsigned NOT NULL,
  `location_id` int(11) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `status_id` int(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='this is private''s combination of class, event and event_part';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `private_event`
--

LOCK TABLES `private_event` WRITE;
/*!40000 ALTER TABLE `private_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `private_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `private_event_daytime`
--

DROP TABLE IF EXISTS `private_event_daytime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `private_event_daytime` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `private_event_id` int(11) unsigned NOT NULL,
  `daytime` datetime NOT NULL,
  `participant_id` int(11) NOT NULL,
  `ped_meta` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='this is private''s version of event_daytime.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `private_event_daytime`
--

/* registration (untested) */

DROP TABLE IF EXISTS `reg`;
CREATE TABLE IF NOT EXISTS `reg` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reg_title` CHAR(255) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

DROP TABLE IF EXISTS `reg_section`;
CREATE TABLE IF NOT EXISTS `reg_section` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reg_id` INT NOT NULL,
  `order_id` INT NOT NULL,
  `section_name` CHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idreg_section_UNIQUE` (`id` ASC))
COMMENT = 'Section by which to group registration questions'
ENGINE = InnoDB;
  
DROP TABLE IF EXISTS `reg_question`;
CREATE TABLE IF NOT EXISTS `reg_question` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `section_id` INT NOT NULL,
  `order_id` INT NOT NULL,
  `question` TEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

DROP TABLE IF EXISTS `reg_question_preload`;
CREATE TABLE IF NOT EXISTS `reg_question_preload` (
  `id` INT NULL AUTO_INCREMENT, 
  `question_id` INT NOT NULL,
  `answer_group_id` INT NOT NULL,
  `answer` TEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

DROP TABLE IF EXISTS `login_participant_reg`;
CREATE TABLE IF NOT EXISTS `login_participant_reg` (
  `id` INT NULL AUTO_INCREMENT, 
  `login_participant_id` int(11) NOT NULL,
  `reg_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`))
COMMENT = 'Ties a registration to a login_participant hash'
ENGINE = InnoDB;

/* registration address info (untested) */

DROP TABLE IF EXISTS `login_address`;
CREATE TABLE `login_address` (
  `id` INT NULL AUTO_INCREMENT, 
  `login_id` int(11) NOT NULL,
  `address_type_id` int(11) NOT NULL,
  `is_primary` int(11) NOT NULL,
  `fname` CHAR(255) NOT NULL,
  `lname` CHAR(255) NOT NULL,
  `address_1` text DEFAULT NULL,
  `address_2` text DEFAULT NULL,
  `city` CHAR(255) NOT NULL,
  `state` CHAR(255) NOT NULL,
  `zip` CHAR(255) NOT NULL,
  `country` CHAR(100) NOT NULL DEFAULT 'USA',
  `h_phone` CHAR(255) DEFAULT NULL,
  `c_phone` CHAR(255) DEFAULT NULL,
  `b_phone` CHAR(255) DEFAULT NULL,
  `email` text DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

DROP TABLE IF EXISTS `address_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address_type` char(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='This is the address type (home, business, etc.)';

INSERT INTO address_type (address_type) VALUES ("Mother's address"),("Father's address"),("Alternate address");

UNLOCK TABLES;
