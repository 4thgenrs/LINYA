-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2025 at 07:53 PM
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
-- Table structure for table `accounting_queue`
--

CREATE TABLE `accounting_queue` (
  `queue_number` int(30) NOT NULL,
  `client_student_number` int(30) NOT NULL,
  `user_id` int(30) NOT NULL,
  `queue_status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'admin', 'admin', 'administrator', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `office_id` int(10) NOT NULL,
  `office_description` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`office_id`, `office_description`) VALUES
(1, 'Registrar'),
(2, 'Accounting'),
(4, 'MIS'),
(6, 'VP Admins');

-- --------------------------------------------------------

--
-- Table structure for table `queue_purpose`
--

CREATE TABLE `queue_purpose` (
  `queue_number` int(30) NOT NULL,
  `service_code` int(20) NOT NULL,
  `client_student_number` int(30) NOT NULL,
  `time_stamp` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registrar_queue`
--

CREATE TABLE `registrar_queue` (
  `queue_number` int(30) NOT NULL,
  `client_student_number` int(30) NOT NULL,
  `user_id` int(30) NOT NULL,
  `queue_status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'T.O.R', 1),
(2, 'Diploma', 1),
(3, 'Good Moral', 1),
(4, 'Adding and Changing', 1),
(8, 'Payment Failure', 4),
(9, 'Honorable Dismissal', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounting_queue`
--
ALTER TABLE `accounting_queue`
  ADD PRIMARY KEY (`queue_number`);

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
-- Indexes for table `registrar_queue`
--
ALTER TABLE `registrar_queue`
  ADD PRIMARY KEY (`queue_number`);

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
-- AUTO_INCREMENT for table `accounting_queue`
--
ALTER TABLE `accounting_queue`
  MODIFY `queue_number` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `registrar_queue`
--
ALTER TABLE `registrar_queue`
  MODIFY `queue_number` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `service_office_id` FOREIGN KEY (`office`) REFERENCES `offices` (`office_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
