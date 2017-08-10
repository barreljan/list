-- MySQL dump 10.14  Distrib 5.5.48-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: webroot
-- ------------------------------------------------------
-- Server version	5.5.48-MariaDB

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `groupid` int(4) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(20) DEFAULT NULL,
  `dateadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`groupid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'administrators','2017-08-10 10:00:00',1);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `lijst`
--

DROP TABLE IF EXISTS `list`;
CREATE TABLE `list` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item` varchar(100) DEFAULT NULL,
  `dateadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemodified` timestamp NULL DEFAULT NULL,
  `datedeleted` timestamp NULL DEFAULT NULL,
  `owner` varchar(20) DEFAULT NULL,
  `changedby` varchar(20) DEFAULT NULL,
  `deletedby` varchar(20) DEFAULT NULL,
  `ordered` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `list` tinyint(1) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `emailout` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `groupid` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientip` varchar(100) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `authenticated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `dateadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `emailadres` varchar(255) DEFAULT NULL,
  `groupid` tinyint(1) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `admin` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$12$8lbzZ0lvEM1aA330wMMX0eNyvWpUmGhTenaYvd3sCe0wTp5i.wFry','2017-08-10 10:00:00','admin@someaddress',1,1,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`
CREATE TABLE `sessions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `endtime` timestamp NOT NULL,
  `clientip` varchar(100) DEFAULT NULL,
  `sessionid` varchar(255) DEFAULT NULL,
  `uagent` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

