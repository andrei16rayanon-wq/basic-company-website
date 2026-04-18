-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2026 at 12:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `employee`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `log_in_time` datetime DEFAULT current_timestamp(),
  `log_out_time` datetime DEFAULT NULL,
  `status` enum('Online','Offline') DEFAULT 'Online',
  `total_earned` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `log_in_time`, `log_out_time`, `status`, `total_earned`) VALUES
(1, 2, '2026-03-21 20:27:11', NULL, 'Online', 0.00),
(2, 2, '2026-03-21 20:43:40', '2026-03-21 20:43:58', 'Offline', 0.00),
(3, 2, '2026-03-21 20:44:09', NULL, 'Online', 0.00),
(4, 2, '2026-03-28 17:59:29', '2026-03-28 19:03:50', 'Offline', 53.63),
(5, 2, '2026-03-28 19:41:35', '2026-03-28 19:41:46', 'Offline', 0.15),
(6, 2, '2026-03-28 19:43:50', NULL, 'Online', 0.00),
(7, 2, '2026-03-28 19:45:14', '2026-03-28 20:34:02', 'Offline', 40.67),
(8, 2, '2026-03-28 20:34:11', NULL, 'Online', 0.00),
(9, 2, '2026-04-11 17:49:55', NULL, 'Online', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `Id` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `DateOfBirth` date NOT NULL,
  `ContactNo` varchar(20) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `ProfilePicture` varchar(255) DEFAULT NULL,
  `Skills` text DEFAULT NULL,
  `EducationalAttainment` varchar(255) DEFAULT NULL,
  `EmploymentStatus` enum('Job Order','Regular','Contractual') NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`Id`, `FirstName`, `LastName`, `DateOfBirth`, `ContactNo`, `Address`, `Email`, `Username`, `Password`, `ProfilePicture`, `Skills`, `EducationalAttainment`, `EmploymentStatus`, `profile_pic`) VALUES
(1, 'dummy', 'acc', '2007-10-16', '0912345678', 'subic', 'dummy@email.com', 'dummy_acc', 'dummyacc', NULL, 'php', 'kinder', 'Regular', NULL),
(2, 'testacc', 'acc', '2026-03-21', '0912345678', 'subic', 'testacc@email.com', 'testacc', '$2y$10$WnUf56Z07/45uiNQnXvWVu207T0qHQwDx6Lphvb6pzAVyUopFPcsW', '1774700341_3b750e04-2306-4673-bd94-31671658bfcd~rs_768.h-cr_0.0.1458.jpg', 'php', 'kinder', 'Contractual', 'uploads/1774096005_stonksmeme.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `asset_name` varchar(50) NOT NULL,
  `transaction_type` enum('BUY','SELL') NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(18,8) NOT NULL,
  `total_value` decimal(18,8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `employee_id`, `asset_name`, `transaction_type`, `quantity`, `price_at_time`, `total_value`, `created_at`) VALUES
(1, 2, 'BTC', 'BUY', 20, 64222.23000000, 1306922.38050000, '2026-03-21 12:48:53'),
(2, 2, 'BTC', 'BUY', 20, 64222.23000000, 1306922.38050000, '2026-03-21 12:49:14'),
(3, 2, 'BTC', 'SELL', 999, 64247.79000000, 64204743.98070000, '2026-03-21 12:49:46'),
(4, 2, 'BTC', 'BUY', 90, 64182.44000000, 5776419.60000000, '2026-03-28 11:48:54'),
(5, 2, 'BTC', 'SELL', 80, 64231.33000000, 5138506.40000000, '2026-03-28 11:48:59'),
(6, 2, 'BTC', 'SELL', 3000, 64241.86000000, 192725580.00000000, '2026-03-28 11:49:05'),
(7, 2, 'BTC', 'BUY', 9000, 64211.17000000, 577900530.00000000, '2026-03-28 11:49:10'),
(8, 2, 'BTC', 'BUY', 90000, 64247.46000000, 5782271400.00000000, '2026-03-28 11:49:14'),
(9, 2, 'BTC', 'SELL', 90, 64249.69000000, 5782472.10000000, '2026-03-28 11:49:20'),
(10, 2, 'BTC', 'SELL', 9000, 64245.34000000, 578208060.00000000, '2026-03-28 12:21:05'),
(11, 2, 'BTC', 'SELL', 700, 64238.89000000, 44967223.00000000, '2026-03-28 12:22:10'),
(12, 2, 'BTC', 'BUY', 400, 64223.19000000, 25689276.00000000, '2026-03-28 12:23:16'),
(13, 2, 'BTC', 'SELL', 90, 64203.53000000, 5778317.70000000, '2026-03-28 12:23:24'),
(14, 2, 'BTC', 'BUY', 7000, 64275.40000000, 449927800.00000000, '2026-03-28 12:23:34'),
(15, 2, 'BTC', 'BUY', 900, 64230.21000000, 57807189.00000000, '2026-03-28 12:26:51'),
(16, 2, 'BTC', 'BUY', 2147483647, 64226.11000000, 9999999999.99999999, '2026-03-28 12:28:38'),
(17, 2, 'BTC', 'SELL', 44444, 64237.42000000, 2854967894.48000000, '2026-03-28 12:28:44'),
(18, 2, 'BTC', 'SELL', 9999, 64233.91000000, 642274866.09000000, '2026-03-28 12:29:16');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_at` datetime NOT NULL,
  `logout_at` datetime DEFAULT NULL,
  `total_earned` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `withdrawn_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `withdrawals`
--

INSERT INTO `withdrawals` (`id`, `employee_id`, `amount`, `withdrawn_at`, `created_at`) VALUES
(1, 2, 10.00, '2026-04-11 10:24:21', '2026-04-11 10:24:21'),
(2, 2, 10.00, '2026-04-11 10:25:04', '2026-04-11 10:25:04'),
(3, 2, 1.00, '2026-04-11 10:26:32', '2026-04-11 10:26:32'),
(4, 2, 1.00, '2026-04-11 10:31:04', '2026-04-11 10:31:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
