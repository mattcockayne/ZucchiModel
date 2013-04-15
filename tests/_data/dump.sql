-- MySQL dump 10.13  Distrib 5.5.29, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: test_zucchimodel
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
-- Table structure for table `test_zucchimodel_customer`
--

DROP TABLE IF EXISTS `test_zucchimodel_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_zucchimodel_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `forename` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `User_Id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `User_Id` (`User_Id`),
  CONSTRAINT `test_zucchimodel_customer_ibfk_1` FOREIGN KEY (`User_Id`) REFERENCES `test_zucchimodel_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_zucchimodel_customer`
--

LOCK TABLES `test_zucchimodel_customer` WRITE;
/*!40000 ALTER TABLE `test_zucchimodel_customer` DISABLE KEYS */;
INSERT INTO `test_zucchimodel_customer` (`id`, `forename`, `address`, `User_Id`) VALUES (1,'Matt','Reichstag, Platz der Republik 1, 10557 Berlin',1),(2,'Rick','Sir Matt Busby Way, Old Trafford, Manchester, M16 0RA.',2),(3,'Dave','21 Hunts Bank, Victoria Station, Manchester, M3 1AR.',3);
/*!40000 ALTER TABLE `test_zucchimodel_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_zucchimodel_elite_customer`
--

DROP TABLE IF EXISTS `test_zucchimodel_elite_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_zucchimodel_elite_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `surname` varchar(255) NOT NULL,
  `PremierCustomer_Id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `PremierCustomer_id` (`PremierCustomer_Id`),
  CONSTRAINT `test_zucchimodel_elite_customer_ibfk_1` FOREIGN KEY (`PremierCustomer_Id`) REFERENCES `test_zucchimodel_premier_customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_zucchimodel_elite_customer`
--

LOCK TABLES `test_zucchimodel_elite_customer` WRITE;
/*!40000 ALTER TABLE `test_zucchimodel_elite_customer` DISABLE KEYS */;
INSERT INTO `test_zucchimodel_elite_customer` (`id`, `surname`, `PremierCustomer_Id`) VALUES (1,'Black',1);
/*!40000 ALTER TABLE `test_zucchimodel_elite_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_zucchimodel_premier_customer`
--

DROP TABLE IF EXISTS `test_zucchimodel_premier_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_zucchimodel_premier_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `discount` int(11) NOT NULL,
  `Customer_Id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Customer_Id` (`Customer_Id`),
  CONSTRAINT `test_zucchimodel_premier_customer_ibfk_1` FOREIGN KEY (`Customer_Id`) REFERENCES `test_zucchimodel_customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_zucchimodel_premier_customer`
--

LOCK TABLES `test_zucchimodel_premier_customer` WRITE;
/*!40000 ALTER TABLE `test_zucchimodel_premier_customer` DISABLE KEYS */;
INSERT INTO `test_zucchimodel_premier_customer` (`id`, `discount`, `Customer_Id`) VALUES (1,15,1);
/*!40000 ALTER TABLE `test_zucchimodel_premier_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_zucchimodel_role`
--

DROP TABLE IF EXISTS `test_zucchimodel_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_zucchimodel_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_zucchimodel_role`
--

LOCK TABLES `test_zucchimodel_role` WRITE;
/*!40000 ALTER TABLE `test_zucchimodel_role` DISABLE KEYS */;
INSERT INTO `test_zucchimodel_role` (`id`, `name`) VALUES (1,'Power'),(2,'Normal');
/*!40000 ALTER TABLE `test_zucchimodel_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_zucchimodel_user`
--

DROP TABLE IF EXISTS `test_zucchimodel_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_zucchimodel_user` (
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
-- Dumping data for table `test_zucchimodel_user`
--

LOCK TABLES `test_zucchimodel_user` WRITE;
/*!40000 ALTER TABLE `test_zucchimodel_user` DISABLE KEYS */;
INSERT INTO `test_zucchimodel_user` (`id`, `forename`, `surname`, `email`, `createdAt`, `updatedAt`) VALUES (1,'James','Hetfield','james@me.co.uk','2012-11-28 01:01:01','2012-11-28 11:11:11'),(2,'Kimi','Raikkonen','kimi@me.co.uk','2012-11-29 02:02:02','2012-11-29 12:12:12'),(3,'Eric','Cantona','eric@me.co.uk','2012-11-30 03:03:03','2012-11-30 13:13:13');
/*!40000 ALTER TABLE `test_zucchimodel_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_zucchimodel_user_role`
--

DROP TABLE IF EXISTS `test_zucchimodel_user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_zucchimodel_user_role` (
  `User_id` int(11) unsigned NOT NULL,
  `Role_id` int(11) unsigned NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`User_id`,`Role_id`),
  KEY `fk_userrole_role` (`Role_id`),
  CONSTRAINT `fk_userrole_role` FOREIGN KEY (`Role_id`) REFERENCES `test_zucchimodel_role` (`id`),
  CONSTRAINT `fk_userrole_user` FOREIGN KEY (`User_id`) REFERENCES `test_zucchimodel_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_zucchimodel_user_role`
--

LOCK TABLES `test_zucchimodel_user_role` WRITE;
/*!40000 ALTER TABLE `test_zucchimodel_user_role` DISABLE KEYS */;
INSERT INTO `test_zucchimodel_user_role` (`User_id`, `Role_id`, `sort`) VALUES (1,1,2),(1,2,1),(2,1,1);
/*!40000 ALTER TABLE `test_zucchimodel_user_role` ENABLE KEYS */;
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
