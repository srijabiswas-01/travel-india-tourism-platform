-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: travel_india
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'manager',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'Srija Biswas','srija@gmail.com','$2y$12$NTPVUrFy.6HWd4ImY7oRSu68sgkE7l8fiqdxP9Kb1eVGXRnLiYxHK','super_admin',1,'2026-09-26 11:13:48','2026-10-12 10:44:00');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `admin_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` varchar(50) DEFAULT NULL,
  `details` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'account_status','customer','biswassrija01@gmail.com','disabled','2026-09-26 11:14:41'),(2,1,'account_status','customer','biswassrija01@gmail.com','active','2026-09-26 11:14:42'),(3,1,'respond','message','7','open','2026-09-26 11:16:57'),(4,1,'disable','state','3','','2026-09-26 12:34:51'),(5,1,'enable','state','3','','2026-09-26 12:34:54'),(6,1,'publish','package','13','','2026-09-28 05:23:44'),(7,1,'archive','package','13','','2026-09-28 05:26:43'),(8,1,'publish','package','13','','2026-09-28 05:26:50'),(9,1,'delete','message','6','','2026-09-28 11:07:49'),(10,1,'delete','message','5','','2026-09-28 11:07:53'),(11,1,'delete','message','2','','2026-09-28 11:07:56'),(12,1,'delete','message','1','','2026-09-28 11:07:58'),(13,1,'delete','message','8','','2026-09-28 11:08:02'),(14,1,'reply','message','7','open','2026-09-28 11:08:06'),(15,1,'reply','message','7','resolved','2026-09-28 11:08:24'),(16,1,'reply','message','7','open','2026-09-28 11:08:29'),(17,1,'delete','message','7','','2026-09-28 11:08:32'),(18,1,'delete','message','4','','2026-09-28 11:08:33'),(19,1,'update','region','10','Goa','2026-09-29 05:09:00'),(20,1,'delete','customer','biswassrija01@gmail.com','Booking history retained','2026-09-29 11:42:00'),(21,1,'create','hotel','1','TajSeva','2026-10-01 07:45:11'),(22,1,'create','hotel','2','Tezz Hotel','2026-10-01 07:45:37'),(23,1,'update','hotel','2','Tezz Hotel','2026-10-01 10:21:13'),(24,1,'status_update','booking','1','pending -> pending; payment unpaid -> unpaid','2026-10-01 10:37:26'),(25,1,'status_update','booking','1','pending -> needs_information; payment unpaid -> unpaid','2026-10-01 10:37:55'),(26,1,'status_update','booking','1','pending -> needs_information; payment unpaid -> unpaid','2026-10-01 10:48:35'),(27,1,'status_update','booking','1','pending -> confirmed; payment unpaid -> unpaid','2026-10-01 10:49:36'),(28,1,'status_update','booking','1','confirmed -> in_progress; payment paid -> paid','2026-10-01 10:59:42'),(29,1,'create','hotel','3','Atithi Hotel','2026-10-01 12:01:25'),(30,1,'create','hotel','4','Mountain Peak Hotel','2026-10-01 12:02:22'),(31,1,'status_update','booking','2','pending -> needs_information; payment unpaid -> unpaid','2026-10-01 12:03:52'),(32,1,'status_update','booking','2','pending -> confirmed; payment unpaid -> unpaid','2026-10-01 12:04:49'),(33,1,'create','offer','1','DIWALI2026_DEMO_10OFF','2026-10-05 06:13:37'),(34,1,'update','offer','1','DIWALI2026_DEMO_10OFF','2026-10-05 06:14:09'),(35,1,'update','offer','1','DIWALI2026_DEMO_10OFF','2026-10-08 10:53:28'),(36,1,'status_update','booking','3','pending -> cancelled; payment unpaid -> unpaid','2026-10-08 11:06:17'),(37,1,'delete','hotel','1','','2026-10-10 05:33:13'),(38,1,'update','accommodation','4','Premium accommodation','2026-10-10 05:41:50'),(39,1,'disable','hotel','4','','2026-10-10 05:46:21'),(40,1,'enable','hotel','4','','2026-10-10 05:46:24'),(41,1,'update','meal_plan','1','Standard meals','2026-10-10 10:30:33'),(42,1,'create','accommodation','5','Premium accommodation','2026-10-10 10:44:09'),(43,1,'create','meal_plan','2','Premium meals','2026-10-10 10:44:55'),(44,1,'approve','cancellation_request','2','TI-261001-6C6C18','2026-10-12 06:14:12'),(45,1,'status_update','booking','2','cancelled -> cancelled; payment refund_pending -> refunded','2026-10-12 06:15:30'),(46,1,'update','offer','1','DIWALI2026_DEMO_10OFF','2026-10-12 11:17:13');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_amendments`
--

DROP TABLE IF EXISTS `booking_amendments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_amendments` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `old_from_date` date NOT NULL,
  `old_to_date` date NOT NULL,
  `requested_from_date` date NOT NULL,
  `requested_to_date` date NOT NULL,
  `customer_reason` varchar(1000) NOT NULL,
  `admin_response` varchar(1000) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'requested',
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_amendments`
--

LOCK TABLES `booking_amendments` WRITE;
/*!40000 ALTER TABLE `booking_amendments` DISABLE KEYS */;
/*!40000 ALTER TABLE `booking_amendments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_reviews`
--

DROP TABLE IF EXISTS `booking_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_reviews` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `user_email` varchar(500) NOT NULL,
  `package_id` int NOT NULL,
  `hotel_id` int DEFAULT NULL,
  `package_rating` tinyint NOT NULL,
  `hotel_rating` tinyint NOT NULL,
  `review_text` varchar(1000) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_review_unique` (`booking_id`),
  KEY `package_id` (`package_id`),
  KEY `hotel_id` (`hotel_id`),
  KEY `user_email` (`user_email`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_reviews`
--

LOCK TABLES `booking_reviews` WRITE;
/*!40000 ALTER TABLE `booking_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `booking_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_status_history`
--

DROP TABLE IF EXISTS `booking_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_status_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `note` text,
  `changed_by` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_status_history`
--

LOCK TABLES `booking_status_history` WRITE;
/*!40000 ALTER TABLE `booking_status_history` DISABLE KEYS */;
INSERT INTO `booking_status_history` VALUES (1,1,NULL,'pending','Booking request submitted','customer:srijabiswas3001@gmail.com','2026-10-01 10:22:41'),(2,1,'pending','needs_information','Need Information of the specific things','srija@gmail.com','2026-10-01 10:37:55'),(3,1,'needs_information','pending','Customer updated booking request','customer:srijabiswas3001@gmail.com','2026-10-01 10:48:15'),(4,1,'pending','needs_information','Update teh hotels','srija@gmail.com','2026-10-01 10:48:35'),(5,1,'needs_information','pending','Customer updated booking request','customer:srijabiswas3001@gmail.com','2026-10-01 10:48:54'),(6,1,'pending','confirmed','please pay the amount','srija@gmail.com','2026-10-01 10:49:36'),(7,1,'confirmed','in_progress','please pay the amount','srija@gmail.com','2026-10-01 10:59:42'),(8,2,NULL,'pending','Booking request submitted','customer:srijabiswas3001@gmail.com','2026-10-01 12:02:59'),(9,2,'pending','needs_information','Details of the family','srija@gmail.com','2026-10-01 12:03:52'),(10,2,'needs_information','pending','Customer updated booking request','customer:srijabiswas3001@gmail.com','2026-10-01 12:04:23'),(11,2,'pending','confirmed','Pay the amount','srija@gmail.com','2026-10-01 12:04:49'),(12,2,'confirmed','in_progress','Payment TI-PAY-261001122350-0B8887 succeeded; booking moved to in progress.','payment:TI-PAY-261001122350-0B8887','2026-10-01 12:23:50'),(13,3,NULL,'pending','Booking request submitted','customer:srijabiswas3001@gmail.com','2026-10-05 06:34:50'),(14,3,'pending','pending','Cancellation request received: adaddad','customer:srijabiswas3001@gmail.com','2026-10-08 11:05:22'),(15,3,'pending','cancelled','Ok','srija@gmail.com','2026-10-08 11:06:17'),(17,2,'in_progress','in_progress','Cancellation request received: adadadad','customer:srijabiswas3001@gmail.com','2026-10-12 06:07:01'),(18,2,'in_progress','cancelled','Cancellation approved; approved refund ₹30,237.30','srija@gmail.com','2026-10-12 06:14:12');
/*!40000 ALTER TABLE `booking_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_travellers`
--

DROP TABLE IF EXISTS `booking_travellers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_travellers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `family_member_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `relationship` varchar(80) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `age_category` varchar(20) NOT NULL DEFAULT 'adult',
  `medical_issues` text,
  `traveller_type` varchar(30) NOT NULL DEFAULT 'other',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_pwd` tinyint(1) NOT NULL DEFAULT '0',
  `pwd_details` varchar(500) DEFAULT NULL,
  `meal_plan_name` varchar(80) NOT NULL DEFAULT 'Standard meals (included)',
  `food_preference` varchar(30) NOT NULL DEFAULT 'Vegetarian',
  `dietary_notes` varchar(500) DEFAULT NULL,
  `meal_surcharge` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `family_member_id` (`family_member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_travellers`
--

LOCK TABLES `booking_travellers` WRITE;
/*!40000 ALTER TABLE `booking_travellers` DISABLE KEYS */;
INSERT INTO `booking_travellers` VALUES (9,1,NULL,'Srija Biswas','Self','2001-01-30','Female','8956451232','adult','','primary','2026-10-01 10:48:54',0,NULL,'Standard meals (included)','Vegetarian',NULL,0.00),(10,1,2,'Jayanta Biswas','Father','1975-04-04','Male','','adult','','family','2026-10-01 10:48:54',0,NULL,'Standard meals (included)','Vegetarian',NULL,0.00),(11,1,1,'Soma Biswas','Mother','1976-01-01','Female','','adult','','family','2026-10-01 10:48:54',0,NULL,'Standard meals (included)','Vegetarian',NULL,0.00),(12,1,NULL,'Chinmoy Pal','Friend','1999-01-21','Male','9965412536','adult','','other','2026-10-01 10:48:54',0,'','Standard meals (included)','Vegetarian',NULL,0.00),(16,2,NULL,'Srija Biswas','Self','2001-01-30','Female','8956451232','adult','','primary','2026-10-01 12:04:23',0,NULL,'Standard meals (included)','Vegetarian',NULL,0.00),(17,2,2,'Jayanta Biswas','Father','1975-04-04','Male','','adult','','family','2026-10-01 12:04:23',0,NULL,'Standard meals (included)','Vegetarian',NULL,0.00),(18,2,1,'Soma Biswas','Mother','1976-01-01','Female','','adult','','family','2026-10-01 12:04:23',0,NULL,'Standard meals (included)','Vegetarian',NULL,0.00),(19,3,NULL,'Srija Biswas','Self','2001-01-30','Female','8956451232','adult','','primary','2026-10-05 06:34:50',0,NULL,'Standard meals (included)','Vegetarian',NULL,0.00),(20,3,2,'Jayanta Biswas','Father','1975-04-04','Male','','adult','','family','2026-10-05 06:34:50',0,NULL,'Standard meals (included)','Vegetarian',NULL,0.00),(21,3,1,'Soma Biswas','Mother','1976-01-01','Female','','adult','','family','2026-10-05 06:34:50',0,NULL,'Standard meals (included)','Vegetarian',NULL,0.00);
/*!40000 ALTER TABLE `booking_travellers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `destination` varchar(255) NOT NULL,
  `hotel` varchar(255) NOT NULL,
  `people` int NOT NULL,
  `customer_name` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `booking_reference` varchar(24) DEFAULT NULL,
  `user_email` varchar(500) DEFAULT NULL,
  `package_id` int DEFAULT NULL,
  `hotel_id` int DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_status` varchar(30) NOT NULL DEFAULT 'unpaid',
  `customer_note` text,
  `trip_category` varchar(30) NOT NULL DEFAULT 'family',
  `admin_note` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `package_unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `base_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `offer_id` int DEFAULT NULL,
  `offer_name` varchar(160) DEFAULT NULL,
  `offer_code` varchar(50) DEFAULT NULL,
  `discount_type` varchar(20) DEFAULT NULL,
  `discount_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `hotel_surcharge_per_traveller` decimal(10,2) NOT NULL DEFAULT '0.00',
  `hotel_surcharge_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `meal_plan_id` int DEFAULT NULL,
  `meal_plan_name` varchar(80) NOT NULL DEFAULT 'Standard meals (included)',
  `meal_surcharge_per_traveller` decimal(10,2) NOT NULL DEFAULT '0.00',
  `meal_surcharge_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `trip_theme` varchar(30) NOT NULL DEFAULT 'leisure',
  `customer_hidden_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_reference_unique` (`booking_reference`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,'2026-10-30','2026-11-02','Ahmedabad','Premium accommodation',4,'Srija Biswas','2026-10-01 10:22:41','TI-261001-56D1E5','srijabiswas3001@gmail.com',21,2,'in_progress',45996.00,'paid','','mixed','Payment received in full.','2026-10-10 10:30:19',8999.00,45996.00,NULL,NULL,NULL,NULL,0.00,0.00,2500.00,10000.00,NULL,'Standard meals (included)',0.00,0.00,'leisure',NULL),(2,'2026-11-01','2026-11-04','Darjeeling','Premium accommodation',3,'Srija Biswas','2026-10-01 12:02:59','TI-261001-6C6C18','srijabiswas3001@gmail.com',57,4,'cancelled',33597.00,'refunded','','family','Cancellation approved. Refund is pending.','2026-10-12 06:26:40',8999.00,33597.00,NULL,NULL,NULL,NULL,0.00,0.00,2200.00,6600.00,NULL,'Standard meals (included)',0.00,0.00,'leisure','2026-10-12 06:26:40'),(3,'2026-10-10','2026-10-13','Darjeeling','Standard accommodation (included)',3,'Srija Biswas','2026-10-05 06:34:50','TI-261005-D7739E','srijabiswas3001@gmail.com',57,3,'cancelled',24997.00,'unpaid','','family','Ok','2026-10-12 06:26:37',8999.00,26997.00,1,'DIWALI2026_DEMO_10OFF','DIWALI10','percentage',10.00,2000.00,0.00,0.00,NULL,'Standard meals (included)',0.00,0.00,'leisure','2026-10-12 06:26:37');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cancellation_policies`
--

DROP TABLE IF EXISTS `cancellation_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cancellation_policies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `minimum_days_before` int NOT NULL,
  `refund_percentage` decimal(5,2) NOT NULL,
  `administrative_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `minimum_days_before` (`minimum_days_before`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cancellation_policies`
--

LOCK TABLES `cancellation_policies` WRITE;
/*!40000 ALTER TABLE `cancellation_policies` DISABLE KEYS */;
INSERT INTO `cancellation_policies` VALUES (1,'15+ days before departure',15,90.00,0.00,'active','2026-01-01',NULL,'2026-10-05 12:44:13'),(2,'8-14 days before departure',8,75.00,0.00,'active','2026-01-01',NULL,'2026-10-05 12:44:13'),(3,'3-7 days before departure',3,50.00,0.00,'active','2026-01-01',NULL,'2026-10-05 12:44:13'),(4,'Less than 72 hours',0,0.00,0.00,'active','2026-01-01',NULL,'2026-10-05 12:44:13');
/*!40000 ALTER TABLE `cancellation_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cancellation_requests`
--

DROP TABLE IF EXISTS `cancellation_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cancellation_requests` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `reason` varchar(1000) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `policy_id` int DEFAULT NULL,
  `policy_name` varchar(150) NOT NULL,
  `refund_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `administrative_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estimated_refund` decimal(10,2) NOT NULL DEFAULT '0.00',
  `approved_refund` decimal(10,2) DEFAULT NULL,
  `admin_response` varchar(1000) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'requested',
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cancellation_requests`
--

LOCK TABLES `cancellation_requests` WRITE;
/*!40000 ALTER TABLE `cancellation_requests` DISABLE KEYS */;
INSERT INTO `cancellation_requests` VALUES (2,2,'adadadad',33597.00,1,'15+ days before departure',90.00,0.00,30237.30,30237.30,'','refunded','2026-10-12 06:07:01','2026-10-12 06:14:12',1);
/*!40000 ALTER TABLE `cancellation_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `iso_code` varchar(3) NOT NULL,
  `currency_code` char(3) NOT NULL DEFAULT 'INR',
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `iso_code` (`iso_code`)
) ENGINE=InnoDB AUTO_INCREMENT=301 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (1,'India','IND','INR',NULL,'active','2026-09-26 12:36:22');
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destinations`
--

DROP TABLE IF EXISTS `destinations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `destinations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `description` text,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `state_id` (`state_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15805 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destinations`
--

LOCK TABLES `destinations` WRITE;
/*!40000 ALTER TABLE `destinations` DISABLE KEYS */;
INSERT INTO `destinations` VALUES (1,1,'Port Blair','andaman-and-nicobar-islands-port-blair','Popular tourism destination in Andaman and Nicobar Islands.','active','2026-09-26 11:27:26'),(2,2,'Visakhapatnam','andhra-pradesh-visakhapatnam','Popular tourism destination in Andhra Pradesh.','active','2026-09-26 11:27:26'),(3,3,'Tawang','arunachal-pradesh-tawang','Popular tourism destination in Arunachal Pradesh.','active','2026-09-26 11:27:26'),(4,4,'Kaziranga','assam-kaziranga','Popular tourism destination in Assam.','active','2026-09-26 11:27:26'),(5,5,'Bodh Gaya','bihar-bodh-gaya','Popular tourism destination in Bihar.','active','2026-09-26 11:27:26'),(6,6,'Chandigarh City','chandigarh-chandigarh-city','Popular tourism destination in Chandigarh.','active','2026-09-26 11:27:26'),(7,7,'Jagdalpur','chhattisgarh-jagdalpur','Popular tourism destination in Chhattisgarh.','active','2026-09-26 11:27:26'),(8,8,'Daman','dadra-and-nagar-haveli-and-daman-and-diu-daman','Popular tourism destination in Dadra and Nagar Haveli and Daman and Diu.','active','2026-09-26 11:27:26'),(9,9,'New Delhi','delhi-new-delhi','Popular tourism destination in Delhi.','active','2026-09-26 11:27:26'),(10,10,'Panaji','goa-panaji','Popular tourism destination in Goa.','active','2026-09-26 11:27:26'),(11,11,'Ahmedabad','gujarat-ahmedabad','Popular tourism destination in Gujarat.','active','2026-09-26 11:27:26'),(12,12,'Kurukshetra','haryana-kurukshetra','Popular tourism destination in Haryana.','active','2026-09-26 11:27:26'),(13,13,'Manali','himachal-pradesh-manali','Popular tourism destination in Himachal Pradesh.','active','2026-09-26 11:27:26'),(14,14,'Srinagar','jammu-and-kashmir-srinagar','Popular tourism destination in Jammu and Kashmir.','active','2026-09-26 11:27:26'),(15,15,'Ranchi','jharkhand-ranchi','Popular tourism destination in Jharkhand.','active','2026-09-26 11:27:26'),(16,16,'Mysuru','karnataka-mysuru','Popular tourism destination in Karnataka.','active','2026-09-26 11:27:26'),(17,17,'Munnar','kerala-munnar','Popular tourism destination in Kerala.','active','2026-09-26 11:27:26'),(18,18,'Leh','ladakh-leh','Popular tourism destination in Ladakh.','active','2026-09-26 11:27:26'),(19,19,'Agatti','lakshadweep-agatti','Popular tourism destination in Lakshadweep.','active','2026-09-26 11:27:26'),(20,20,'Khajuraho','madhya-pradesh-khajuraho','Popular tourism destination in Madhya Pradesh.','active','2026-09-26 11:27:26'),(21,21,'Mumbai','maharashtra-mumbai','Popular tourism destination in Maharashtra.','active','2026-09-26 11:27:26'),(22,22,'Imphal','manipur-imphal','Popular tourism destination in Manipur.','active','2026-09-26 11:27:26'),(23,23,'Shillong','meghalaya-shillong','Popular tourism destination in Meghalaya.','active','2026-09-26 11:27:26'),(24,24,'Aizawl','mizoram-aizawl','Popular tourism destination in Mizoram.','active','2026-09-26 11:27:26'),(25,25,'Kohima','nagaland-kohima','Popular tourism destination in Nagaland.','active','2026-09-26 11:27:26'),(26,26,'Puri','odisha-puri','Popular tourism destination in Odisha.','active','2026-09-26 11:27:26'),(27,27,'Puducherry City','puducherry-puducherry-city','Popular tourism destination in Puducherry.','active','2026-09-26 11:27:26'),(28,28,'Amritsar','punjab-amritsar','Popular tourism destination in Punjab.','active','2026-09-26 11:27:26'),(29,29,'Jaipur','rajasthan-jaipur','Popular tourism destination in Rajasthan.','active','2026-09-26 11:27:26'),(30,30,'Gangtok','sikkim-gangtok','Popular tourism destination in Sikkim.','active','2026-09-26 11:27:26'),(31,31,'Madurai','tamil-nadu-madurai','Popular tourism destination in Tamil Nadu.','active','2026-09-26 11:27:26'),(32,32,'Hyderabad','telangana-hyderabad','Popular tourism destination in Telangana.','active','2026-09-26 11:27:26'),(33,33,'Agartala','tripura-agartala','Popular tourism destination in Tripura.','active','2026-09-26 11:27:26'),(34,34,'Agra','uttar-pradesh-agra','Popular tourism destination in Uttar Pradesh.','active','2026-09-26 11:27:26'),(35,35,'Haridwar','uttarakhand-haridwar','Popular tourism destination in Uttarakhand.','active','2026-09-26 11:27:26'),(36,36,'Darjeeling','west-bengal-darjeeling','Popular tourism destination in West Bengal.','active','2026-09-26 11:27:26');
/*!40000 ALTER TABLE `destinations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_members`
--

DROP TABLE IF EXISTS `family_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_email` varchar(500) NOT NULL,
  `name` varchar(150) NOT NULL,
  `relationship` varchar(80) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `medical_issues` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_pwd` tinyint(1) NOT NULL DEFAULT '0',
  `pwd_details` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_members`
--

LOCK TABLES `family_members` WRITE;
/*!40000 ALTER TABLE `family_members` DISABLE KEYS */;
INSERT INTO `family_members` VALUES (1,'srijabiswas3001@gmail.com','Soma Biswas','Mother','1976-01-01','Female','','',1,'2026-09-29 05:13:10',0,NULL),(2,'srijabiswas3001@gmail.com','Jayanta Biswas','Father','1975-04-04','Male','','',1,'2026-09-29 11:30:35',0,NULL);
/*!40000 ALTER TABLE `family_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotels`
--

DROP TABLE IF EXISTS `hotels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `destination_id` int DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'Standard',
  `address` varchar(500) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upgrade_surcharge` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hotel_destination_name` (`destination_id`,`name`),
  KEY `destination_id` (`destination_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotels`
--

LOCK TABLES `hotels` WRITE;
/*!40000 ALTER TABLE `hotels` DISABLE KEYS */;
INSERT INTO `hotels` VALUES (2,11,'Premium accommodation #2','Premium',NULL,'','active','2026-10-01 07:45:37','2026-10-10 10:30:18',2500.00),(3,36,'Standard accommodation #3','Standard',NULL,'','active','2026-10-01 12:01:25','2026-10-10 10:30:18',0.00),(4,36,'Premium accommodation #4','Premium',NULL,'','active','2026-10-01 12:02:22','2026-10-10 10:30:18',2200.00),(5,NULL,'Premium accommodation #5','Premium',NULL,'','active','2026-10-10 10:44:09','2026-10-10 10:46:20',3000.00);
/*!40000 ALTER TABLE `hotels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `portal` varchar(20) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `identifier` (`identifier`,`portal`,`attempted_at`),
  KEY `ip_address` (`ip_address`,`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES (1,'srijabiswas3001@gmail.com','admin','::1','2026-09-30 05:14:16');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meal_plans`
--

DROP TABLE IF EXISTS `meal_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meal_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `destination_id` int DEFAULT NULL,
  `name` varchar(80) NOT NULL,
  `plan_type` varchar(20) NOT NULL DEFAULT 'premium',
  `included_meals` varchar(255) NOT NULL DEFAULT 'Breakfast, lunch and dinner',
  `description` text,
  `surcharge_per_traveller` decimal(10,2) NOT NULL DEFAULT '0.00',
  `supports_veg` tinyint(1) NOT NULL DEFAULT '1',
  `supports_nonveg` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `destination_id` (`destination_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meal_plans`
--

LOCK TABLES `meal_plans` WRITE;
/*!40000 ALTER TABLE `meal_plans` DISABLE KEYS */;
INSERT INTO `meal_plans` VALUES (1,NULL,'Standard meals','standard','Breakfast, lunch and dinner','Standard vegetarian or non-vegetarian meals included with the package',0.00,1,1,'active','2026-10-10 10:30:18','2026-10-10 10:30:33'),(2,NULL,'Premium meals','premium','Breakfast, lunch and dinner','',3500.00,1,1,'active','2026-10-10 10:44:55',NULL);
/*!40000 ALTER TABLE `meal_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(500) NOT NULL,
  `fname` varchar(500) NOT NULL,
  `lname` varchar(500) NOT NULL,
  `sub` varchar(500) NOT NULL,
  `message` varchar(10000) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'open',
  `admin_response` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
INSERT INTO `message` VALUES (3,'soma01@gmail.com','Soma','Biswas','Enquiry','Please send me north India package list details','open',NULL,'2026-09-26 11:01:40',NULL);
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_reads`
--

DROP TABLE IF EXISTS `notification_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_reads` (
  `notification_id` bigint NOT NULL,
  `user_email` varchar(500) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`,`user_email`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_reads`
--

LOCK TABLES `notification_reads` WRITE;
/*!40000 ALTER TABLE `notification_reads` DISABLE KEYS */;
INSERT INTO `notification_reads` VALUES (1,'srijabiswas3001@gmail.com','2026-10-01 10:26:42'),(2,'srijabiswas3001@gmail.com','2026-10-01 10:38:02'),(3,'srijabiswas3001@gmail.com','2026-10-01 10:48:39'),(4,'srijabiswas3001@gmail.com','2026-10-01 10:49:05'),(5,'srijabiswas3001@gmail.com','2026-10-01 10:49:05'),(6,'srijabiswas3001@gmail.com','2026-10-01 10:53:42'),(7,'srijabiswas3001@gmail.com','2026-10-01 10:59:20'),(8,'srijabiswas3001@gmail.com','2026-10-01 11:00:09'),(9,'srijabiswas3001@gmail.com','2026-10-01 12:03:06'),(10,'srijabiswas3001@gmail.com','2026-10-01 12:04:37'),(11,'srijabiswas3001@gmail.com','2026-10-01 12:04:37'),(12,'srijabiswas3001@gmail.com','2026-10-05 05:58:08'),(13,'srijabiswas3001@gmail.com','2026-10-05 05:58:08'),(14,'srijabiswas3001@gmail.com','2026-10-05 05:58:08'),(15,'srijabiswas3001@gmail.com','2026-10-05 06:35:32'),(16,'admin:srija@gmail.com','2026-10-08 05:55:38'),(17,'srijabiswas3001@gmail.com','2026-10-08 11:06:28'),(18,'admin:srija@gmail.com','2026-10-08 12:08:20'),(19,'srijabiswas3001@gmail.com','2026-10-08 11:06:28'),(20,'srijabiswas3001@gmail.com','2026-10-09 12:43:32'),(21,'admin:srija@gmail.com','2026-10-10 05:49:35'),(22,'srijabiswas3001@gmail.com','2026-10-12 06:26:45'),(23,'admin:srija@gmail.com','2026-10-12 06:27:10'),(24,'srijabiswas3001@gmail.com','2026-10-12 06:26:45'),(25,'srijabiswas3001@gmail.com','2026-10-12 06:26:45');
/*!40000 ALTER TABLE `notification_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `audience` varchar(20) NOT NULL DEFAULT 'public',
  `user_email` varchar(500) DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'info',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audience` (`audience`),
  KEY `user_email` (`user_email`),
  KEY `is_active` (`is_active`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'user','srijabiswas3001@gmail.com','Booking request received','Your booking TI-261001-56D1E5 has been received and is awaiting review.','my-bookings.php','success',1,NULL,NULL,NULL,'2026-10-01 10:22:41'),(2,'user','srijabiswas3001@gmail.com','Booking status updated','Your booking TI-261001-56D1E5 is now needs information. Need Information of the specific things','my-bookings.php','warning',1,NULL,NULL,1,'2026-10-01 10:37:55'),(3,'user','srijabiswas3001@gmail.com','Booking update received','Your updated booking TI-261001-56D1E5 has been sent for review.','my-bookings.php#booking-1','success',1,NULL,NULL,NULL,'2026-10-01 10:48:16'),(4,'user','srijabiswas3001@gmail.com','Booking status updated','Your booking TI-261001-56D1E5 is now needs information. Update teh hotels','booking.php?edit_booking=1','warning',1,NULL,NULL,1,'2026-10-01 10:48:35'),(5,'user','srijabiswas3001@gmail.com','Booking update received','Your updated booking TI-261001-56D1E5 has been sent for review.','my-bookings.php#booking-1','success',1,NULL,NULL,NULL,'2026-10-01 10:48:54'),(6,'user','srijabiswas3001@gmail.com','Booking confirmed','Your booking TI-261001-56D1E5 is now confirmed. please pay the amount','my-bookings.php#booking-1','success',1,NULL,NULL,1,'2026-10-01 10:49:36'),(7,'user','srijabiswas3001@gmail.com','Demo payment recorded','Demo payment DEMO-261001105910-F8DD44 was marked successful for booking TI-261001-56D1E5. No money was charged.','payment.php?booking_id=1','success',1,NULL,NULL,NULL,'2026-10-01 10:59:10'),(8,'user','srijabiswas3001@gmail.com','Booking status updated','Your booking TI-261001-56D1E5 is now in progress. please pay the amount','my-bookings.php#booking-1','info',1,NULL,NULL,1,'2026-10-01 10:59:42'),(9,'user','srijabiswas3001@gmail.com','Booking request received','Your booking TI-261001-6C6C18 has been received and is awaiting review.','my-bookings.php#booking-2','success',1,NULL,NULL,NULL,'2026-10-01 12:03:00'),(10,'user','srijabiswas3001@gmail.com','Booking status updated','Your booking TI-261001-6C6C18 is now needs information. Details of the family','booking.php?edit_booking=2','warning',1,NULL,NULL,1,'2026-10-01 12:03:52'),(11,'user','srijabiswas3001@gmail.com','Booking update received','Your updated booking TI-261001-6C6C18 has been sent for review.','my-bookings.php#booking-2','success',1,NULL,NULL,NULL,'2026-10-01 12:04:23'),(12,'user','srijabiswas3001@gmail.com','Booking confirmed','Your booking TI-261001-6C6C18 is now confirmed. Pay the amount','my-bookings.php#booking-2','success',1,NULL,NULL,1,'2026-10-01 12:04:49'),(13,'user','srijabiswas3001@gmail.com','Booking in progress','Your confirmed booking is now in progress after payment was recorded.','my-bookings.php#booking-2','success',1,NULL,NULL,NULL,'2026-10-01 12:23:50'),(14,'user','srijabiswas3001@gmail.com','Payment recorded','Payment reference TI-PAY-261001122350-0B8887 was marked successful for booking TI-261001-6C6C18. No money was charged.','payment.php?booking_id=2','success',1,NULL,NULL,NULL,'2026-10-01 12:23:50'),(15,'user','srijabiswas3001@gmail.com','Booking request received','Your booking TI-261005-D7739E has been received and is awaiting review.','my-bookings.php#booking-3','success',1,NULL,NULL,NULL,'2026-10-05 06:34:50'),(16,'admin',NULL,'New booking request','Booking TI-261005-D7739E from Srija Biswas is waiting for admin review.','admin/bookings.php','info',1,NULL,NULL,NULL,'2026-10-05 06:34:50'),(17,'user','srijabiswas3001@gmail.com','Cancellation request received','Your cancellation request for booking TI-261005-D7739E is awaiting review. Estimated refund: ₹0.','my-bookings.php#booking-3','info',1,NULL,NULL,NULL,'2026-10-08 11:05:22'),(18,'admin',NULL,'Cancellation request','Booking TI-261005-D7739E requires administrator review.','admin/booking-details.php?id=3','warning',1,NULL,NULL,NULL,'2026-10-08 11:05:22'),(19,'user','srijabiswas3001@gmail.com','Booking status updated','Your booking TI-261005-D7739E is now cancelled. Ok','my-bookings.php#booking-3','info',1,NULL,NULL,1,'2026-10-08 11:06:17'),(20,'user','srijabiswas3001@gmail.com','Booking request received','Your booking TI-261009-65969A has been received and is awaiting review.','my-bookings.php#booking-15','success',1,NULL,NULL,NULL,'2026-10-09 12:42:26'),(21,'admin',NULL,'New booking request','Booking TI-261009-65969A from Srija Biswas is waiting for admin review.','admin/bookings.php','info',1,NULL,NULL,NULL,'2026-10-09 12:42:26'),(22,'user','srijabiswas3001@gmail.com','Cancellation request received','Your cancellation request for booking TI-261001-6C6C18 is awaiting review. Estimated refund: ₹30,237.30.','my-bookings.php#booking-2','info',1,NULL,NULL,NULL,'2026-10-12 06:07:01'),(23,'admin',NULL,'Cancellation request','Booking TI-261001-6C6C18 requires administrator review.','admin/booking-details.php?id=2','warning',1,NULL,NULL,NULL,'2026-10-12 06:07:01'),(24,'user','srijabiswas3001@gmail.com','Cancellation approved','Booking TI-261001-6C6C18 has been cancelled. Approved refund: ₹30,237.30. Refund is pending.','my-bookings.php#booking-2','success',1,NULL,NULL,1,'2026-10-12 06:14:12'),(25,'user','srijabiswas3001@gmail.com','Payment status updated','Payment for booking TI-261001-6C6C18 is now refunded.','my-bookings.php#booking-2','info',1,NULL,NULL,1,'2026-10-12 06:15:30');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_packages`
--

DROP TABLE IF EXISTS `offer_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offer_packages` (
  `offer_id` int NOT NULL,
  `package_id` int NOT NULL,
  PRIMARY KEY (`offer_id`,`package_id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_packages`
--

LOCK TABLES `offer_packages` WRITE;
/*!40000 ALTER TABLE `offer_packages` DISABLE KEYS */;
INSERT INTO `offer_packages` VALUES (1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,27),(1,33),(1,49),(1,51),(1,57);
/*!40000 ALTER TABLE `offer_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_redemptions`
--

DROP TABLE IF EXISTS `offer_redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offer_redemptions` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `offer_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `user_email` varchar(500) NOT NULL,
  `base_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `final_amount` decimal(10,2) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_offer` (`booking_id`,`offer_id`),
  KEY `offer_id` (`offer_id`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_redemptions`
--

LOCK TABLES `offer_redemptions` WRITE;
/*!40000 ALTER TABLE `offer_redemptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `offer_redemptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `public_heading` varchar(200) NOT NULL,
  `description` text,
  `terms` text,
  `image` varchar(255) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `discount_type` varchar(20) NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `calculation_scope` varchar(30) NOT NULL DEFAULT 'booking_total',
  `minimum_travellers` int NOT NULL DEFAULT '1',
  `minimum_booking_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `maximum_discount` decimal(10,2) DEFAULT NULL,
  `booking_start_at` datetime NOT NULL,
  `booking_end_at` datetime NOT NULL,
  `usage_limit` int DEFAULT NULL,
  `per_customer_limit` int DEFAULT NULL,
  `is_automatic` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `code` (`code`),
  KEY `status` (`status`),
  KEY `booking_start_at` (`booking_start_at`),
  KEY `booking_end_at` (`booking_end_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offers`
--

LOCK TABLES `offers` WRITE;
/*!40000 ALTER TABLE `offers` DISABLE KEYS */;
INSERT INTO `offers` VALUES (1,'DIWALI2026_DEMO_10OFF','diwali2026-demo-10off','Diwali Travel Special – Get 10% Off','Celebrate Diwali with a special 10% discount on selected India tour packages. Book your next adventure and save instantly.','Valid on selected packages only. 10% discount applies to the booking total, subject to a maximum discount of ₹2,000 per booking. Minimum booking value ₹5,000. Offer cannot be combined with other promotional discounts. One use per customer. Offer valid during the specified promotional period','uploads/offers/b7badd9c978daa8c43660ad1467c65eb0b8751dd6cd6f4f03efebca461e458c9.png','DIWALI10','percentage',10.00,'booking_total',1,5000.00,2000.00,'2026-10-05 12:00:00','2026-11-15 12:00:00',100,1,1,1,10,'active',1,'2026-10-05 06:13:37','2026-10-05 06:14:09');
/*!40000 ALTER TABLE `offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state_id` int DEFAULT NULL,
  `destination_id` int DEFAULT NULL,
  `transport_category_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `destination` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `visit_places` text,
  `image` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'INR',
  `duration_days` int NOT NULL DEFAULT '3',
  `hotel_rating` tinyint NOT NULL DEFAULT '3',
  `transport` varchar(255) NOT NULL DEFAULT 'Air-conditioned transport',
  `status` varchar(30) NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
INSERT INTO `packages` VALUES (1,34,34,1,'Agra','agra','Agra','Explore the Taj Mahal, Agra Fort, and the timeless heritage along the Yamuna River.','Taj Mahal, Agra Fort, Mehtab Bagh, Itmad-ud-Daulah Tomb, Akbar Tomb, Fatehpur Sikri','img/Agra.jpg',8999.00,'INR',3,4,'Private car and local transfers','published','2026-09-26 11:01:40','2026-10-12 05:48:08'),(2,28,28,1,'Amritsar','amritsar','Amritsar','Experience the Golden Temple, historic old city, and the moving Wagah border ceremony.','Golden Temple, Jallianwala Bagh, Wagah Border, Partition Museum, Durgiana Temple, Gobindgarh Fort','img/Amritsar.jpg',10999.00,'INR',4,4,'Train and air-conditioned coach','published','2026-09-26 11:01:40','2026-10-12 05:48:08'),(3,13,NULL,1,'Dalhousie','dalhousie','Dalhousie','Relax among pine-covered hills, colonial architecture, and panoramic Dhauladhar views.','Khajjiar, Dainkund Peak, Panchpula, Kalatop Wildlife Sanctuary, St John Church, Subhash Baoli','img/Dalhousie.jpg',7499.00,'INR',3,3,'Air-conditioned coach','published','2026-09-26 11:01:40','2026-10-12 05:48:08'),(4,13,NULL,1,'Dharamshala','dharamshala','Dharamshala','Discover cedar forests, Tibetan culture, monasteries, and Himalayan mountain scenery.','McLeod Ganj, Dalai Lama Temple, Bhagsu Waterfall, Namgyal Monastery, Dal Lake, Triund','img/Dharamshala.jpg',9999.00,'INR',4,4,'Private car and local transfers','published','2026-09-26 11:01:40','2026-10-12 05:48:08'),(5,35,35,1,'Haridwar','haridwar','Haridwar','Visit sacred ghats, witness the evening Ganga Aarti, and explore the Himalayan foothills.','Har Ki Pauri, Mansa Devi Temple, Chandi Devi Temple, Daksh Mahadev Temple, Bharat Mata Mandir, Shantikunj','img/Haridwar.jpg',6999.00,'INR',3,3,'Train and local transfers','published','2026-09-26 11:01:40','2026-10-12 05:48:08'),(6,13,NULL,1,'Kasauli','kasauli','Kasauli','Enjoy quiet colonial lanes, orchards, forest walks, and sweeping hill views.','Monkey Point, Gilbert Trail, Sunset Point, Christ Church, Mall Road, Kasauli Brewery','img/Kasauli.jpg',7999.00,'INR',3,3,'Private car','published','2026-09-26 11:01:40','2026-10-12 05:48:08'),(7,13,13,1,'Manali','manali','Manali','A Himalayan escape with valleys, adventure activities, temples, and mountain passes.','Hadimba Temple, Solang Valley, Rohtang Pass, Manu Temple, Vashisht Hot Springs, Mall Road','img/Manali.jpg',14999.00,'INR',5,4,'Volvo coach and local transfers','published','2026-09-26 11:01:40','2026-10-12 05:48:08'),(8,34,NULL,1,'Mathura','mathura','Mathura','Explore the birthplace of Lord Krishna, ancient temples, ghats, and nearby Vrindavan.','Krishna Janmabhoomi, Dwarkadhish Temple, Vishram Ghat, Govardhan Hill, Prem Mandir, Banke Bihari Temple','img/Mathura.jpg',6499.00,'INR',3,3,'Private car and local transfers','published','2026-09-26 11:01:40','2026-10-12 05:48:08'),(9,21,21,1,'Mumbai','mumbai','Mumbai','See the Gateway of India, Marine Drive, heritage districts, and vibrant city life.','Gateway of India, Marine Drive, Chhatrapati Shivaji Terminus, Elephanta Caves, Colaba Causeway, Siddhivinayak Temple','img/Mumbai.jpg',12999.00,'INR',4,4,'Airport and city transfers','published','2026-09-26 11:01:40','2026-10-12 05:48:08'),(10,NULL,NULL,1,'Sydney','sydney','Sydney','Discover Sydney Harbour, the Opera House, coastal walks, and lively waterfront districts.',NULL,'img/Sydney.jpg',499.00,'INR',6,4,'Airport transfer and city coach','archived','2026-09-26 11:01:40','2026-09-28 10:17:56'),(11,1,1,1,'Port Blair Discovery','andaman-nicobar-discovery','Port Blair','Discover the culture, heritage, landscapes, and local experiences of Port Blair, Andaman and Nicobar Islands. This curated India tour includes comfortable stays and guided sightseeing.','Cellular Jail, Corbyn Cove Beach, Ross Island, North Bay Island, Samudrika Museum, Chidiya Tapu','img/1.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(12,2,2,1,'Visakhapatnam Discovery','andhra-pradesh-discovery','Visakhapatnam','Discover the culture, heritage, landscapes, and local experiences of Visakhapatnam, Andhra Pradesh. This curated India tour includes comfortable stays and guided sightseeing.','RK Beach, Kailasagiri, INS Kursura Museum, Simhachalam Temple, Rushikonda Beach, Borra Caves','img/2.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(13,3,3,1,'Tawang Discovery','arunachal-pradesh-discovery','Tawang','Discover the culture, heritage, landscapes, and local experiences of Tawang, Arunachal Pradesh. This curated India tour includes comfortable stays and guided sightseeing.','Tawang Monastery, Sela Pass, Madhuri Lake, Tawang War Memorial, Nuranang Falls, Bum La Pass','img/3.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(14,4,4,1,'Kaziranga Discovery','assam-discovery','Kaziranga','Discover the culture, heritage, landscapes, and local experiences of Kaziranga, Assam. This curated India tour includes comfortable stays and guided sightseeing.','Kaziranga National Park, Kohora Range, Bagori Range, Orchid Park, Kakochang Waterfalls, Tea Gardens','img/4.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(15,5,5,1,'Bodh Gaya Discovery','bihar-discovery','Bodh Gaya','Discover the culture, heritage, landscapes, and local experiences of Bodh Gaya, Bihar. This curated India tour includes comfortable stays and guided sightseeing.','Mahabodhi Temple, Bodhi Tree, Great Buddha Statue, Thai Monastery, Royal Bhutan Monastery, Archaeological Museum','img/5.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(16,6,6,1,'Chandigarh City Discovery','chandigarh-discovery','Chandigarh City','Discover the culture, heritage, landscapes, and local experiences of Chandigarh City, Chandigarh. This curated India tour includes comfortable stays and guided sightseeing.','Rock Garden, Sukhna Lake, Rose Garden, Capitol Complex, Government Museum, Japanese Garden','img/6.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(17,7,7,1,'Jagdalpur Discovery','chhattisgarh-discovery','Jagdalpur','Discover the culture, heritage, landscapes, and local experiences of Jagdalpur, Chhattisgarh. This curated India tour includes comfortable stays and guided sightseeing.','Chitrakote Falls, Tirathgarh Falls, Kanger Valley National Park, Kutumsar Caves, Danteshwari Temple, Anthropological Museum','img/7.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(18,8,8,1,'Daman Discovery','dadra-nagar-haveli-daman-diu-discovery','Daman','Discover the culture, heritage, landscapes, and local experiences of Daman, Dadra and Nagar Haveli and Daman and Diu. This curated India tour includes comfortable stays and guided sightseeing.','Devka Beach, Jampore Beach, Moti Daman Fort, Nani Daman Fort, Bom Jesus Church, Daman Lighthouse','img/9.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(19,9,9,1,'New Delhi Discovery','delhi-discovery','New Delhi','Discover the culture, heritage, landscapes, and local experiences of New Delhi, Delhi. This curated India tour includes comfortable stays and guided sightseeing.','India Gate, Red Fort, Qutub Minar, Humayun Tomb, Lotus Temple, Raj Ghat, Akshardham Temple','img/10.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(20,10,10,1,'Panaji Discovery','goa-discovery','Panaji','Discover the culture, heritage, landscapes, and local experiences of Panaji, Goa. This curated India tour includes comfortable stays and guided sightseeing.','Fontainhas, Our Lady of Immaculate Conception Church, Miramar Beach, Dona Paula, Mandovi River, Reis Magos Fort','img/11.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(21,11,11,1,'Ahmedabad Discovery','gujarat-discovery','Ahmedabad','Discover the culture, heritage, landscapes, and local experiences of Ahmedabad, Gujarat. This curated India tour includes comfortable stays and guided sightseeing.','Sabarmati Ashram, Adalaj Stepwell, Sidi Saiyyed Mosque, Kankaria Lake, Calico Museum, Science City','img/12.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(22,12,12,1,'Kurukshetra Discovery','haryana-discovery','Kurukshetra','Discover the culture, heritage, landscapes, and local experiences of Kurukshetra, Haryana. This curated India tour includes comfortable stays and guided sightseeing.','Brahma Sarovar, Jyotisar, Krishna Museum, Sannihit Sarovar, Bhadrakali Temple, Sheikh Chilli Tomb','img/13.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(23,14,14,1,'Srinagar Discovery','jammu-kashmir-discovery','Srinagar','Discover the culture, heritage, landscapes, and local experiences of Srinagar, Jammu and Kashmir. This curated India tour includes comfortable stays and guided sightseeing.','Dal Lake, Mughal Gardens, Shankaracharya Temple, Hazratbal Shrine, Pari Mahal, Old City','img/15.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(24,15,15,1,'Ranchi Discovery','jharkhand-discovery','Ranchi','Discover the culture, heritage, landscapes, and local experiences of Ranchi, Jharkhand. This curated India tour includes comfortable stays and guided sightseeing.','Dassam Falls, Hundru Falls, Tagore Hill, Jagannath Temple, Rock Garden, Birsa Zoological Park','img/16.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(25,16,16,1,'Mysuru Discovery','karnataka-discovery','Mysuru','Discover the culture, heritage, landscapes, and local experiences of Mysuru, Karnataka. This curated India tour includes comfortable stays and guided sightseeing.','Mysore Palace, Chamundi Hills, Brindavan Gardens, St Philomena Cathedral, Mysore Zoo, Jaganmohan Palace','img/17.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(27,17,17,1,'Munnar Discovery','kerala-discovery','Munnar','Discover the culture, heritage, landscapes, and local experiences of Munnar, Kerala. This curated India tour includes comfortable stays and guided sightseeing.','Eravikulam National Park, Mattupetty Dam, Tea Museum, Top Station, Echo Point, Kundala Lake','img/18.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(29,18,18,1,'Leh Discovery','ladakh-discovery','Leh','Discover the culture, heritage, landscapes, and local experiences of Leh, Ladakh. This curated India tour includes comfortable stays and guided sightseeing.','Leh Palace, Shanti Stupa, Thiksey Monastery, Magnetic Hill, Hall of Fame, Pangong Lake','img/30.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(31,19,19,1,'Agatti Discovery','lakshadweep-discovery','Agatti','Discover the culture, heritage, landscapes, and local experiences of Agatti, Lakshadweep. This curated India tour includes comfortable stays and guided sightseeing.','Agatti Beach, Agatti Lagoon, Bangaram Island, Thinnakara Island, Kalpitti Island, Marine Museum','img/19.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(33,20,20,1,'Khajuraho Discovery','madhya-pradesh-discovery','Khajuraho','Discover the culture, heritage, landscapes, and local experiences of Khajuraho, Madhya Pradesh. This curated India tour includes comfortable stays and guided sightseeing.','Western Group of Temples, Kandariya Mahadeva Temple, Lakshmana Temple, Eastern Group of Temples, Archaeological Museum, Raneh Falls','img/20.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(35,22,22,1,'Imphal Discovery','manipur-discovery','Imphal','Discover the culture, heritage, landscapes, and local experiences of Imphal, Manipur. This curated India tour includes comfortable stays and guided sightseeing.','Kangla Fort, Loktak Lake, Ima Keithel, Shri Govindajee Temple, INA Museum, Keibul Lamjao National Park','img/22.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(37,23,23,1,'Shillong Discovery','meghalaya-discovery','Shillong','Discover the culture, heritage, landscapes, and local experiences of Shillong, Meghalaya. This curated India tour includes comfortable stays and guided sightseeing.','Umiam Lake, Elephant Falls, Shillong Peak, Ward Lake, Don Bosco Museum, Laitlum Canyons','img/23.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(39,24,24,1,'Aizawl Discovery','mizoram-discovery','Aizawl','Discover the culture, heritage, landscapes, and local experiences of Aizawl, Mizoram. This curated India tour includes comfortable stays and guided sightseeing.','Solomon Temple, Mizoram State Museum, Durtlang Hills, Reiek Tlang, KV Paradise, Falkawn Village','img/24.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(41,25,25,1,'Kohima Discovery','nagaland-discovery','Kohima','Discover the culture, heritage, landscapes, and local experiences of Kohima, Nagaland. This curated India tour includes comfortable stays and guided sightseeing.','Kohima War Cemetery, Kisama Heritage Village, Dzukou Valley, Nagaland State Museum, Japfu Peak, Cathedral of Mary Help of Christians','img/25.png',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(43,26,26,1,'Puri Discovery','odisha-discovery','Puri','Discover the culture, heritage, landscapes, and local experiences of Puri, Odisha. This curated India tour includes comfortable stays and guided sightseeing.','Jagannath Temple, Puri Beach, Gundicha Temple, Narendra Tank, Raghurajpur Village, Chilika Lake','img/26.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(45,27,27,1,'Puducherry City Discovery','puducherry-discovery','Puducherry City','Discover the culture, heritage, landscapes, and local experiences of Puducherry City, Puducherry. This curated India tour includes comfortable stays and guided sightseeing.','Promenade Beach, Sri Aurobindo Ashram, Auroville, French Quarter, Paradise Beach, Basilica of the Sacred Heart','img/27.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(47,29,29,1,'Jaipur Discovery','rajasthan-discovery','Jaipur','Discover the culture, heritage, landscapes, and local experiences of Jaipur, Rajasthan. This curated India tour includes comfortable stays and guided sightseeing.','Amber Fort, City Palace, Hawa Mahal, Jantar Mantar, Jal Mahal, Nahargarh Fort','img/29.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(49,30,30,1,'Gangtok Discovery','sikkim-discovery','Gangtok','Discover the culture, heritage, landscapes, and local experiences of Gangtok, Sikkim. This curated India tour includes comfortable stays and guided sightseeing.','MG Marg, Rumtek Monastery, Tsomgo Lake, Nathula Pass, Tashi View Point, Namgyal Institute of Tibetology','img/30.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(51,31,31,1,'Madurai Discovery','tamil-nadu-discovery','Madurai','Discover the culture, heritage, landscapes, and local experiences of Madurai, Tamil Nadu. This curated India tour includes comfortable stays and guided sightseeing.','Meenakshi Amman Temple, Thirumalai Nayakkar Palace, Gandhi Memorial Museum, Vandiyur Mariamman Teppakulam, Alagar Kovil, Pazhamudhir Solai','img/31.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(53,32,32,1,'Hyderabad Discovery','telangana-discovery','Hyderabad','Discover the culture, heritage, landscapes, and local experiences of Hyderabad, Telangana. This curated India tour includes comfortable stays and guided sightseeing.','Charminar, Golconda Fort, Salar Jung Museum, Hussain Sagar, Chowmahalla Palace, Qutb Shahi Tombs','img/32.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(55,33,33,1,'Agartala Discovery','tripura-discovery','Agartala','Discover the culture, heritage, landscapes, and local experiences of Agartala, Tripura. This curated India tour includes comfortable stays and guided sightseeing.','Ujjayanta Palace, Neermahal, Tripura Sundari Temple, Heritage Park, Sepahijala Wildlife Sanctuary, Chaturdasha Temple','img/33.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08'),(57,36,36,1,'Darjeeling Discovery','west-bengal-discovery','Darjeeling','Discover the culture, heritage, landscapes, and local experiences of Darjeeling, West Bengal. This curated India tour includes comfortable stays and guided sightseeing.','Tiger Hill, Batasia Loop, Himalayan Railway, Padmaja Naidu Zoo, Peace Pagoda, Tea Gardens','img/36.jpg',8999.00,'INR',4,3,'Air-conditioned local transport','published','2026-09-26 11:28:58','2026-10-12 05:48:08');
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) NOT NULL DEFAULT 'manual',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,45996.00,'demo_upi','DEMO-261001105910-F8DD44','paid','2026-10-01 10:59:10'),(2,2,33597.00,'practice_netbanking','TI-PAY-261001122350-0B8887','paid','2026-10-01 12:23:50'),(3,2,-30237.30,'admin_refund','REFUND-261012061530-98149E','refunded','2026-10-12 06:15:30');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `states`
--

DROP TABLE IF EXISTS `states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `country_id` int DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(130) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'state',
  `image` varchar(255) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=18001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `states`
--

LOCK TABLES `states` WRITE;
/*!40000 ALTER TABLE `states` DISABLE KEYS */;
INSERT INTO `states` VALUES (1,1,'Andaman and Nicobar Islands','andaman-nicobar','union_territory','img/1.jpg','active','2026-09-26 11:23:36'),(2,1,'Andhra Pradesh','andhra-pradesh','state','img/2.jpg','active','2026-09-26 11:23:36'),(3,1,'Arunachal Pradesh','arunachal-pradesh','state','img/3.jpg','active','2026-09-26 11:23:36'),(4,1,'Assam','assam','state','img/4.jpg','active','2026-09-26 11:23:36'),(5,1,'Bihar','bihar','state','img/5.jpg','active','2026-09-26 11:23:36'),(6,1,'Chandigarh','chandigarh','union_territory','img/6.jpg','active','2026-09-26 11:23:36'),(7,1,'Chhattisgarh','chhattisgarh','state','img/7.jpg','active','2026-09-26 11:23:36'),(8,1,'Dadra and Nagar Haveli and Daman and Diu','dadra-nagar-haveli-daman-diu','union_territory','img/9.jpg','active','2026-09-26 11:23:36'),(9,1,'Delhi','delhi','union_territory','img/10.jpg','active','2026-09-26 11:23:36'),(10,1,'Goa','goa-10','state','uploads/catalog/49647d02c199c343d456652864e9697b6390741115a4ee61f47cea9f7ab1a4fd.jpg','active','2026-09-26 11:23:36'),(11,1,'Gujarat','gujarat','state','img/12.jpg','active','2026-09-26 11:23:36'),(12,1,'Haryana','haryana','state','img/13.jpg','active','2026-09-26 11:23:37'),(13,1,'Himachal Pradesh','himachal-pradesh','state','img/14.jpg','active','2026-09-26 11:23:37'),(14,1,'Jammu and Kashmir','jammu-kashmir','union_territory','img/15.jpg','active','2026-09-26 11:23:37'),(15,1,'Jharkhand','jharkhand','state','img/16.jpg','active','2026-09-26 11:23:37'),(16,1,'Karnataka','karnataka','state','img/17.jpg','active','2026-09-26 11:23:37'),(17,1,'Kerala','kerala','state','img/18.jpg','active','2026-09-26 11:23:37'),(18,1,'Ladakh','ladakh','union_territory','img/30.jpg','active','2026-09-26 11:23:37'),(19,1,'Lakshadweep','lakshadweep','union_territory','img/19.jpg','active','2026-09-26 11:23:37'),(20,1,'Madhya Pradesh','madhya-pradesh','state','img/20.jpg','active','2026-09-26 11:23:37'),(21,1,'Maharashtra','maharashtra','state','img/21.jpg','active','2026-09-26 11:23:37'),(22,1,'Manipur','manipur','state','img/22.jpg','active','2026-09-26 11:23:37'),(23,1,'Meghalaya','meghalaya','state','img/23.jpg','active','2026-09-26 11:23:37'),(24,1,'Mizoram','mizoram','state','img/24.jpg','active','2026-09-26 11:23:37'),(25,1,'Nagaland','nagaland','state','img/25.png','active','2026-09-26 11:23:37'),(26,1,'Odisha','odisha','state','img/26.jpg','active','2026-09-26 11:23:37'),(27,1,'Puducherry','puducherry','union_territory','img/27.jpg','active','2026-09-26 11:23:37'),(28,1,'Punjab','punjab','state','img/28.jpg','active','2026-09-26 11:23:37'),(29,1,'Rajasthan','rajasthan','state','img/29.jpg','active','2026-09-26 11:23:37'),(30,1,'Sikkim','sikkim','state','img/30.jpg','active','2026-09-26 11:23:37'),(31,1,'Tamil Nadu','tamil-nadu','state','img/31.jpg','active','2026-09-26 11:23:37'),(32,1,'Telangana','telangana','state','img/32.jpg','active','2026-09-26 11:23:37'),(33,1,'Tripura','tripura','state','img/33.jpg','active','2026-09-26 11:23:37'),(34,1,'Uttar Pradesh','uttar-pradesh','state','img/34.jpg','active','2026-09-26 11:23:37'),(35,1,'Uttarakhand','uttarakhand','state','img/35.jpg','active','2026-09-26 11:23:37'),(36,1,'West Bengal','west-bengal','state','img/36.jpg','active','2026-09-26 11:23:37');
/*!40000 ALTER TABLE `states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transport_categories`
--

DROP TABLE IF EXISTS `transport_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transport_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=817 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transport_categories`
--

LOCK TABLES `transport_categories` WRITE;
/*!40000 ALTER TABLE `transport_categories` DISABLE KEYS */;
INSERT INTO `transport_categories` VALUES (1,'Air-conditioned transport','Comfortable air-conditioned road transport','active','2026-09-28 10:17:10'),(2,'Private car','Private point-to-point car service','active','2026-09-28 10:17:10'),(3,'Train and coach','Rail journey with local coach transfers','active','2026-09-28 10:17:10'),(4,'Flight and transfers','Flight with airport and local transfers','active','2026-09-28 10:17:10');
/*!40000 ALTER TABLE `transport_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trip_themes`
--

DROP TABLE IF EXISTS `trip_themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_themes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(110) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `display_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trip_themes`
--

LOCK TABLES `trip_themes` WRITE;
/*!40000 ALTER TABLE `trip_themes` DISABLE KEYS */;
INSERT INTO `trip_themes` VALUES (1,'Leisure','leisure','Relaxed sightseeing and recreation','active',10,'2026-10-10 10:46:20','2026-10-10 10:46:20'),(2,'Family holiday','family_holiday','Journeys planned for families','active',20,'2026-10-10 10:46:20','2026-10-10 10:46:20'),(3,'Couple','couple','Travel for couples','active',30,'2026-10-10 10:46:20','2026-10-10 10:46:20'),(4,'Friends','friends','Group travel with friends','active',40,'2026-10-10 10:46:20','2026-10-10 10:46:20'),(5,'Solo','solo','Individual travel','active',50,'2026-10-10 10:46:20','2026-10-10 10:46:20'),(6,'Business','business','Work-related travel','active',60,'2026-10-10 10:46:20','2026-10-10 10:46:20'),(7,'Pilgrimage','pilgrimage','Religious and spiritual journeys','active',70,'2026-10-10 10:46:20','2026-10-10 10:46:20'),(8,'Adventure','adventure','Activity and adventure travel','active',80,'2026-10-10 10:46:20','2026-10-10 10:46:20'),(9,'Wellness','wellness','Health, relaxation, and wellness travel','active',90,'2026-10-10 10:46:20','2026-10-10 10:46:20'),(10,'Other','other','Other travel requirements','active',100,'2026-10-10 10:46:20','2026-10-10 10:46:20');
/*!40000 ALTER TABLE `trip_themes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `email` varchar(500) NOT NULL,
  `name` varchar(500) NOT NULL,
  `password` varchar(500) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_image` varchar(255) DEFAULT NULL,
  `alt_phone` varchar(20) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state` varchar(120) DEFAULT NULL,
  `postal_code` varchar(12) DEFAULT NULL,
  `country` varchar(80) NOT NULL DEFAULT 'India',
  `medical_issues` text,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `is_pwd` tinyint(1) NOT NULL DEFAULT '0',
  `pwd_details` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('srijabiswas3001@gmail.com','Srija Biswas','$2y$12$//oBwVOurwaOHf1Q/DgQD.4ANdpewRM5yhWdLypQGopnsxzfrVXHW','Kali Charan Ghosh','8956451232',1,'2026-09-26 11:01:39','uploads/profile/e8e203ba47922a81762d720c9e4efa92.jpg','','Kolkata','West Bengal','700004','India','','2001-01-30','Female',0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-12 17:26:37
