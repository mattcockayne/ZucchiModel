-- MySQL dump 10.13  Distrib 5.5.29, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: moduledev
-- ------------------------------------------------------
-- Server version	5.5.29-0ubuntu0.12.10.1

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
-- Table structure for table `moduledev_customer`
--

DROP TABLE IF EXISTS `moduledev_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moduledev_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `forename` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `User_Id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `User_Id` (`User_Id`),
  CONSTRAINT `moduledev_customer_ibfk_1` FOREIGN KEY (`User_Id`) REFERENCES `moduledev_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moduledev_customer`
--

LOCK TABLES `moduledev_customer` WRITE;
/*!40000 ALTER TABLE `moduledev_customer` DISABLE KEYS */;
INSERT INTO `moduledev_customer` (`id`, `forename`, `address`, `User_Id`) VALUES (1,'Bob','Eaton House',1),(2,'James','Parkfield Road',2),(3,'Dave','Knutsford',3);
/*!40000 ALTER TABLE `moduledev_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moduledev_elite_customer`
--

DROP TABLE IF EXISTS `moduledev_elite_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moduledev_elite_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `surname` varchar(255) NOT NULL,
  `PremierCustomer_Id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `PremierCustomer_id` (`PremierCustomer_Id`),
  CONSTRAINT `moduledev_elite_customer_ibfk_1` FOREIGN KEY (`PremierCustomer_Id`) REFERENCES `moduledev_premier_customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moduledev_elite_customer`
--

LOCK TABLES `moduledev_elite_customer` WRITE;
/*!40000 ALTER TABLE `moduledev_elite_customer` DISABLE KEYS */;
INSERT INTO `moduledev_elite_customer` (`id`, `surname`, `PremierCustomer_Id`) VALUES (1,'something',1);
/*!40000 ALTER TABLE `moduledev_elite_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moduledev_premier_customer`
--

DROP TABLE IF EXISTS `moduledev_premier_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moduledev_premier_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `discount` int(11) NOT NULL,
  `Customer_Id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Customer_Id` (`Customer_Id`),
  CONSTRAINT `moduledev_premier_customer_ibfk_1` FOREIGN KEY (`Customer_Id`) REFERENCES `moduledev_customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moduledev_premier_customer`
--

LOCK TABLES `moduledev_premier_customer` WRITE;
/*!40000 ALTER TABLE `moduledev_premier_customer` DISABLE KEYS */;
INSERT INTO `moduledev_premier_customer` (`id`, `discount`, `Customer_Id`) VALUES (1,15,1);
/*!40000 ALTER TABLE `moduledev_premier_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moduledev_role`
--

DROP TABLE IF EXISTS `moduledev_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moduledev_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moduledev_role`
--

LOCK TABLES `moduledev_role` WRITE;
/*!40000 ALTER TABLE `moduledev_role` DISABLE KEYS */;
INSERT INTO `moduledev_role` (`id`, `name`) VALUES (1,'test role 1'),(2,'test role 2');
/*!40000 ALTER TABLE `moduledev_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moduledev_user`
--

DROP TABLE IF EXISTS `moduledev_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moduledev_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `forename` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moduledev_user`
--

LOCK TABLES `moduledev_user` WRITE;
/*!40000 ALTER TABLE `moduledev_user` DISABLE KEYS */;
INSERT INTO `moduledev_user` (`id`, `forename`, `surname`, `email`, `createdAt`, `updatedAt`) VALUES (1,'test','4','rick@zucchi.co.uk','2012-11-28 01:01:01','2012-11-28 11:11:11'),(2,'Bill','Cash','bill@jones.me','2012-11-29 02:02:02','2012-11-29 12:12:12'),(3,'Bill','Jones','tom@jones.me','2012-11-30 03:03:03','2012-11-30 13:13:13');
/*!40000 ALTER TABLE `moduledev_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moduledev_user_role`
--

DROP TABLE IF EXISTS `moduledev_user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moduledev_user_role` (
  `User_id` int(11) unsigned NOT NULL,
  `Role_id` int(11) unsigned NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`User_id`,`Role_id`),
  KEY `fk_userrole_role` (`Role_id`),
  CONSTRAINT `fk_userrole_role` FOREIGN KEY (`Role_id`) REFERENCES `moduledev_role` (`id`),
  CONSTRAINT `fk_userrole_user` FOREIGN KEY (`User_id`) REFERENCES `moduledev_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moduledev_user_role`
--

LOCK TABLES `moduledev_user_role` WRITE;
/*!40000 ALTER TABLE `moduledev_user_role` DISABLE KEYS */;
INSERT INTO `moduledev_user_role` (`User_id`, `Role_id`, `sort`) VALUES (1,1,2),(1,2,1),(2,1,1);
/*!40000 ALTER TABLE `moduledev_user_role` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-04-09 15:38:52
