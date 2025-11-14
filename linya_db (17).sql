-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 08:37 AM
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
(5, 'badong', '1234', 'badong', 2, 2),
(7, 'roberts', 'roberts', 'roberts', 2, 8),
(8, 'eryel', 'eryel', 'eryel', 2, 8),
(9, 'christian', '12345', 'christian', 2, 8),
(10, 'christo', '12345', 'chris', 2, 1),
(11, 'ricardo', '123456', 'ricardo', 2, 8);

-- --------------------------------------------------------

--
-- Table structure for table `finished_queues`
--

CREATE TABLE `finished_queues` (
  `finished_id` int(11) NOT NULL,
  `original_queue_id` int(11) DEFAULT NULL,
  `office_id` int(11) NOT NULL,
  `window_id` int(11) DEFAULT 0,
  `processed_by` varchar(100) DEFAULT NULL,
  `finished_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `status` varchar(20) DEFAULT 'finished',
  `services` text DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finished_queues`
--

INSERT INTO `finished_queues` (`finished_id`, `original_queue_id`, `office_id`, `window_id`, `processed_by`, `finished_at`, `created_at`, `status`, `services`, `customer_name`, `customer_phone`, `customer_email`) VALUES
(8, 27, 2, 4, 'badong', '2025-11-03 22:13:17', '2025-10-30 11:06:04', 'finished', 'Order of Payment', NULL, NULL, NULL),
(9, 28, 2, 4, 'badong', '2025-11-03 22:13:21', '2025-10-30 11:06:04', 'finished', 'Order of Payment', NULL, NULL, NULL),
(10, 29, 2, 4, 'badong', '2025-11-03 22:13:50', '2025-10-30 11:08:26', 'finished', 'Order of Payment', NULL, NULL, NULL),
(11, 32, 2, 4, 'badong', '2025-11-03 22:13:52', '2025-10-30 11:13:45', 'finished', 'T.O.R', NULL, NULL, NULL),
(12, 33, 2, 4, 'badong', '2025-11-03 22:13:55', '2025-10-30 11:13:52', 'finished', 'Order of Payment', NULL, NULL, NULL),
(13, 36, 2, 4, 'badong', '2025-11-03 22:13:57', '2025-11-01 04:35:33', 'finished', 'Order of Payment, 1v1 with Sir Aldrin, Payment Failure, T.O.R', NULL, NULL, NULL),
(14, 37, 2, 4, 'badong', '2025-11-03 22:17:15', '2025-11-01 06:22:42', 'finished', 'Payment Failure, Order of Payment', NULL, NULL, NULL),
(15, 57, 2, 4, 'badong', '2025-11-03 22:29:37', '2025-11-03 22:29:01', 'finished', '1v1 with Sir Aldrin (PDF), T.O.R, Payment Failure', 'Jeremy', '09663811477', 'jeremy@email.com'),
(16, 58, 2, 4, 'badong', '2025-11-03 22:29:39', '2025-11-03 22:29:17', 'finished', 'Payment Failure (PDF)', 'jobert', '09123455555', 'salvador.ricardo.bscs2022@gmail.com'),
(17, 60, 2, 4, 'badong', '2025-11-03 23:30:12', '2025-11-03 22:50:04', 'finished', '1v1 with Sir Aldrin, Order of Payment, Payment Failure, T.O.R', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(18, 61, 2, 4, 'badong', '2025-11-03 23:30:16', '2025-11-03 23:28:52', 'finished', 'Order of Payment', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(19, 62, 2, 4, 'badong', '2025-11-05 09:07:31', '2025-11-05 08:51:39', 'finished', 'Order of Payment', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(20, 63, 2, 1, 'Astaff1', '2025-11-05 09:07:34', '2025-11-05 08:52:23', 'finished', 'Payment Failure (PDF), 1v1 with Sir Aldrin (PDF), Order of Payment (PDF), T.O.R (PDF)', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(21, 64, 2, 4, 'badong', '2025-11-05 13:02:42', '2025-11-05 08:52:58', 'finished', '1v1 with Sir Aldrin', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(22, 66, 2, 4, 'badong', '2025-11-05 13:09:50', '2025-11-05 13:02:15', 'finished', 'Order of Payment (PDF)', 'Camille Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(23, 67, 2, 4, 'badong', '2025-11-05 13:11:23', '2025-11-05 13:10:32', 'finished', 'Order of Payment (PDF)', 'Lawrence', '09474492247', 'bitancurjohnlawrence.bsit@gmail.com'),
(24, 68, 2, 4, 'badong', '2025-11-05 13:12:33', '2025-11-05 13:12:13', 'finished', 'Payment Failure (PDF)', 'John Lexter Balladares', '09276164084', 'armenta.johnlexter.bscs2022@gmail.com'),
(25, 69, 2, 4, 'badong', '2025-11-05 19:57:11', '2025-11-05 19:56:38', 'finished', 'Order of Payment (PDF)', 'Camille Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(26, 70, 2, 4, 'badong', '2025-11-05 21:00:08', '2025-11-05 19:57:39', 'finished', 'T.O.R', 'Jojo B.', '09123455555', 'salvador.ricardo.bscs2022@gmail.com'),
(27, 71, 2, 4, 'badong', '2025-11-05 21:06:01', '2025-11-05 21:00:01', 'finished', 'Payment Failure (PDF), Order of Payment (PDF), T.O.R', 'John', '09915261467', 'ricksiv0107@gmail.com'),
(28, 65, 2, 1, 'Astaff1', '2025-11-06 06:26:34', '2025-11-05 09:05:56', 'finished', 'T.O.R (PDF), 1v1 with Sir Aldrin (PDF), Payment Failure (PDF), Order of Payment (PDF)', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(29, 73, 2, 1, 'Astaff1', '2025-11-06 06:27:44', '2025-11-06 06:26:48', 'finished', 'Order of Payment', 'Camille Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(30, 74, 2, 1, 'Astaff1', '2025-11-06 06:27:46', '2025-11-06 06:27:14', 'finished', 'Order of Payment (PDF)', 'Jeremy', '09663811477', 'jeremy@email.com'),
(31, 75, 2, 1, 'Astaff1', '2025-11-06 06:27:49', '2025-11-06 06:27:28', 'finished', '1v1 with Sir Aldrin (PDF), Order of Payment, Payment Failure', 'jobert', '09123455555', 'salvador.ricardo.bscs2022@gmail.com'),
(32, 76, 2, 1, 'Astaff1', '2025-11-06 06:28:38', '2025-11-06 06:28:06', 'finished', 'Payment Failure (PDF)', 'John', '09915261467', 'ricksiv0107@gmail.com'),
(33, 72, 2, 4, 'badong', '2025-11-06 06:51:57', '2025-11-05 21:06:22', 'finished', 'Order of Payment (PDF)', 'John', '09915261467', 'ricksiv0107@gmail.com'),
(34, 78, 2, 4, 'badong', '2025-11-06 06:52:40', '2025-11-06 06:44:31', 'finished', 'T.O.R', 'Jeremy', '09663811477', 'jeremy@email.com'),
(35, 79, 2, 4, 'badong', '2025-11-06 07:33:28', '2025-11-06 06:44:46', 'finished', 'T.O.R', 'John Lexter Balladares', '09276164084', 'armenta.johnlexter.bscs2022@gmail.com'),
(36, 82, 2, 4, 'badong', '2025-11-13 21:57:59', '2025-11-13 20:05:07', 'finished', 'Order of Payment (PDF)', 'Christian Espinoza', '09223394958', 'espinoza.christian.bscs2022@gmail.com'),
(37, 83, 2, 4, 'badong', '2025-11-13 22:36:03', '2025-11-13 22:15:30', 'finished', '1v1 with Sir Aldrin, Payment Failure, Order of Payment (PDF)', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(38, 84, 2, 4, 'badong', '2025-11-13 22:37:40', '2025-11-13 22:15:50', 'finished', 'Payment Failure (PDF), T.O.R', 'jobert', '09123455555', 'salvador.ricardo.bscs2022@gmail.com');

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
(8, 'MIS', 'MIS'),
(9, 'College Of Law', 'COL');

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
(6, 1, 1, 4, 1, '2025-10-30 03:45:40'),
(7, 8, 1, 8, 1, '2025-11-14 00:33:53'),
(8, 1, 2, 10, 0, '2025-11-14 03:01:33');

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
(85, 'ACC-004', 2, 4, '2025-11-13 22:37:34', 'ongoing', 'Camille T. Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(86, 'ACC-005', 2, 0, '2025-11-14 08:08:23', 'pending', 'Mylene Bayobay', '09942774966', 'bayobaymylene.bsit@gmail.com'),
(87, 'ACC-006', 2, 0, '2025-11-14 08:08:34', 'pending', 'Mylene Bayobay', '09942774966', 'bayobaymylene.bsit@gmail.com'),
(88, 'ACC-007', 2, 0, '2025-11-14 08:08:51', 'pending', 'Mylene Bayobay', '09942774966', 'bayobaymylene.bsit@gmail.com');

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
(1, 0),
(2, 7);

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
(52, 85, 10, 0, 1),
(53, 86, 10, 0, 1),
(54, 86, 12, 0, 1),
(55, 87, 1, 1, 1),
(56, 88, 11, 0, 1),
(57, 88, 12, 1, 1);

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
(12, 'Payment Failure', 2),
(14, 'Memorandum of Agreem', 9);

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
  ADD PRIMARY KEY (`finished_id`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `original_queue_id` (`original_queue_id`);

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
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `finished_queues`
--
ALTER TABLE `finished_queues`
  MODIFY `finished_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `office_windows`
--
ALTER TABLE `office_windows`
  MODIFY `window_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `queues`
--
ALTER TABLE `queues`
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `queue_services`
--
ALTER TABLE `queue_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
