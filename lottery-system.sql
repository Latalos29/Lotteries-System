-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 07:33 AM
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
-- Database: `lottery-system`
--

-- --------------------------------------------------------

--
-- Table structure for table `draws`
--

CREATE TABLE `draws` (
  `draw_id` varchar(8) NOT NULL,
  `draw_date` date NOT NULL,
  `draw_name` date NOT NULL,
  `status` varchar(255) NOT NULL COMMENT 'open/closed/result_announced',
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `draws`
--

INSERT INTO `draws` (`draw_id`, `draw_date`, `draw_name`, `status`, `created_at`) VALUES
('11111111', '2026-02-28', '2026-02-16', 'open',             '2026-02-17'),
('22222222', '2026-01-30', '2026-01-16', 'result_announced', '2026-01-18'),
('26515634', '2026-03-01', '2026-03-16', 'open',             '2026-03-01'),
('33333333', '2026-02-28', '2026-02-16', 'closed',           '2026-02-18'),
('34515165', '2026-03-01', '2026-03-16', 'open',             '2026-03-01'),
('65492493', '2026-03-01', '2026-03-16', 'open',             '2026-03-01'),
-- งวดใหม่ เริ่ม 1 มีนาคม 2026 (16 มีนา)
('30000001', '2026-03-16', '2026-03-01', 'open',             '2026-03-01'),
('30000002', '2026-03-16', '2026-03-01', 'open',             '2026-03-01'),
('30000003', '2026-03-16', '2026-03-01', 'open',             '2026-03-01'),
('30000004', '2026-03-16', '2026-03-01', 'open',             '2026-03-01'),
('30000005', '2026-03-16', '2026-03-01', 'open',             '2026-03-01'),
-- งวดใหม่ เริ่ม 1 มีนาคม 2026 (1 เมษา)
('30000006', '2026-04-01', '2026-03-01', 'open',             '2026-03-01'),
('30000007', '2026-04-01', '2026-03-01', 'open',             '2026-03-01'),
('30000008', '2026-04-01', '2026-03-01', 'open',             '2026-03-01'),
('30000009', '2026-04-01', '2026-03-01', 'open',             '2026-03-01'),
('30000010', '2026-04-01', '2026-03-01', 'open',             '2026-03-01');

-- --------------------------------------------------------

--
-- Table structure for table `lotteries`
--

CREATE TABLE `lotteries` (
  `lottery_id` varchar(13) NOT NULL,
  `lotteryNumber` int(6) NOT NULL,
  `price` int(3) NOT NULL,
  `draw_id` varchar(8) DEFAULT NULL,
  `status` varchar(255) NOT NULL COMMENT 'available/reserved/sold',
  `created_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lotteries`
--

INSERT INTO `lotteries` (`lottery_id`, `lotteryNumber`, `price`, `draw_id`, `status`, `created_at`) VALUES
('12345678',    111111, 100, '11111111', 'available', '2026-02-19'),
('43216578',    321654, 150, '33333333', 'sold',      '2026-02-19'),
('87654321',    222222, 120, '22222222', 'reserved',  '2026-01-19'),
-- ลอตเตอรี่งวดใหม่ มีนาคม 2026
('3000000001',  100001,  80, '30000001', 'available', '2026-03-01'),
('3000000002',  200034,  80, '30000001', 'available', '2026-03-01'),
('3000000003',  345678, 100, '30000002', 'available', '2026-03-01'),
('3000000004',  456789, 100, '30000002', 'reserved',  '2026-03-01'),
('3000000005',  567890, 120, '30000003', 'available', '2026-03-01'),
('3000000006',  678901, 120, '30000003', 'sold',      '2026-03-01'),
('3000000007',  789012,  80, '30000004', 'available', '2026-03-01'),
('3000000008',  890123,  80, '30000004', 'reserved',  '2026-03-01'),
('3000000009',  901234, 100, '30000005', 'available', '2026-03-01'),
('3000000010',  123456, 100, '30000005', 'sold',      '2026-03-01'),
('3000000011',  234567, 120, '30000006', 'available', '2026-03-01'),
('3000000012',  135792,  80, '30000006', 'available', '2026-03-01'),
('3000000013',  246801, 100, '30000007', 'reserved',  '2026-03-01'),
('3000000014',  357912, 100, '30000007', 'available', '2026-03-01'),
('3000000015',  468023, 120, '30000008', 'sold',      '2026-03-01'),
('3000000016',  579134,  80, '30000008', 'available', '2026-03-01'),
('3000000017',  680245, 100, '30000009', 'available', '2026-03-01'),
('3000000018',  791356,  80, '30000009', 'reserved',  '2026-03-01'),
('3000000019',  802467, 100, '30000010', 'available', '2026-03-01'),
('3000000020',  913578, 120, '30000010', 'sold',      '2026-03-01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `draws`
--
ALTER TABLE `draws`
  ADD PRIMARY KEY (`draw_id`);

--
-- Indexes for table `lotteries`
--
ALTER TABLE `lotteries`
  ADD PRIMARY KEY (`lottery_id`),
  ADD KEY `draw_id` (`draw_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lotteries`
--
ALTER TABLE `lotteries`
  ADD CONSTRAINT `lotteries_ibfk_1` FOREIGN KEY (`draw_id`) REFERENCES `draws` (`draw_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
