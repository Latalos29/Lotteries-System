-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2026 at 02:20 PM
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
('11111111', '2026-02-28', '2026-02-16', 'open', '2026-02-17'),
('22222222', '2026-01-30', '2026-01-16', 'result_announced', '2026-01-18'),
('33333333', '2026-02-28', '2026-02-16', 'closed', '2026-02-18');

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
('12345678', 111111, 100, '11111111', 'avilable', '2026-02-19'),
('43216578', 321654, 150, '33333333', 'sold', '2026-02-19'),
('87654321', 222222, 120, '22222222', 'reserved', '2026-01-19');

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
