-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2025 at 05:20 PM
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
-- Table structure for table `queues`
--

CREATE TABLE `queues` (
  `queue_id` int(11) NOT NULL,
  `queue_code` varchar(32) NOT NULL,
  `office_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `window_id` int(10) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queues`
--

INSERT INTO `queues` (`queue_id`, `queue_code`, `office_id`, `created_at`, `window_id`, `status`) VALUES
(1, 'ACC-001', 2, '2025-10-24 03:50:31', 0, 'pending'),
(2, 'REG-001', 1, '2025-10-24 03:51:09', 0, 'pending'),
(3, 'REG-002', 1, '2025-10-24 03:51:19', 0, 'pending'),
(4, 'ACC-002', 2, '2025-10-24 03:53:28', 0, 'pending'),
(5, 'ACC-003', 2, '2025-10-24 04:14:15', 0, 'pending'),
(6, 'ACC-004', 2, '2025-10-24 04:14:24', 0, 'pending'),
(7, 'ACC-005', 2, '2025-10-24 04:28:06', 0, 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `queues`
--
ALTER TABLE `queues`
  ADD PRIMARY KEY (`queue_id`),
  ADD UNIQUE KEY `uq_queue_code` (`queue_code`),
  ADD KEY `office_id` (`office_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `queues`
--
ALTER TABLE `queues`
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `queues`
--
ALTER TABLE `queues`
  ADD CONSTRAINT `fk_queue_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
