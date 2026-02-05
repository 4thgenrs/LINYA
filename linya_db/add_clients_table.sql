-- Migration: add clients table
-- Run this on your `linya_db` database to create the clients table

CREATE TABLE IF NOT EXISTS `clients` (
  `client_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `safe_name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `face_data` LONGBLOB DEFAULT NULL,
  `face_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`client_id`),
  KEY `idx_safe_name` (`safe_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
