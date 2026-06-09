-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: bus_tracker_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `active_trips`
--

DROP TABLE IF EXISTS `active_trips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `active_trips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `bus_id` varchar(50) NOT NULL,
  `from_city` varchar(100) NOT NULL,
  `to_city` varchar(100) NOT NULL,
  `crowd_level` varchar(20) DEFAULT 'Medium',
  `status` varchar(30) DEFAULT 'Running',
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `last_moving_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_ping_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_active_driver` (`driver_id`),
  UNIQUE KEY `uniq_active_bus` (`bus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `active_trips`
--

LOCK TABLES `active_trips` WRITE;
/*!40000 ALTER TABLE `active_trips` DISABLE KEYS */;
INSERT INTO `active_trips` VALUES (13,2,'KA-19-B-9987','Puttur','Mangalore','Medium','Running',12.799,75.2422,'2026-05-05 10:33:50','2026-05-05 10:33:50','2026-05-05 10:32:46');
/*!40000 ALTER TABLE `active_trips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$BByPXOPjOOXJjJYpDkWOcuwZfODW7QCGIvsD49fGfUhbQ5QqGokD2');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bus_emergencies`
--

DROP TABLE IF EXISTS `bus_emergencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bus_emergencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bus_id` varchar(50) NOT NULL,
  `driver_username` varchar(100) NOT NULL,
  `issue_type` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bus_emergencies`
--

LOCK TABLES `bus_emergencies` WRITE;
/*!40000 ALTER TABLE `bus_emergencies` DISABLE KEYS */;
/*!40000 ALTER TABLE `bus_emergencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bus_location`
--

DROP TABLE IF EXISTS `bus_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bus_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bus_id` varchar(50) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `from_city` varchar(100) DEFAULT NULL,
  `to_city` varchar(100) DEFAULT NULL,
  `crowd_level` varchar(20) DEFAULT 'Medium',
  `status` varchar(20) DEFAULT 'Running',
  `last_moving_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bus_id` (`bus_id`),
  UNIQUE KEY `uniq_bus_location_bus` (`bus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1781 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bus_location`
--

LOCK TABLES `bus_location` WRITE;
/*!40000 ALTER TABLE `bus_location` DISABLE KEYS */;
INSERT INTO `bus_location` VALUES (1,'KA-19-B-9987',12.799,75.2422,'2026-05-05 10:33:50','Puttur','Mangalore','Medium','Running','2026-05-05 10:33:50'),(1032,'KA-19-B-9989',12.6392,75.5619,'2026-05-04 10:02:34','Kukke Subramanya','Sullia','Low','Running','2026-05-04 09:51:33');
/*!40000 ALTER TABLE `bus_location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buses`
--

DROP TABLE IF EXISTS `buses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `buses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bus_id` varchar(50) NOT NULL,
  `bus_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bus_id` (`bus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buses`
--

LOCK TABLES `buses` WRITE;
/*!40000 ALTER TABLE `buses` DISABLE KEYS */;
INSERT INTO `buses` VALUES (1,'KA-21-F-4455','KSRTC Rajahamsa'),(2,'KA-19-D-2233','VRL Coastal Travels'),(3,'KA-19-A-9988','Kerala State Transport'),(4,'KA-21-B-5566','Durgamba Motors'),(5,'KL-14-C-7777','Sugama Tourist'),(13,'KA-19-B-9988','KSRTC'),(16,'KA-19-B-9987','KSRTC'),(22,'KA-19-Z-3611','Demo Fleet Bus'),(28,'KA-19-Z-3669','Demo Fleet Bus'),(34,'KA-19-Z-3706','Demo Fleet Bus'),(36,'KA-19-B-9989','KSRTC');
/*!40000 ALTER TABLE `buses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trip_bus_id` varchar(50) NOT NULL,
  `sender_type` enum('passenger','driver') NOT NULL,
  `sender_id` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chats`
--

LOCK TABLES `chats` WRITE;
/*!40000 ALTER TABLE `chats` DISABLE KEYS */;
INSERT INTO `chats` VALUES (9,'KA-19-B-9987','passenger','8088958663','Hlo','2026-05-05 10:33:41');
/*!40000 ALTER TABLE `chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `city_name` (`city_name`)
) ENGINE=InnoDB AUTO_INCREMENT=499 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cities`
--

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;
INSERT INTO `cities` VALUES (116,'Afzalpur'),(115,'Aland'),(42,'Alur'),(19,'Ankola'),(43,'Arkalgud'),(46,'Arsikere'),(88,'Athani'),(126,'Aurad'),(101,'Badami'),(98,'Bagalkot'),(152,'Bagepalli'),(89,'Bailhongal'),(107,'Ballari (Bellary)'),(1,'Bangalore'),(145,'Bangarapet'),(9,'Bantwal'),(123,'Basavakalyan'),(87,'Belagavi (Belgaum)'),(8,'Belthangady'),(39,'Belur'),(30,'Bhadravathi'),(124,'Bhalki'),(14,'Bhatkal'),(122,'Bidar'),(143,'Byadgi'),(13,'Byndoor'),(133,'Challakere'),(75,'Chamarajanagar'),(131,'Channagiri'),(58,'Channapatna'),(45,'Channarayapatna'),(150,'Chikkaballapur'),(33,'Chikkamagaluru'),(50,'Chikkanayakanahalli'),(90,'Chikkodi'),(118,'Chincholi'),(154,'Chintamani'),(132,'Chitradurga'),(114,'Chittapur'),(127,'Davangere'),(157,'Dharmasthala'),(6,'Dharwad'),(103,'Gadag'),(105,'Gangavathi'),(151,'Gauribidanur'),(91,'Gokak'),(17,'Gokarna'),(48,'Gubbi'),(153,'Gudibanda'),(76,'Gundlupet'),(40,'Halebeedu'),(23,'Haliyal'),(79,'Hanur'),(128,'Harihara'),(38,'Hassan'),(138,'Haveri'),(71,'Heggadadevanakote'),(140,'Hirekerur'),(134,'Hiriyur'),(135,'Holalkere'),(44,'Holenarasipura'),(130,'Honnali'),(15,'Honnavar'),(136,'Hosadurga'),(106,'Hosapete (Hospet)'),(83,'Hubballi (Hubli)'),(92,'Hukkeri'),(125,'Humnabad'),(72,'Hunsur'),(102,'Ilkal'),(129,'Jagalur'),(99,'Jamkhandi'),(117,'Jewargi'),(24,'Joida'),(32,'Kadur'),(112,'Kalaburagi (Gulbarga)'),(84,'Kalghatgi'),(59,'Kanakapura'),(11,'Karkala'),(18,'Karwar'),(5,'Kasaragod'),(93,'Khanapur'),(144,'Kolar'),(149,'Kolar Gold Fields (KGF)'),(77,'Kollegal'),(35,'Koppa'),(104,'Koppal'),(53,'Koratagere'),(162,'Kotekar'),(66,'Krishnarajapet'),(156,'Kukke Subramanya'),(16,'Kumta'),(12,'Kundapura'),(85,'Kundgol'),(55,'Kunigal'),(62,'Maddur'),(52,'Madhugiri'),(80,'Madikeri'),(60,'Magadi'),(63,'Malavalli'),(146,'Malur'),(61,'Mandya'),(2,'Mangalore'),(158,'Manipal'),(137,'Molakalmuru'),(10,'Moodabidri'),(100,'Mudhol'),(34,'Mudigere'),(147,'Mulbagal'),(25,'Mundgod'),(68,'Mysuru (Mysore)'),(67,'Nagamangala'),(70,'Nanjangud'),(37,'Narasimharajapura'),(86,'Navalgund'),(65,'Pandavapura'),(54,'Pavagada'),(73,'Piriyapatna'),(3,'Puttur'),(94,'Raibag'),(110,'Raichur'),(57,'Ramanagara'),(95,'Ramdurg'),(139,'Ranebennur'),(26,'Sagara'),(41,'Sakleshpur'),(74,'Saligrama'),(109,'Sandur'),(96,'Savadatti'),(142,'Savanoor'),(113,'Sedam'),(120,'Shahapur'),(141,'Shiggaon'),(28,'Shikaripura'),(29,'Shivamogga (Shimoga)'),(21,'Siddapur'),(155,'Sidlaghatta'),(111,'Sindhanur'),(51,'Sira'),(20,'Sirsi'),(108,'Siruguppa'),(163,'Someshwara'),(82,'Somwarpet'),(27,'Soraba'),(36,'Sringeri'),(148,'Srinivaspur'),(64,'Srirangapatna'),(7,'Sullia'),(165,'Surathkal'),(121,'Surpur'),(31,'Tarikere'),(49,'Tiptur'),(69,'Tirumakudalu Narasipura'),(47,'Tumakuru (Tumkur)'),(56,'Turuvekere'),(4,'Udupi'),(159,'Ujire'),(164,'Ullal'),(160,'Uppinangady'),(97,'Vijayapura (Bijapur)'),(81,'Virajpet'),(161,'Vittal'),(119,'Yadgir'),(78,'Yelandur'),(22,'Yellapur');
/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bus_id` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drivers`
--

LOCK TABLES `drivers` WRITE;
/*!40000 ALTER TABLE `drivers` DISABLE KEYS */;
INSERT INTO `drivers` VALUES (1,'Ram','$2y$10$8ujLb2rzEfpaT8zogIvwTeRzq0NclViOpNR1tJdkdtP9gCayYYE9S','BUS-101','2026-04-19 06:48:38',0),(2,'Sam','$2y$10$gUagwazTW45RG.cOjK8iUuoarpLN/ttZbci5RxGCDeJ.x8JJmhC1C','','2026-04-23 11:10:28',1),(3,'drv83611','$2y$10$hjofYW43il6ssCDattm.mu0WPq2HVxnBnKMjhM/2mK2z6XHHzwADW','KA-19-Z-3611','2026-04-23 17:03:31',0),(4,'drv83669','$2y$10$fisZ6IoZY6AQVGtcSQ2kEOVu3dQte2ecX31cnF1EWQFW//p5pPsMy','KA-19-Z-3669','2026-04-23 17:04:29',1),(5,'drv83706','$2y$10$BdnEoExTfwkFNJFc2NyPZu3l7B8.QFrFlcCSefJg4m9cD2N7de92C','KA-19-Z-3706','2026-04-23 17:05:06',0),(6,'demo_driver','$2y$10$3TzQ0gzs6xJhvmfVPPuckOGFp0wso2/MqY8ZdtbPltAB83QtDeJCm','KA-19-B-9987','2026-05-04 09:20:01',1);
/*!40000 ALTER TABLE `drivers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lost_items`
--

DROP TABLE IF EXISTS `lost_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lost_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bus_id` varchar(50) NOT NULL,
  `passenger_name` varchar(100) NOT NULL,
  `passenger_phone` varchar(20) NOT NULL,
  `item_description` text NOT NULL,
  `status` varchar(20) DEFAULT 'Lost',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lost_items`
--

LOCK TABLES `lost_items` WRITE;
/*!40000 ALTER TABLE `lost_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `lost_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passenger_history`
--

DROP TABLE IF EXISTS `passenger_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `passenger_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL,
  `bus_id` varchar(50) NOT NULL,
  `from_city` varchar(100) DEFAULT NULL,
  `to_city` varchar(100) DEFAULT NULL,
  `travel_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passenger_history`
--

LOCK TABLES `passenger_history` WRITE;
/*!40000 ALTER TABLE `passenger_history` DISABLE KEYS */;
INSERT INTO `passenger_history` VALUES (1,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 16:49:26'),(2,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 17:02:03'),(3,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 18:16:31'),(4,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 18:22:27'),(5,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 18:59:54'),(6,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 19:17:19'),(7,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 19:19:09'),(8,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 19:19:29'),(9,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 19:31:55'),(10,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-19 19:40:00'),(11,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-23 17:47:36'),(12,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-23 18:09:27'),(13,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-24 04:57:28'),(14,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-24 06:09:59'),(15,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-24 06:21:07'),(16,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-24 19:48:08'),(17,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-25 05:48:25'),(18,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-28 17:07:20'),(19,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-28 17:07:53'),(20,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-28 17:08:29'),(21,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-28 17:08:34'),(22,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-28 20:35:15'),(23,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-28 20:43:26'),(24,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-28 20:56:16'),(25,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-28 20:57:32'),(26,'8088958663','KA-19-A-9988','Puttur','Sullia','2026-04-28 20:58:10'),(27,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-28 20:58:47'),(28,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-28 20:59:40'),(29,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-29 04:31:59'),(30,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-29 04:32:22'),(31,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-29 05:04:18'),(32,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-29 05:21:32'),(33,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-29 05:32:59'),(34,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-29 07:19:32'),(35,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-29 07:24:29'),(36,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-04-29 07:25:11'),(37,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-02 06:59:02'),(38,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-02 06:59:28'),(39,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-02 07:11:33'),(40,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-02 10:06:52'),(41,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-02 11:07:37'),(42,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-04 07:12:24'),(43,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-04 07:15:24'),(44,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-04 09:33:22'),(45,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-04 09:33:41'),(46,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-04 09:33:46'),(47,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-04 09:33:50'),(48,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-04 09:33:53'),(49,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-04 09:36:10'),(50,'8088958663','KA-19-B-9987','Puttur','Sullia','2026-05-04 09:39:10'),(51,'8088958663','KA-19-B-9989','Kukke Subramanya','Sullia','2026-05-04 09:51:49'),(52,'8088958663','KA-19-B-9989','Kukke Subramanya','Sullia','2026-05-04 09:56:36'),(53,'8088958663','KA-19-B-9989','Kukke Subramanya','Sullia','2026-05-04 10:01:13'),(54,'8088958663','KA-19-B-9989','Kukke Subramanya','Sullia','2026-05-04 10:04:44'),(55,'8088958663','KA-19-B-9989','Kukke Subramanya','Sullia','2026-05-04 10:05:04'),(56,'8088958663','KA-19-B-9987','Kukke Subramanya','Puttur','2026-05-05 07:23:19'),(57,'8088958663','KA-19-B-9987','Kukke Subramanya','Puttur','2026-05-05 07:39:34'),(58,'9876543210','KA-19-B-9987','Mangalore','Bangalore','2026-05-05 07:49:31'),(59,'8088958663','KA-19-B-9987','Kukke Subramanya','Puttur','2026-05-05 07:52:34'),(60,'9876543210','KA-19-B-9987','Mangalore','Bangalore','2026-05-05 07:59:34'),(61,'9876543210','KA-19-B-9987','Mangalore','Bangalore','2026-05-05 08:05:06'),(62,'9876543210','KA-19-B-9987','Mangalore','Bangalore','2026-05-05 08:05:16'),(63,'9876543210','KA-19-B-9987','Mangalore','Bangalore','2026-05-05 08:24:38'),(64,'8088958663','KA-19-B-9987','Kukke Subramanya','Puttur','2026-05-05 10:15:17'),(65,'8088958663','KA-19-B-9987','Kukke Subramanya','Puttur','2026-05-05 10:28:09'),(66,'8088958663','KA-19-B-9987','Puttur','Mangalore','2026-05-05 10:33:29'),(67,'8088958663','KA-19-B-9987','Puttur','Mangalore','2026-05-05 10:34:25'),(68,'8088958663','KA-19-B-9987','Puttur','Mangalore','2026-05-05 10:34:56'),(69,'8088958663','KA-19-B-9987','Puttur','Mangalore','2026-05-05 10:51:05'),(70,'8088958663','KA-19-B-9987','Puttur','Mangalore','2026-05-05 11:18:57'),(71,'8088958663','KA-19-B-9987','Puttur','Mangalore','2026-05-05 11:25:29');
/*!40000 ALTER TABLE `passenger_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passengers`
--

DROP TABLE IF EXISTS `passengers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `passengers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passengers`
--

LOCK TABLES `passengers` WRITE;
/*!40000 ALTER TABLE `passengers` DISABLE KEYS */;
INSERT INTO `passengers` VALUES (1,'8088958663','$2y$10$QXftGx58.J2VyOFMaGfeBeboTvouuE2Z/bFKiGxAHNyy5G3lcKh0.','2026-04-19 16:21:25'),(2,'9776983706','$2y$10$HDdk10MUPLhFuxJyRSZI5ulZeNaCqFZ5Pw425udgdA49F3qvedG12','2026-04-23 17:05:08'),(3,'9999999999','$2y$10$RHOcQq1OLZtXOkXIaV1l5.lUjlM4.8WUavHPESal8ZZBlRU4WH9Ai','2026-04-25 05:52:40'),(4,'9480523762','$2y$10$n5lyrzYTB1uXki4af2pideUm9H/JSn2qYqgAqIi7CuzoMbzoRNA2e','2026-05-04 09:29:41'),(6,'9876543210','$2y$10$9itppsE4Sv2x8ErbUBOIceZQf2A46eGA9JfwOQEziGMebIzUIJX7.','2026-05-05 08:03:54');
/*!40000 ALTER TABLE `passengers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trip_history_summary`
--

DROP TABLE IF EXISTS `trip_history_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trip_history_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `bus_id` varchar(50) NOT NULL,
  `from_city` varchar(100) NOT NULL,
  `to_city` varchar(100) NOT NULL,
  `start_lat` float DEFAULT NULL,
  `start_lng` float DEFAULT NULL,
  `end_lat` float DEFAULT NULL,
  `end_lng` float DEFAULT NULL,
  `ended_reason` varchar(30) DEFAULT 'manual',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trip_history_summary`
--

LOCK TABLES `trip_history_summary` WRITE;
/*!40000 ALTER TABLE `trip_history_summary` DISABLE KEYS */;
INSERT INTO `trip_history_summary` VALUES (1,5,'KA-19-Z-3706','Mangalore','Bangalore',12.9155,74.8578,12.9155,74.8578,'manual','2026-04-23 17:05:06','2026-04-23 17:05:08'),(2,2,'KA-19-B-9988','Puttur','Sullia',12.3197,76.6612,12.3197,76.6612,'manual','2026-04-23 18:30:31','2026-04-24 06:06:32'),(3,2,'KA-19-B-9987','Puttur','Sullia',12.7439,75.2127,12.7439,75.2127,'manual','2026-04-24 06:06:54','2026-04-24 06:06:59'),(4,2,'KA-19-B-9987','Puttur','Sullia',12.7439,75.2127,12.7439,75.2127,'manual','2026-04-24 06:07:01','2026-04-24 19:39:27'),(5,2,'KA-19-B-9987','Puttur','Sullia',12.7804,75.1869,12.7804,75.1869,'manual','2026-04-25 05:53:28','2026-04-25 05:53:48'),(6,2,'KA-19-B-9987','Puttur','Sullia',13.9337,75.573,13.9337,75.573,'manual','2026-04-28 17:09:48','2026-04-28 17:09:54'),(7,2,'KA-19-B-9987','Puttur','Sullia',12.736,75.2134,12.736,75.2134,'manual','2026-04-28 20:53:50','2026-04-28 20:59:05'),(8,2,'KA-19-B-9987','Puttur','Sullia',12.736,75.2134,12.736,75.2134,'manual','2026-04-29 05:04:11','2026-04-29 05:05:29'),(9,2,'KA-19-B-9987','Puttur','Sullia',12.736,75.2134,12.736,75.2134,'manual','2026-04-29 05:05:52','2026-04-29 05:09:48'),(10,2,'KA-19-B-9987','Puttur','Sullia',14.7947,75.4112,14.7947,75.4112,'manual','2026-04-29 05:11:00','2026-05-04 09:40:35'),(11,2,'KA-19-B-9987','Gundya','Puttur',12.8326,75.2822,12.8326,75.2822,'manual','2026-05-05 10:27:01','2026-05-05 10:29:37'),(12,2,'KA-19-B-9987','Gundya','Puttur',12.8166,75.2509,12.8166,75.2509,'manual','2026-05-05 10:29:49','2026-05-05 10:30:10'),(13,2,'KA-19-B-9987','Puttur','Mangaluru ',12.8049,75.24,12.8049,75.24,'manual','2026-05-05 10:31:06','2026-05-05 10:32:26');
/*!40000 ALTER TABLE `trip_history_summary` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-05 18:04:34
