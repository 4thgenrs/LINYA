-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2025 at 08:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `linya_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `user_id` int(10) NOT NULL,
  `user_name` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `name` varchar(30) NOT NULL,
  `user_type` int(10) NOT NULL,
  `office` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`user_id`, `user_name`, `password`, `name`, `user_type`, `office`) VALUES
(1, 'admin', 'admin', 'administrator', 1, 1),
(2, 'Astaff1', 'Astaff1', 'Jason', 2, 10),
(3, 'Astaff2', 'Astaff2', 'Jameson', 2, 10),
(4, 'robert', 'robert', 'Robert', 2, 1),
(5, 'badong', '1234', 'badong', 2, NULL),
(7, 'roberts', 'roberts', 'roberts', 2, NULL),
(8, 'eryel', 'eryel', 'eryel', 2, NULL),
(9, 'christian', '12345', 'christian', 2, NULL),
(12, 'lexter', '123', 'lexter', 2, NULL),
(13, 'abcd', '1234', 'abcd', 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `finished_queues`
--

CREATE TABLE `finished_queues` (
  `finished_id` int(11) NOT NULL,
  `original_queue_id` int(11) DEFAULT NULL,
  `office_id` varchar(255) DEFAULT NULL,
  `window_id` int(11) DEFAULT NULL,
  `processed_by` varchar(100) DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `services` text DEFAULT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finished_queues`
--

INSERT INTO `finished_queues` (`finished_id`, `original_queue_id`, `office_id`, `window_id`, `processed_by`, `finished_at`, `created_at`, `status`, `services`, `customer_name`, `customer_phone`, `customer_email`) VALUES
(1, 94, 'College Of Law', 1, 'lexty', '2025-11-16 20:35:04', '2025-11-16 20:34:59', 'finished', 'Legal Documents', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `office_id` int(10) NOT NULL,
  `office_description` varchar(20) NOT NULL,
  `office_code` varchar(3) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`office_id`, `office_description`, `office_code`, `enabled`) VALUES
(1, 'Registrar', 'REG', 1),
(10, 'Management Informati', 'MIS', 1),
(12, 'Accounting', 'ACC', 1);

-- --------------------------------------------------------

--
-- Table structure for table `office_windows`
--

CREATE TABLE `office_windows` (
  `window_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `window_no` int(11) NOT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_windows`
--

INSERT INTO `office_windows` (`window_id`, `office_id`, `window_no`, `assigned_staff_id`, `enabled`, `created_at`) VALUES
(1, NULL, 1, 2, 1, '2025-10-30 03:30:48'),
(2, NULL, 2, 3, 1, '2025-10-30 03:32:07'),
(6, 1, 1, 4, 1, '2025-10-30 03:45:40'),
(7, NULL, 1, 8, 1, '2025-11-14 00:33:53'),
(8, 1, 2, 10, 0, '2025-11-14 03:01:33'),
(9, NULL, 4, 13, 1, '2025-11-15 06:02:20'),
(10, NULL, 3, 5, 1, '2025-11-15 06:04:26'),
(11, NULL, 2, 9, 1, '2025-11-15 06:05:57'),
(12, NULL, 3, 12, 1, '2025-11-15 06:06:16'),
(13, NULL, 4, 7, 1, '2025-11-15 06:06:26'),
(14, 1, 3, 14, 1, '2025-11-17 02:46:51'),
(15, NULL, 1, 15, 1, '2025-11-17 03:33:44');

-- --------------------------------------------------------

--
-- Table structure for table `queues`
--

CREATE TABLE `queues` (
  `queue_id` int(11) NOT NULL,
  `queue_code` varchar(32) NOT NULL,
  `office_id` int(11) NOT NULL,
  `window_id` int(10) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queues`
--

INSERT INTO `queues` (`queue_id`, `queue_code`, `office_id`, `window_id`, `created_at`, `status`, `customer_name`, `customer_phone`, `customer_email`) VALUES
(90, 'MIS-001', 10, 0, '2025-11-16 06:05:00', 'pending', 'John', '09456654322', ''),
(92, 'REG-002', 1, 0, '2025-11-16 19:48:12', 'pending', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com'),
(93, 'REG-003', 1, 0, '2025-11-16 19:48:24', 'pending', 'Camille T. Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `queue_counters`
--

CREATE TABLE `queue_counters` (
  `office_id` int(11) NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_counters`
--

INSERT INTO `queue_counters` (`office_id`, `last_number`) VALUES
(1, 3),
(10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `queue_services`
--

CREATE TABLE `queue_services` (
  `id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `soft_copy` tinyint(1) NOT NULL DEFAULT 0,
  `hard_copy` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_services`
--

INSERT INTO `queue_services` (`id`, `queue_id`, `service_id`, `soft_copy`, `hard_copy`) VALUES
(59, 90, 17, 0, 1),
(61, 92, 19, 1, 1),
(62, 92, 20, 1, 1),
(63, 93, 20, 0, 1),
(64, 93, 19, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(10) NOT NULL,
  `role_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'admin'),
(2, 'staff');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(10) NOT NULL,
  `service_description` varchar(20) NOT NULL,
  `office` int(10) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_description`, `office`, `enabled`) VALUES
(16, 'Fix printer', 10, 1),
(17, 'Add software', 10, 1),
(18, 'Assist in AIms', 10, 1),
(19, 'Tor', 1, 1),
(20, 'form137', 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_type_and_role_id` (`user_type`),
  ADD KEY `office_and_offices_id` (`office`);

--
-- Indexes for table `finished_queues`
--
ALTER TABLE `finished_queues`
  ADD PRIMARY KEY (`finished_id`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`office_id`);

--
-- Indexes for table `office_windows`
--
ALTER TABLE `office_windows`
  ADD PRIMARY KEY (`window_id`),
  ADD UNIQUE KEY `uk_office_window` (`office_id`,`window_no`),
  ADD KEY `idx_office` (`office_id`),
  ADD KEY `idx_staff` (`assigned_staff_id`);

--
-- Indexes for table `queues`
--
ALTER TABLE `queues`
  ADD PRIMARY KEY (`queue_id`),
  ADD UNIQUE KEY `uq_queue_code` (`queue_code`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `window_id` (`window_id`);

--
-- Indexes for table `queue_counters`
--
ALTER TABLE `queue_counters`
  ADD PRIMARY KEY (`office_id`);

--
-- Indexes for table `queue_services`
--
ALTER TABLE `queue_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_qs_queue` (`queue_id`),
  ADD KEY `fk_qs_service` (`service_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `service_office_id` (`office`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `finished_queues`
--
ALTER TABLE `finished_queues`
  MODIFY `finished_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `office_windows`
--
ALTER TABLE `office_windows`
  MODIFY `window_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `queues`
--
ALTER TABLE `queues`
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `queue_services`
--
ALTER TABLE `queue_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `office_and_offices_id` FOREIGN KEY (`office`) REFERENCES `offices` (`office_id`),
  ADD CONSTRAINT `user_type_and_role_id` FOREIGN KEY (`user_type`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `queues`
--
ALTER TABLE `queues`
  ADD CONSTRAINT `fk_queue_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE CASCADE;

--
-- Constraints for table `queue_services`
--
ALTER TABLE `queue_services`
  ADD CONSTRAINT `fk_qs_queue` FOREIGN KEY (`queue_id`) REFERENCES `queues` (`queue_id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `service_office_id` FOREIGN KEY (`office`) REFERENCES `offices` (`office_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
