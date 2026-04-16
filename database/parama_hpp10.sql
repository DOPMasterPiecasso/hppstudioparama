-- MariaDB dump 10.19  Distrib 10.6.24-MariaDB
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

-- ------------------------------------------------------
-- Table structure for table `addons`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `addons`;
CREATE TABLE `addons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` bigint(20) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_active` (`active`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `addons` WRITE;
INSERT INTO `addons` VALUES 
(1,'Hardcover Book',50000,'book','packaging','Hardcover binding untuk buku',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(2,'Premium Paper',15000,'per','paper','Premium glossy paper',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(3,'Dust Jacket',25000,'piece','print','Dust jacket protection',1,'2026-04-14 10:35:34','2026-04-14 10:35:34');
UNLOCK TABLES;

-- ------------------------------------------------------
-- Table structure for table `cetak_base`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `cetak_base`;
CREATE TABLE `cetak_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `min_students` int(11) NOT NULL,
  `max_students` int(11) NOT NULL,
  `price` bigint(20) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_range` (`min_students`,`max_students`),
  KEY `idx_students` (`min_students`,`max_students`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `cetak_base` WRITE;
INSERT INTO `cetak_base` VALUES 
(1,30,50,150000,'30-50 siswa',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(2,51,75,135000,'51-75 siswa',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(3,76,100,120000,'76-100 siswa',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(4,101,150,115000,'101-150 siswa',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(5,151,200,110000,'151-200 siswa',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(6,201,300,105000,'201-300 siswa',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(7,301,500,100000,'301-500 siswa',1,'2026-04-14 10:35:34','2026-04-14 10:35:34');
UNLOCK TABLES;

-- ------------------------------------------------------
-- Table structure for table `overhead`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `overhead`;
CREATE TABLE `overhead` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `amount` bigint(20) NOT NULL DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `overhead` WRITE;
INSERT INTO `overhead` VALUES 
(1,'designer',20000000,'Desainer grafis & layout',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(2,'marketing',15000000,'Tim marketing & branding',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(3,'creative',8000000,'Produksi kreatif & video',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(4,'pm',8000000,'Project manager',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(5,'sosmed',7000000,'Social media & content',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(6,'freelance',4000000,'Freelancer & contractor',1,'2026-04-14 10:35:34','2026-04-14 10:35:34'),
(7,'operasional',12586000,'Operasional & misc',1,'2026-04-14 10:35:34','2026-04-14 10:35:34');
UNLOCK TABLES;

-- ------------------------------------------------------
-- Table structure for table `users`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role_id` int(11) NOT NULL DEFAULT 3,
  `is_active` tinyint(4) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_active` (`is_active`),
  KEY `idx_role` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES 
(1,'admin','$2y$10$JVcYQCJ9bOxiKKzjD5AaIuFm0JsI.wTCpCsh4WjjMlTELw7aRE0J2','Administrator','admin@parama.studio',1,1,NULL,'2026-04-06 00:00:00','2026-04-06 00:00:00'),
(2,'manager','$2y$10$vkWDCDwnijr09oKHW50ny.tK6u7mGd08CPzTo0SdVsGCbiLWKMIF6','Manajer','manager@parama.studio',2,1,NULL,'2026-04-06 00:00:00','2026-04-06 00:00:00'),
(3,'staff','$2y$10$xwONKMp6sripzA5QZRFBTuoYPhXqIdTvi/Tt0VU5LneOeJjMu6cse','Staff Member','staff@parama.studio',3,1,NULL,'2026-04-06 00:00:00','2026-04-06 00:00:00'),
(4,'qwdq','$2y$10$6Pel76DjzAtKDroKyLeEruGPoz6pCw8QihIswmrlkfLJCQ7AOQveq','qweqwe','',1,1,NULL,'2026-04-06 10:08:38','2026-04-06 10:08:38');
UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
