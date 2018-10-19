-- MySQL dump 10.13  Distrib 5.5.38, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: gardes_site
-- ------------------------------------------------------
-- Server version	5.5.38-0+wheezy1

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
-- Table structure for table `Pages`
--

DROP TABLE IF EXISTS `Pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(255) NOT NULL,
  `page` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=487550 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `calendrier`
--

DROP TABLE IF EXISTS `calendrier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendrier` (
  `date` date NOT NULL,
  `pharmacien` int(11) DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `day` int(11) NOT NULL,
  PRIMARY KEY (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dettes`
--

DROP TABLE IF EXISTS `dettes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dettes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pharmacien` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `graces`
--

DROP TABLE IF EXISTS `graces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graces` (
  `type` varchar(11) NOT NULL,
  `domapp` int(11) NOT NULL,
  `grace` int(11) NOT NULL,
  `domapp2` int(11) NOT NULL DEFAULT '10',
  `grace2` int(11) NOT NULL DEFAULT '0',
  `ordre` int(11) NOT NULL,
  PRIMARY KEY (`type`),
  UNIQUE KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pharmaciens`
--

DROP TABLE IF EXISTS `pharmaciens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmaciens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Nom` varchar(80) NOT NULL,
  `preference` int(11) NOT NULL DEFAULT '1',
  `lundi` int(11) NOT NULL DEFAULT '2',
  `detlun` int(11) NOT NULL DEFAULT '0',
  `detmar` int(11) NOT NULL DEFAULT '0',
  `detmer` int(11) NOT NULL DEFAULT '0',
  `detjeu` int(11) NOT NULL DEFAULT '0',
  `detven` int(11) NOT NULL DEFAULT '0',
  `detsam` int(11) NOT NULL DEFAULT '0',
  `detsun` int(11) NOT NULL DEFAULT '0',
  `detfer` int(11) NOT NULL DEFAULT '0',
  `detday` int(11) NOT NULL DEFAULT '0',
  `detbig` int(11) NOT NULL DEFAULT '0',
  `ordre` int(11) NOT NULL,
  `mail` varchar(80) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-05-09  3:32:13
