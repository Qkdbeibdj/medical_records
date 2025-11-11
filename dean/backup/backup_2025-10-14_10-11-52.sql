-- MySQL dump 10.13  Distrib 9.1.0, for Win64 (x86_64)
--
-- Host: localhost    Database: medical_records
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `certificate_requests`
--

DROP TABLE IF EXISTS `certificate_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificate_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `claim_datetime` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `scheduled_date` date DEFAULT NULL,
  `scheduled_time` time DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `certificate_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificate_requests`
--

LOCK TABLES `certificate_requests` WRITE;
/*!40000 ALTER TABLE `certificate_requests` DISABLE KEYS */;
INSERT INTO `certificate_requests` VALUES (19,7,'rejected',NULL,NULL,'2025-09-26 18:41:22',NULL,NULL),(20,7,'rejected',NULL,NULL,'2025-10-01 18:50:53',NULL,NULL),(21,7,'rejected',NULL,NULL,'2025-10-03 10:45:14',NULL,NULL),(24,74,'pending',NULL,NULL,'2025-10-04 01:41:12',NULL,NULL),(25,7,'approved','2025-10-13 17:09:00',NULL,'2025-10-11 21:44:15',NULL,NULL);
/*!40000 ALTER TABLE `certificate_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ishihara_questions`
--

DROP TABLE IF EXISTS `ishihara_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ishihara_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  `correct_answer` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ishihara_questions`
--

LOCK TABLES `ishihara_questions` WRITE;
/*!40000 ALTER TABLE `ishihara_questions` DISABLE KEYS */;
INSERT INTO `ishihara_questions` VALUES (1,'images/ishihara/plate1.webp','7'),(2,'images/ishihara/plate2.webp','6'),(3,'images/ishihara/plate3.webp','26'),(4,'images/ishihara/plate4.webp','15'),(5,'images/ishihara/plate5.webp','6'),(6,'images/ishihara/plate6.webp','73'),(7,'images/ishihara/plate7.webp','5'),(8,'images/ishihara/plate8.webp','16'),(9,'images/ishihara/plate9.webp','45'),(10,'images/ishihara/plate10.webp','12'),(11,'images/ishihara/plate11.webp','29'),(12,'images/ishihara/plate12.webp','8');
/*!40000 ALTER TABLE `ishihara_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_certificate`
--

DROP TABLE IF EXISTS `medical_certificate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medical_certificate` (
  `certificate_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `certificate_file` varchar(255) DEFAULT NULL,
  `issued_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`certificate_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `medical_certificate_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_certificate`
--

LOCK TABLES `medical_certificate` WRITE;
/*!40000 ALTER TABLE `medical_certificate` DISABLE KEYS */;
INSERT INTO `medical_certificate` VALUES (76,7,'Approved','uploads/certificates/medical_certificate_7_1760257302.pdf','2025-10-12 08:21:42');
/*!40000 ALTER TABLE `medical_certificate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_tests`
--

DROP TABLE IF EXISTS `medical_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medical_tests` (
  `test_id` int(11) NOT NULL AUTO_INCREMENT,
  `test_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`test_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_tests`
--

LOCK TABLES `medical_tests` WRITE;
/*!40000 ALTER TABLE `medical_tests` DISABLE KEYS */;
INSERT INTO `medical_tests` VALUES (1,'General Check Up','Measures systolic and diastolic pressure.'),(2,'Blood Typing','Checks for visual acuity and color blindness.'),(3,'Chest X-ray','Radiographic image to check internal structures.'),(4,'Basic Hearing Screening','Lab test to analyze urine content.'),(5,' Drug Test','Assesses hearing levels and loss.');
/*!40000 ALTER TABLE `medical_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `physician_activity_log`
--

DROP TABLE IF EXISTS `physician_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `physician_activity_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `physician_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `action_type` enum('test_entry','notification','certificate','upload') NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `physician_activity_log`
--

LOCK TABLES `physician_activity_log` WRITE;
/*!40000 ALTER TABLE `physician_activity_log` DISABLE KEYS */;
INSERT INTO `physician_activity_log` VALUES (1,2,7,'certificate','Uploaded certificate for student ID: 7, file: medical_certificate_7_1759491166.pdf','2025-10-03 11:32:46'),(2,2,7,'certificate','Uploaded certificate for student ID: 7, file: medical_certificate_7_1760257302.pdf','2025-10-12 08:21:42');
/*!40000 ALTER TABLE `physician_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_certificate_logs`
--

DROP TABLE IF EXISTS `student_certificate_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_certificate_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_certificate_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_certificate_logs`
--

LOCK TABLES `student_certificate_logs` WRITE;
/*!40000 ALTER TABLE `student_certificate_logs` DISABLE KEYS */;
INSERT INTO `student_certificate_logs` VALUES (32,7,'requested','Student requested a certificate','2025-09-26 18:41:22'),(33,7,'rejected','Rejected: Certificate request rejected by physician\nOct 01, 2025 08:50 PM','2025-10-01 18:50:39'),(34,7,'requested','Student requested a certificate','2025-10-01 18:50:53'),(35,7,'rejected','Rejected: Certificate request rejected by physician\nOct 03, 2025 04:25 AM','2025-10-03 02:25:39'),(36,7,'requested','Student requested a certificate','2025-10-03 10:45:14'),(37,7,'rejected','Rejected: Certificate request rejected by physician\nOct 03, 2025 01:14 PM','2025-10-03 11:14:37'),(38,7,'requested','Student requested a certificate','2025-10-03 11:17:19'),(39,7,'approved','Approved: Certificate approved. Scheduled claim date and time on Oct 30, 2025 at 07:27 PM','2025-10-03 11:25:44'),(40,74,'requested','Student requested a certificate','2025-10-04 01:28:28'),(41,74,'requested','Student requested a certificate','2025-10-04 01:41:12'),(42,7,'requested','Student requested a certificate','2025-10-11 21:44:15'),(43,7,'approved','Approved: Certificate approved. Scheduled claim date and time on Oct 13, 2025 at 05:09 PM','2025-10-12 08:09:18');
/*!40000 ALTER TABLE `student_certificate_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_ishihara_results`
--

DROP TABLE IF EXISTS `student_ishihara_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_ishihara_results` (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `user_answer` text NOT NULL,
  `score` int(11) NOT NULL,
  `assessment` enum('Passed','Failed') NOT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`result_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_ishihara_results_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_ishihara_results`
--

LOCK TABLES `student_ishihara_results` WRITE;
/*!40000 ALTER TABLE `student_ishihara_results` DISABLE KEYS */;
INSERT INTO `student_ishihara_results` VALUES (23,9,'{\"1\":\"4\",\"2\":\"8\",\"3\":\"26\",\"4\":\"15\",\"5\":\"6\",\"6\":\"78\",\"7\":\"5\",\"8\":\"16\",\"9\":\"46\",\"10\":\"12\",\"11\":\"28\",\"12\":\"8\"}',7,'Failed','2025-08-11 11:13:15'),(24,31,'{\"1\":\"7\",\"2\":\"6\",\"3\":\"26\",\"4\":\"15\",\"5\":\"6\",\"6\":\"73\",\"7\":\"5\",\"8\":\"16\",\"9\":\"46\",\"10\":\"12\",\"11\":\"29\",\"12\":\"8\"}',11,'Passed','2025-08-15 11:11:44'),(25,30,'{\"1\":\"2\",\"2\":\"2\",\"3\":\"1\",\"4\":\"121\",\"5\":\"\",\"6\":\"21\",\"7\":\"\",\"8\":\"\",\"9\":\"1\",\"10\":\"12\",\"11\":\"\",\"12\":\"1\"}',1,'Failed','2025-09-26 10:50:47'),(26,57,'{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"2\",\"7\":\"1\",\"8\":\"1\",\"9\":\"1\",\"10\":\"12\",\"11\":\"\",\"12\":\"1\"}',1,'Failed','2025-09-26 23:44:24'),(27,7,'{\"1\":\"7\",\"2\":\"6\",\"3\":\"26\",\"4\":\"15\",\"5\":\"6\",\"6\":\"73\",\"7\":\"5\",\"8\":\"16\",\"9\":\"45\",\"10\":\"12\",\"11\":\"29\",\"12\":\"8\"}',12,'Passed','2025-10-01 18:08:14'),(28,32,'{\"1\":\"7\",\"2\":\"6\",\"3\":\"26\",\"4\":\"15\",\"5\":\"6\",\"6\":\"78\",\"7\":\"5\",\"8\":\"16\",\"9\":\"46\",\"10\":\"12\",\"11\":\"29\",\"12\":\"8\"}',10,'Passed','2025-10-03 14:31:22'),(29,71,'{\"1\":\"\",\"2\":\"1\",\"3\":\"\",\"4\":\"\",\"5\":\"2\",\"6\":\"\",\"7\":\"1\",\"8\":\"1\",\"9\":\"\",\"10\":\"\",\"11\":\"1\",\"12\":\"1\"}',0,'Failed','2025-10-12 11:52:24'),(30,33,'{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\",\"9\":\"\",\"10\":\"\",\"11\":\"\",\"12\":\"\"}',0,'Failed','2025-10-13 15:20:18');
/*!40000 ALTER TABLE `student_ishihara_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_notifications`
--

DROP TABLE IF EXISTS `student_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `physician_id` int(11) NOT NULL,
  `test_type` varchar(100) NOT NULL,
  `test_datetime` datetime NOT NULL,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `physician_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `physician_id` (`physician_id`),
  CONSTRAINT `student_notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  CONSTRAINT `student_notifications_ibfk_2` FOREIGN KEY (`physician_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_notifications`
--

LOCK TABLES `student_notifications` WRITE;
/*!40000 ALTER TABLE `student_notifications` DISABLE KEYS */;
INSERT INTO `student_notifications` VALUES (41,74,2,' Drug Test','2025-10-03 08:30:00',NULL,'2025-10-03 10:04:00',2),(42,7,2,' Drug Test','2025-10-10 19:13:00',NULL,'2025-10-03 11:13:00',2),(43,7,2,'General Check Up','2025-10-03 23:42:00',NULL,'2025-10-03 11:42:48',2),(44,9,2,' Drug Test','2025-10-17 14:14:00',NULL,'2025-10-08 15:14:49',2),(45,32,2,'Basic Hearing Screening','2025-10-07 06:58:00',NULL,'2025-10-13 22:55:38',2);
/*!40000 ALTER TABLE `student_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_tests`
--

DROP TABLE IF EXISTS `student_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_tests` (
  `student_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `bp` varchar(20) DEFAULT NULL,
  `hr` varchar(20) DEFAULT NULL,
  `rr` varchar(20) DEFAULT NULL,
  `o2_sat` varchar(20) DEFAULT NULL,
  `temperature` varchar(20) DEFAULT NULL,
  `subjective` text DEFAULT NULL,
  `past_history` text DEFAULT NULL,
  `family_history` text DEFAULT NULL,
  `physical_exam` text DEFAULT NULL,
  `assessment` varchar(50) DEFAULT NULL,
  `blood_type` varchar(10) DEFAULT NULL,
  `lungs_findings` text DEFAULT NULL,
  `heart_findings` text DEFAULT NULL,
  `bones_findings` text DEFAULT NULL,
  `impression` text DEFAULT NULL,
  `hearing_result` varchar(50) DEFAULT NULL,
  `thc_result` varchar(10) DEFAULT NULL,
  `meth_result` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`student_id`,`test_id`),
  KEY `fk_student_tests_user_id` (`user_id`),
  CONSTRAINT `fk_student_tests_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_tests`
--

LOCK TABLES `student_tests` WRITE;
/*!40000 ALTER TABLE `student_tests` DISABLE KEYS */;
INSERT INTO `student_tests` VALUES (7,1,'a','a','a','a','a','a','Heart Disease, Asthma/Allergy/Skin, Diabetes/Thyroid |','Hypertension, Allergy |','Neuro, Genitalia | asa','conditional',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-03 11:43:59',NULL),(7,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed','A',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-26 02:44:41',NULL),(7,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'failed',NULL,'Normal','Normal','Normal','normal',NULL,NULL,NULL,'2025-09-26 04:32:21',NULL),(7,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed',NULL,NULL,NULL,NULL,NULL,'normal',NULL,NULL,'2025-09-26 03:11:33',NULL),(7,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed',NULL,NULL,NULL,NULL,NULL,NULL,'negative','negative','2025-10-03 11:36:00',NULL),(9,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed','A+',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-26 03:30:48',NULL),(9,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed',NULL,'For Referral','Normal','Normal','Normal',NULL,NULL,NULL,'2025-08-10 05:26:29',NULL),(9,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'failed',NULL,NULL,NULL,NULL,NULL,'normal',NULL,NULL,'2025-09-26 13:30:45',NULL),(9,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed',NULL,NULL,NULL,NULL,NULL,NULL,'negative','negative','2025-10-13 05:47:22',NULL),(30,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed','A-',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-26 15:53:27',NULL),(30,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed',NULL,'Normal','Normal','Normal','normal',NULL,NULL,NULL,'2025-10-13 05:47:05',NULL),(31,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed','A-',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-01 18:44:00',NULL),(39,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'passed','A-',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-12 03:41:46',NULL);
/*!40000 ALTER TABLE `student_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_number` varchar(20) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` varchar(10) NOT NULL DEFAULT 'Unknown',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `student_number` (`student_number`),
  KEY `fk_students_user_id` (`user_id`),
  CONSTRAINT `fk_students_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (7,'34465','Mark Ian E. Ballesca','2nd Year','BSMT','ballescaian24@gmail.com','121212','2002-04-04','Gais',23,'Male','2025-08-10 03:44:58',238,'active'),(9,'1234','Zaldy Datuin','2nd Year','BSMT','datuinzaldy17@gmail.com','121212','0012-12-12','212121',21,'Male','2025-08-10 05:15:24',241,'inactive'),(30,'S100001','John Doe','1st Year','BSMT','johndoe1@example.com','09123456789','2000-05-15','123 Sample Street',24,'Male','2025-08-10 15:01:01',NULL,'active'),(31,'S100002','Jane Smith','2nd Year','BSMT','janesmith1@example.com','09123456788','1999-06-20','456 Another Street',25,'Female','2025-08-10 15:01:01',NULL,'active'),(32,'S100003','Alex Johnson','1st Year','BSMT','alexjohnson1@example.com','09123456787','2001-03-10','789 Random Road',23,'Male','2025-08-10 15:01:01',NULL,'active'),(33,'S100004','Chris Lee','2nd Year','BSMT','chrislee1@example.com','09123456786','2000-07-22','1010 Seaside Avenue',24,'Male','2025-08-10 15:01:01',NULL,'active'),(34,'S100005','Michael Brown','3rd Year','BSMT','michaelbrown1@example.com','09123456785','1998-11-05','2020 Beachside Blvd',26,'Male','2025-08-10 15:01:01',NULL,'active'),(35,'S100006','Sarah Davis','1st Year','BSMT','sarahdavis1@example.com','09123456784','2001-02-18','3030 Riverside St.',23,'Female','2025-08-10 15:01:01',NULL,'active'),(36,'S100007','Jessica Taylor','2nd Year','BSMT','jessicataylor1@example.com','09123456783','2000-10-09','4040 Hilltop Ave',24,'Female','2025-08-10 15:01:01',NULL,'active'),(37,'S100008','David Wilson','1st Year','BSMT','davidwilson1@example.com','09123456782','2000-12-13','5050 Oakwood Dr.',24,'Male','2025-08-10 15:01:01',NULL,'active'),(38,'S100009','Emma Clark','2nd Year','BSMT','emmaclark1@example.com','09123456781','1999-04-08','6060 Meadow Lane',25,'Female','2025-08-10 15:01:01',NULL,'active'),(39,'S100010','Lucas Walker','3rd Year','BSMT','lucaswalker1@example.com','09123456780','1998-09-19','7070 Elm St.',26,'Male','2025-08-10 15:01:01',NULL,'active'),(40,'S100011','Olivia Harris','1st Year','BSMT','oliviaharris1@example.com','09123456779','2001-01-23','8080 Pinewood St.',23,'Female','2025-08-10 15:01:01',NULL,'active'),(41,'S100012','Daniel Lewis','2nd Year','BSMT','daniellewis1@example.com','09123456778','2000-08-17','9090 Cedar St.',24,'Male','2025-08-10 15:01:01',NULL,'active'),(42,'S100013','Sophia Young','1st Year','BSMT','sophiayoung1@example.com','09123456777','2001-05-10','1011 Birch Ave.',23,'Female','2025-08-10 15:01:01',NULL,'active'),(43,'S100014','William King','2nd Year','BSMT','williamking1@example.com','09123456776','1999-02-01','1122 Walnut St.',25,'Male','2025-08-10 15:01:01',NULL,'active'),(44,'S100015','Charlotte Scott','3rd Year','BSMT','charlottescott1@example.com','09123456775','1998-11-14','1233 Maple Rd.',26,'Female','2025-08-10 15:01:01',NULL,'active'),(45,'S100016','Ethan Green','1st Year','BSMT','ethangreen1@example.com','09123456774','2000-06-10','1344 Redwood Ln.',24,'Male','2025-08-10 15:01:01',NULL,'active'),(46,'S100017','Avery Adams','2nd Year','BSMT','averyadams1@example.com','09123456773','1999-01-30','1455 Aspen Dr.',25,'Female','2025-08-10 15:01:01',NULL,'active'),(47,'S100018','Mason Hall','1st Year','BSMT','masonhall1@example.com','09123456772','2000-04-25','1566 Hickory Ln.',24,'Male','2025-08-10 15:01:01',NULL,'active'),(48,'S100019','Harper Allen','2nd Year','BSMT','harperallen1@example.com','09123456771','1999-12-19','1677 Willow St.',25,'Female','2025-08-10 15:01:01',NULL,'active'),(49,'S100020','Benjamin Young','3rd Year','BSMT','benjaminyoung1@example.com','09123456770','1998-10-03','1788 Juniper Rd.',26,'Male','2025-08-10 15:01:01',NULL,'active'),(50,'S200001','Liam Moore','1st Year','BSMarE','liammoore1@example.com','09123456769','2000-05-15','1000 Ocean St.',24,'Male','2025-08-10 15:01:01',NULL,'inactive'),(51,'S200002','Mia Anderson','2nd Year','BSMarE','miaanderson1@example.com','09123456768','1999-06-10','2000 Harbor St.',25,'Female','2025-08-10 15:01:01',NULL,'active'),(52,'S200003','William Taylor','1st Year','BSMarE','williamtaylor1@example.com','09123456767','2001-07-22','3000 Marina Blvd',23,'Male','2025-08-10 15:01:01',NULL,'inactive'),(53,'S200004','Isabella Jackson','2nd Year','BSMarE','isabellajackson1@example.com','09123456766','1999-12-01','4000 Shore Ln.',25,'Female','2025-08-10 15:01:01',NULL,'active'),(54,'S200005','James White','3rd Year','BSMarE','jameswhite1@example.com','09123456765','1998-10-30','5000 Seaside St.',26,'Male','2025-08-10 15:01:01',NULL,'active'),(55,'S200006','Emma Harris','1st Year','BSMarE','emmaharris1@example.com','09123456764','2000-05-12','6000 Marina Dr.',24,'Female','2025-08-10 15:01:01',NULL,'active'),(56,'S200007','Daniel Clark','2nd Year','BSMarE','danielclark1@example.com','09123456763','2000-09-17','7000 Lagoon Rd.',24,'Male','2025-08-10 15:01:01',NULL,'active'),(57,'S200008','Sophia Martinez','1st Year','BSMarE','sophiamartinez1@example.com','09123456762','2000-11-25','8000 Oceanfront Ave',24,'Female','2025-08-10 15:01:01',NULL,'active'),(58,'S200009','Benjamin Scott','2nd Year','BSMarE','benjaminscott1@example.com','09123456761','1999-08-15','9000 Coral St.',25,'Male','2025-08-10 15:01:01',NULL,'active'),(59,'S200010','Lucas Robinson','3rd Year','BSMarE','lucasrobinson1@example.com','09123456760','1998-06-07','10000 Seaway Blvd',26,'Male','2025-08-10 15:01:01',NULL,'active'),(60,'S200011','Olivia Lewis','1st Year','BSMarE','olivialewis1@example.com','09123456759','2001-04-22','11000 Mariner St.',23,'Female','2025-08-10 15:01:01',NULL,'active'),(61,'S200012','Ethan Young','2nd Year','BSMarE','ethanyoung1@example.com','09123456758','2000-01-09','12000 Dockside Rd.',24,'Male','2025-08-10 15:01:01',NULL,'active'),(62,'S200013','Mason Walker','1st Year','BSMarE','masonwalker1@example.com','09123456757','2000-08-11','13000 Shipyard Ln.',24,'Male','2025-08-10 15:01:01',NULL,'active'),(63,'S200014','Ava King','2nd Year','BSMarE','avaking1@example.com','09123456756','1999-03-14','14000 Tidal Blvd.',25,'Female','2025-08-10 15:01:01',NULL,'active'),(64,'S200015','Ethan Adams','3rd Year','BSMarE','ethanadams1@example.com','09123456755','1998-07-29','15000 Wavecrest Rd.',26,'Male','2025-08-10 15:01:01',NULL,'active'),(65,'S200016','Charlotte Walker','1st Year','BSMarE','charlottewalker1@example.com','09123456754','2001-05-17','16000 Lighthouse St.',23,'Female','2025-08-10 15:01:01',NULL,'active'),(66,'S200017','Grace Robinson','2nd Year','BSMarE','gracerobinson1@example.com','09123456753','2000-06-19','17000 Gull Rd.',24,'Female','2025-08-10 15:01:01',NULL,'active'),(67,'S200018','Jack Allen','1st Year','BSMarE','jackallen1@example.com','09123456752','2000-02-02','18000 Harbor Blvd.',24,'Male','2025-08-10 15:01:01',NULL,'active'),(68,'S200019','Sophia Lee','2nd Year','BSMarE','sophialee1@example.com','09123456751','1999-04-14','19000 Marina Blvd.',25,'Female','2025-08-10 15:01:01',NULL,'active'),(69,'S200020','Jackson White','3rd Year','BSMarE','jacksonwhite1@example.com','09123456750','1998-09-03','20000 Coastal Rd.',26,'Male','2025-08-10 15:01:01',NULL,'active'),(70,'212121','limuel','3rd Year','BSMT','a@g.com','12121','0001-12-12','1212',2,'Female','2025-08-10 15:06:59',242,'active'),(71,'1','sef','2nd Year','BSMarE','aa@g.com','1212','0001-12-12','21212',2,'Male','2025-08-10 15:07:59',NULL,'active'),(72,'31','Dinver Balderas','2nd Year','BSMT','aaa@g.com','3434','0012-12-12','asdx',22,'Male','2025-08-11 14:11:39',244,'active'),(73,'38587','Dinver B. Balderas','3rd Year','BSMT','dinvernbalderas07@gmail.com','09999999999','2025-10-30','doon',99,'Male','2025-10-03 09:51:51',NULL,'active'),(74,'1212121','Cedric J. Ebidag','2nd Year','BSMarE','cedricebidag@gmail.com','3434','2006-03-21','Dasol',121,'Male','2025-10-03 10:02:31',246,'active'),(75,'385871','asasasas','2nd Year','BSMarE','asas@fas.com',NULL,NULL,NULL,NULL,'Unknown','2025-10-12 14:10:44',247,'active'),(76,'092131','Joshua Espinosa Ballesca','2nd Year','BSMarE','ballescaian123@gmail.com',NULL,NULL,NULL,NULL,'Unknown','2025-10-13 07:14:54',248,'active'),(77,'1211312','asdasdasd adas','2nd Year','BSMarE','as@f.com',NULL,NULL,NULL,NULL,'Unknown','2025-10-13 22:00:57',249,'active'),(78,'123','asasasas ass as ad sad','3rd Year','BSMT','adssa@g.com',NULL,NULL,NULL,NULL,'Unknown','2025-10-13 22:01:53',250,'active'),(79,'11232','asdasdasd adass','2nd Year','BSMarE','asdasd@g.com',NULL,NULL,NULL,NULL,'Unknown','2025-10-13 22:04:00',251,'active'),(80,'12121','Physician asdasd','3rd Year','BSMarE','asdasddasda@g.com',NULL,NULL,NULL,NULL,'Unknown','2025-10-13 22:05:03',252,'active');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('physician','student','dean') DEFAULT 'physician',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_id` int(11) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `email_2` (`email`),
  KEY `fk_users_student` (`student_id`),
  CONSTRAINT `fk_users_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Physician','physician@gmail.com','$2y$10$uxKSekMduJBIy9vh5BR6muQx4KXTn5Zq98qibZFNQCZagkHVu5Ita','physician','2025-03-16 04:03:32',NULL,NULL,NULL,NULL,NULL),(194,'STO','sto@gmail.com','$2y$10$gZXvlaGv7Oin0FIn1FXSE.kMHZqow3wBEXJdsdQPD0QKkO5bpTvx.','dean','2025-06-12 01:28:47',NULL,NULL,NULL,NULL,NULL),(238,'Mark Ian E. Ballesca','ballescaian24@gmail.com','$2y$10$b1X60y1FidQbUj.KK5N96.T3LV78Q40SZ3TyIpGbvhRy9/g1niARe','student','2025-08-10 03:44:58',NULL,'6348f409127d718575f66d9e3275012ef6148036b07c7013ca2017bfc80d7fba','2025-10-02 02:15:43','816267','2025-10-12 16:08:38'),(241,'Zaldy Datuin','datuinzaldy17@gmail.com','$2y$10$FqW8etBDbUSkWhs7L.sdreNJbE7GcKc.GNWj01XyUELVeYwl2DJ2W','student','2025-08-10 05:15:24',NULL,NULL,NULL,NULL,NULL),(242,'limuel','a@g.com','$2y$10$hL3twRuOxTyv6hY1kYQZOu28gNZqRZIbGWR35x5pGLmwgIKGR4yai','student','2025-08-10 15:06:58',NULL,NULL,NULL,NULL,NULL),(243,'sef','aa@g.com','$2y$10$HoirKnK0cwj1sWOJ4jVZceO6iCnM53CyT8ANEB.5dJLWnRh2Y05YG','student','2025-08-10 15:07:59',71,NULL,NULL,NULL,NULL),(244,'Dinver Balderas','aaa@g.com','$2y$10$ZR0aPfg4lDRMc0qwF1ZZ9.1UGeCFaCcFg080EjcXx95UxRrK18sRW','student','2025-08-11 14:11:39',NULL,NULL,NULL,NULL,NULL),(245,'Dinver B. Balderas','dinvernbalderas07@gmail.com','$2y$10$RMWcHgF/e8b7DXuEgiF8Du9RhT5d1Lur0fSsv6n7JfNvRuMAZHekO','student','2025-10-03 09:51:51',73,NULL,NULL,NULL,NULL),(246,'Cedric J. Ebidag','cedricebidag@gmail.com','$2y$10$izG1zgo9P2H2CF7WuOP/HegOSdpZUYJ6nUKD4pTx/nVRkup82C/Sm','student','2025-10-03 10:02:31',NULL,NULL,NULL,NULL,NULL),(247,'asasasas','asas@fas.com','$2y$10$329PQ7yaGbAVTTSxg9QDHe1xY5duYaAsauiKCtoB/rMdBA1sjacg6','student','2025-10-12 14:10:44',NULL,NULL,NULL,NULL,NULL),(248,'Joshua Espinosa Ballesca','ballescaian123@gmail.com','$2y$10$o0CbRIv.ALxbWkGq/61ux.WNyG3esoHpcEbKd/CXIrH6VOAt1INw6','student','2025-10-13 07:14:54',NULL,NULL,NULL,NULL,NULL),(249,'asdasdasd adas','as@f.com','$2y$10$O8qm8LEWTrz95Gsjl5U.MeUBzBmt1f.tc5hglcbrziSq4QesaySXK','student','2025-10-13 22:00:57',NULL,NULL,NULL,NULL,NULL),(250,'asasasas ass as ad sad','adssa@g.com','$2y$10$hEKVRbmmpMRyIBhEMQNq7eUJvfji7zICTpSoZakfAm/86Ir.3ov3C','student','2025-10-13 22:01:53',NULL,NULL,NULL,NULL,NULL),(251,'asdasdasd adass','asdasd@g.com','$2y$10$nPm/PSDXGofUOLWVOA9DEO/LPTnW/PP9XPen3MWmYXIR9UJlh5QUy','student','2025-10-13 22:04:00',NULL,NULL,NULL,NULL,NULL),(252,'Physician asdasd','asdasddasda@g.com','$2y$10$FsahdKZR/6FGMgbTXrikbeQ2LNoXgfPN6wJ0FaAp712mCQzRIjpHO','student','2025-10-13 22:05:03',NULL,NULL,NULL,NULL,NULL);
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

-- Dump completed on 2025-10-14 16:11:53
