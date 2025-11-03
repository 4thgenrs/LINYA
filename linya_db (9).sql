-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 04:44 PM
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
  `office` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`user_id`, `user_name`, `password`, `name`, `user_type`, `office`) VALUES
(1, 'admin', 'admin', 'administrator', 1, 1),
(2, 'Astaff1', 'Astaff1', 'Jason', 2, 2),
(3, 'Astaff2', 'Astaff2', 'Jameson', 2, 2),
(4, 'robert', 'robert', 'Robert', 2, 1),
(5, 'badong', '1234', 'badong', 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `office_id` int(10) NOT NULL,
  `office_description` varchar(20) NOT NULL,
  `office_code` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`office_id`, `office_description`, `office_code`) VALUES
(1, 'Registrar', 'REG'),
(2, 'Accounting', 'ACC'),
(8, 'MIS', 'MIS');

-- --------------------------------------------------------

--
-- Table structure for table `office_windows`
--

CREATE TABLE `office_windows` (
  `window_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `window_no` int(11) NOT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_windows`
--

INSERT INTO `office_windows` (`window_id`, `office_id`, `window_no`, `assigned_staff_id`, `enabled`, `created_at`) VALUES
(1, 2, 1, 2, 1, '2025-10-30 03:30:48'),
(2, 2, 2, 3, 1, '2025-10-30 03:32:07'),
(4, 2, 3, NULL, 0, '2025-10-30 03:32:19'),
(5, 2, 4, 5, 1, '2025-10-30 03:44:24'),
(6, 1, 1, 4, 1, '2025-10-30 03:45:40');

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
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queues`
--

INSERT INTO `queues` (`queue_id`, `queue_code`, `office_id`, `window_id`, `created_at`, `status`) VALUES
(1, 'ACC-001', 2, 4, '2025-10-24 03:50:31', 'finished'),
(2, 'REG-001', 1, 1, '2025-10-24 03:51:09', 'finished'),
(3, 'REG-002', 1, 1, '2025-10-24 03:51:19', 'finished'),
(4, 'ACC-002', 2, 4, '2025-10-24 03:53:28', 'finished'),
(5, 'ACC-003', 2, 4, '2025-10-24 04:14:15', 'finished'),
(6, 'ACC-004', 2, 4, '2025-10-24 04:14:24', 'finished'),
(7, 'ACC-005', 2, 4, '2025-10-24 04:28:06', 'finished'),
(8, 'ACC-006', 2, 4, '2025-10-24 05:56:35', 'finished'),
(9, 'REG-003', 1, 0, '2025-10-24 13:30:42', 'pending'),
(10, 'REG-004', 1, 0, '2025-10-30 04:04:31', 'pending'),
(11, 'REG-005', 1, 0, '2025-10-30 04:07:37', 'pending'),
(12, 'REG-006', 1, 0, '2025-10-30 04:07:58', 'pending'),
(13, 'REG-007', 1, 0, '2025-10-30 04:08:09', 'pending'),
(14, 'REG-008', 1, 0, '2025-10-30 04:13:49', 'pending'),
(15, 'REG-009', 1, 0, '2025-10-30 04:14:18', 'pending'),
(16, 'ACC-007', 2, 4, '2025-10-30 04:29:09', 'finished'),
(17, 'ACC-008', 2, 4, '2025-10-30 10:49:45', 'finished'),
(18, 'REG-010', 1, 0, '2025-10-30 10:51:03', 'pending'),
(19, 'ACC-009', 2, 4, '2025-10-30 10:51:14', 'finished'),
(20, 'ACC-010', 2, 0, '2025-10-30 11:00:34', 'pending'),
(21, 'ACC-011', 2, 0, '2025-10-30 11:01:28', 'pending'),
(22, 'ACC-012', 2, 0, '2025-10-30 11:01:28', 'pending'),
(23, 'ACC-013', 2, 0, '2025-10-30 11:01:35', 'pending'),
(24, 'ACC-014', 2, 0, '2025-10-30 11:01:35', 'pending'),
(25, 'ACC-015', 2, 0, '2025-10-30 11:05:55', 'pending'),
(26, 'ACC-016', 2, 0, '2025-10-30 11:05:55', 'pending'),
(27, 'ACC-017', 2, 0, '2025-10-30 11:06:04', 'pending'),
(28, 'ACC-018', 2, 0, '2025-10-30 11:06:04', 'pending'),
(29, 'ACC-019', 2, 0, '2025-10-30 11:08:26', 'pending'),
(30, 'REG-011', 1, 0, '2025-10-30 11:10:34', 'pending'),
(31, 'REG-012', 1, 0, '2025-10-30 11:13:12', 'pending'),
(32, 'ACC-020', 2, 0, '2025-10-30 11:13:45', 'pending'),
(33, 'ACC-021', 2, 0, '2025-10-30 11:13:52', 'pending'),
(34, 'REG-013', 1, 0, '2025-10-30 14:35:39', 'pending'),
(35, 'REG-014', 1, 0, '2025-10-30 14:35:52', 'pending'),
(36, 'ACC-022', 2, 0, '2025-11-01 04:35:33', 'pending'),
(37, 'ACC-023', 2, 0, '2025-11-01 06:22:42', 'pending'),
(38, 'ACC-024', 2, 0, '2025-11-01 06:23:07', 'pending'),
(39, 'ACC-025', 2, 0, '2025-11-01 06:23:14', 'pending'),
(40, 'ACC-026', 2, 0, '2025-10-31 23:26:45', 'pending'),
(41, 'REG-015', 1, 0, '2025-10-31 23:30:37', 'pending'),
(42, 'ACC-027', 2, 0, '2025-10-31 23:33:16', 'pending'),
(43, 'ACC-028', 2, 0, '2025-10-31 23:42:20', 'pending'),
(44, 'ACC-029', 2, 0, '2025-11-01 00:31:39', 'pending'),
(45, 'ACC-030', 2, 0, '2025-11-01 16:01:40', 'pending'),
(46, 'REG-016', 1, 0, '2025-11-01 16:02:43', 'pending'),
(47, 'ACC-031', 2, 0, '2025-11-01 16:15:22', 'pending');

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
(1, 16),
(2, 31);

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
(1, 1, 10, 0, 1),
(2, 1, 11, 0, 1),
(3, 2, 3, 0, 1),
(4, 2, 4, 0, 1),
(5, 3, 3, 0, 1),
(6, 3, 2, 0, 1),
(7, 4, 11, 0, 1),
(8, 5, 10, 0, 1),
(9, 6, 10, 0, 1),
(10, 6, 11, 0, 1),
(11, 7, 11, 0, 1),
(12, 8, 12, 0, 1),
(13, 9, 3, 0, 1),
(14, 10, 4, 0, 1),
(15, 10, 3, 0, 1),
(16, 11, 3, 1, 1),
(17, 12, 3, 1, 1),
(18, 12, 4, 1, 1),
(19, 13, 3, 0, 1),
(20, 13, 4, 1, 1),
(21, 14, 3, 1, 1),
(22, 15, 3, 1, 1),
(23, 15, 4, 1, 1),
(24, 15, 2, 0, 1),
(25, 16, 11, 0, 1),
(26, 16, 12, 0, 1),
(27, 17, 12, 0, 1),
(28, 17, 11, 0, 1),
(29, 18, 4, 0, 1),
(30, 18, 3, 0, 1),
(31, 19, 1, 0, 1),
(32, 19, 10, 0, 1),
(33, 19, 11, 0, 1),
(34, 19, 12, 0, 1),
(35, 20, 10, 0, 1),
(36, 21, 10, 0, 1),
(37, 22, 10, 0, 1),
(38, 23, 10, 0, 1),
(39, 24, 10, 0, 1),
(40, 25, 10, 0, 1),
(41, 26, 10, 0, 1),
(42, 27, 10, 0, 1),
(43, 28, 10, 0, 1),
(44, 29, 10, 0, 1),
(45, 30, 2, 0, 1),
(46, 31, 2, 0, 1),
(47, 32, 1, 0, 1),
(48, 33, 10, 0, 1),
(49, 34, 4, 0, 1),
(50, 35, 4, 0, 1),
(51, 36, 10, 0, 1),
(52, 36, 11, 0, 1),
(53, 36, 12, 0, 1),
(54, 36, 1, 0, 1),
(55, 37, 12, 0, 1),
(56, 37, 10, 0, 1),
(57, 38, 10, 0, 1),
(58, 39, 12, 0, 1),
(59, 40, 12, 1, 1),
(60, 40, 10, 0, 1),
(61, 41, 2, 1, 1),
(62, 41, 3, 0, 1),
(63, 42, 12, 0, 1),
(64, 43, 10, 1, 1),
(65, 44, 10, 1, 1),
(66, 45, 10, 1, 1),
(67, 45, 12, 1, 1),
(68, 46, 4, 1, 1),
(69, 46, 2, 1, 1),
(70, 46, 3, 1, 1),
(71, 47, 10, 0, 1),
(72, 47, 12, 1, 1);

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
  `office` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_description`, `office`) VALUES
(1, 'T.O.R', 2),
(2, 'Diploma', 1),
(3, 'Good Moral', 1),
(4, 'Adding and Changing', 1),
(10, 'Order of Payment', 2),
(11, '1v1 with Sir Aldrin', 2),
(12, 'Payment Failure', 2);

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
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `office_windows`
--
ALTER TABLE `office_windows`
  MODIFY `window_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `queues`
--
ALTER TABLE `queues`
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `queue_services`
--
ALTER TABLE `queue_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  ADD CONSTRAINT `fk_qs_queue` FOREIGN KEY (`queue_id`) REFERENCES `queues` (`queue_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qs_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `service_office_id` FOREIGN KEY (`office`) REFERENCES `offices` (`office_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
