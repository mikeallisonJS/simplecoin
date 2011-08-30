-- MySQL dump 10.13  Distrib 5.1.54, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: simplecoin
-- ------------------------------------------------------
-- Server version	5.1.54-1ubuntu4-log

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
-- Table structure for table `accountBalance`
--

DROP TABLE IF EXISTS `accountBalance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountBalance` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `userId` int(255) NOT NULL,
  `balance` varchar(40) DEFAULT NULL,
  `sendAddress` varchar(255) DEFAULT '',
  `paid` varchar(40) DEFAULT '0',
  `threshold` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`),
  KEY `b_userId` (`userId`)
) ENGINE=MyISAM AUTO_INCREMENT=706 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountBalance`
--
-- WHERE:  1=0

LOCK TABLES `accountBalance` WRITE;
/*!40000 ALTER TABLE `accountBalance` DISABLE KEYS */;
/*!40000 ALTER TABLE `accountBalance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locks`
--

DROP TABLE IF EXISTS `locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locks` (
  `name` varchar(11) NOT NULL,
  `locked` tinyint(4) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locks`
--
-- WHERE:  1=0

LOCK TABLES `locks` WRITE;
/*!40000 ALTER TABLE `locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `networkBlocks`
--

DROP TABLE IF EXISTS `networkBlocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `networkBlocks` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `blockNumber` int(255) NOT NULL,
  `timestamp` int(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5876 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `networkBlocks`
--
-- WHERE:  1=0

LOCK TABLES `networkBlocks` WRITE;
/*!40000 ALTER TABLE `networkBlocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `networkBlocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `message` varchar(255) NOT NULL,
  `timestap` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ' ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--
-- WHERE:  1=0

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pool_worker`
--

DROP TABLE IF EXISTS `pool_worker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pool_worker` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `associatedUserId` int(255) NOT NULL,
  `username` char(50) DEFAULT NULL,
  `password` char(255) DEFAULT NULL,
  `allowed_hosts` text,
  PRIMARY KEY (`id`),
  KEY `p_username` (`username`),
  KEY `pool_worker_associatedUserId` (`associatedUserId`),
  KEY `pool_worker_username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=1398 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pool_worker`
--
-- WHERE:  1=0

LOCK TABLES `pool_worker` WRITE;
/*!40000 ALTER TABLE `pool_worker` DISABLE KEYS */;
/*!40000 ALTER TABLE `pool_worker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`setting`),
  KEY `set_setting` (`setting`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--
-- WHERE:  1=0

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shares`
--

DROP TABLE IF EXISTS `shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shares` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `rem_host` varchar(255) NOT NULL,
  `username` varchar(120) NOT NULL,
  `our_result` enum('Y','N') NOT NULL,
  `upstream_result` enum('Y','N') DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `solution` varchar(257) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `shares_username` (`username`),
  KEY `shares_time` (`time`),
  KEY `shares_our_result` (`our_result`)
) ENGINE=InnoDB AUTO_INCREMENT=17615344 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shares`
--
-- WHERE:  1=0

LOCK TABLES `shares` WRITE;
/*!40000 ALTER TABLE `shares` DISABLE KEYS */;
/*!40000 ALTER TABLE `shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shares_counted`
--

DROP TABLE IF EXISTS `shares_counted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shares_counted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blockNumber` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `invalid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=614 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shares_counted`
--
-- WHERE:  1=0

LOCK TABLES `shares_counted` WRITE;
/*!40000 ALTER TABLE `shares_counted` DISABLE KEYS */;
/*!40000 ALTER TABLE `shares_counted` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unconfirmed_rewards`
--

DROP TABLE IF EXISTS `unconfirmed_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unconfirmed_rewards` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `userId` int(255) NOT NULL,
  `blockNumber` int(255) NOT NULL,
  `amount` varchar(40) NOT NULL,
  `rewarded` enum('N','Y') NOT NULL DEFAULT 'N',
  `shares` int(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `unconfirmed_rewards_blockNumber` (`blockNumber`),
  KEY `unconfirmed_rewards_rewarded` (`rewarded`),
  KEY `unconfirmed_rewards_userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unconfirmed_rewards`
--
-- WHERE:  1=0

LOCK TABLES `unconfirmed_rewards` WRITE;
/*!40000 ALTER TABLE `unconfirmed_rewards` DISABLE KEYS */;
/*!40000 ALTER TABLE `unconfirmed_rewards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userHashrates`
--

DROP TABLE IF EXISTS `userHashrates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userHashrates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userId` int(255) NOT NULL,
  `hashrate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `userHashrates_id1` (`userId`),
  KEY `userId_timestamp` (`userId`,`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=2253830 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userHashrates`
--
-- WHERE:  1=0

LOCK TABLES `userHashrates` WRITE;
/*!40000 ALTER TABLE `userHashrates` DISABLE KEYS */;
/*!40000 ALTER TABLE `userHashrates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webUsers`
--

DROP TABLE IF EXISTS `webUsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webUsers` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `admin` int(1) NOT NULL,
  `username` varchar(40) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL COMMENT 'Assocaited email: used for validating users, and re-setting passwords',
  `emailAuthPin` varchar(10) NOT NULL COMMENT 'The pin required to authorize that email address',
  `secret` varchar(10) NOT NULL,
  `loggedIp` varchar(255) NOT NULL,
  `sessionTimeoutStamp` int(255) NOT NULL,
  `accountLocked` int(255) NOT NULL COMMENT 'This is the timestamp when the account will be unlocked(usually used to lock accounts that are trying to be bruteforced)',
  `accountFailedAttempts` int(2) NOT NULL COMMENT 'This counts the number of failed attempts for web login',
  `pin` varchar(255) NOT NULL COMMENT 'four digit pin to allow account changes',
  `share_count` int(11) NOT NULL DEFAULT '0',
  `stale_share_count` int(11) NOT NULL DEFAULT '0',
  `shares_this_round` int(11) NOT NULL DEFAULT '0',
  `api_key` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `activeEmail` int(1) DEFAULT NULL,
  `donate_percent` varchar(11) DEFAULT '1',
  `btc_lock` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `u_username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=710 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webUsers`
--
-- WHERE:  1=0

LOCK TABLES `webUsers` WRITE;
/*!40000 ALTER TABLE `webUsers` DISABLE KEYS */;
/*!40000 ALTER TABLE `webUsers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `winning_shares`
--

DROP TABLE IF EXISTS `winning_shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `winning_shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blockNumber` int(11) NOT NULL,
  `username` varchar(11) NOT NULL,
  `share_id` int(255) NOT NULL DEFAULT '0',
  `rewarded` enum('N','Y') NOT NULL DEFAULT 'N',
  `amount` varchar(40) NOT NULL DEFAULT '0',
  `confirms` smallint(6) NOT NULL DEFAULT '0',
  `txid` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `scored` enum('N','Y') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `winning_shares_scored` (`scored`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `winning_shares`
--
-- WHERE:  1=0

LOCK TABLES `winning_shares` WRITE;
/*!40000 ALTER TABLE `winning_shares` DISABLE KEYS */;
/*!40000 ALTER TABLE `winning_shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'simplecoin'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-08-26 18:28:36
