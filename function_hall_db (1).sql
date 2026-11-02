-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2026 at 10:49 AM
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
-- Database: `function_hall_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$4.kQ1w3k.N9Z5I2lK3L/Uu6i3oM9R/XhX.E/qZqLzQzQzQzQzQzQz');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_dates`
--

CREATE TABLE `blocked_dates` (
  `id` int(11) NOT NULL,
  `blocked_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `reservation_code` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `guest_count` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `add_ons` text DEFAULT NULL,
  `id_proof_path` varchar(255) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_receipt_path` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `downpayment_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active_event_date` date GENERATED ALWAYS AS (case when `status` in ('Pending','Approved') then `event_date` else NULL end) STORED,
  `secondary_contact_name` varchar(150) DEFAULT NULL,
  `secondary_contact_number` varchar(30) DEFAULT NULL,
  `event_hours` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `reservation_code`, `full_name`, `email`, `phone`, `event_type`, `guest_count`, `event_date`, `add_ons`, `id_proof_path`, `payment_method`, `payment_receipt_path`, `total_amount`, `downpayment_amount`, `status`, `admin_notes`, `created_at`, `secondary_contact_name`, `secondary_contact_number`, `event_hours`) VALUES
(1, 'RES-9ED6B0E0', 'Michael John Rosales', 'michaeljohnr198@gmail.com', '09526149170', 'Corporate Event', 250, '2026-10-21', '', 'uploads/1792573362_id_norsu.png', 'GCash', 'uploads/1792573362_rcpt_9c7906e7-1f0f-435b-82a9-c5f3bd39897a.jpg', 15000.00, 7500.00, 'Approved', NULL, '2026-10-21 09:02:42', NULL, NULL, 1),
(2, 'RES-756DBD60', 'Maria Merced', 'michaeljohnr198@gmail.com', '09526149170', 'Birthday', 100, '2026-10-24', 'Catering Service (₱8,000), Sound and DJ System (₱5,000)', 'uploads/1792741756_id_grand_horizon_logo.png', 'GoTyme', 'uploads/1792741756_rcpt_Glossy MJ Tech Class Badge.png', 28000.00, 14000.00, 'Pending', NULL, '2026-10-23 07:49:16', NULL, NULL, 1),
(3, 'RES-948F97C6', 'jasper', 'michaeljohnr198@gmail.com', '09526149170', 'Birthday', 200, '2026-10-27', '', 'uploads/1792742260_id_MJ Tech Class Emblem.png', 'GCash', 'uploads/1792742260_rcpt_M.J Rosales Grand Horizon Emblem.png', 15000.00, 7500.00, 'Approved', NULL, '2026-10-23 07:57:40', NULL, NULL, 1),
(4, 'RES-FEC44378', 'Rosales, Michael John M.', 'mjrosales200618@gmail.com', '09526149170', 'Birthday', 200, '2026-11-01', '', 'uploads/id_proof_d77fa3d7e20ceed54f08838b.png', 'GCash', 'uploads/payment_receipt_cd5112518b6066c197131e03.jpg', 15000.00, 7500.00, 'Approved', NULL, '2026-10-31 14:51:45', NULL, NULL, 1),
(5, 'RES-AF0E73A3', 'Rosales, Michael John M.', 'mjrosales200618@gmail.com', '09526149170', 'Birthday', 300, '2026-11-02', '', 'uploads/id_proof_30f97392d71dd5166ce80bf4.jpg', 'GCash', 'uploads/payment_receipt_ef16e9533ad8f66dcf471e3e.jpg', 15000.00, 7500.00, 'Pending', NULL, '2026-10-31 15:49:45', 'Michael John', '09526149170', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_reply` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `customer_name`, `rating`, `comment`, `created_at`, `admin_reply`, `status`) VALUES
(2, 'Maria', 4, 'This Grand M.J Rosales Horizon Function Hall is very Nice and so Beautiful.', '2026-10-22 11:58:25', 'THANK YOU FOR YOU\'R FEEDBACK MA\'AM !!', 'Approved'),
(3, 'MARIO', 5, 'IYGSOFUYGLIFUDK', '2026-10-22 11:59:43', 'HFGBIUSHFSUKF', 'Approved'),
(4, 'Jasper', 3, 'sfsfdfdfdfdfd', '2026-10-23 08:21:05', NULL, 'Rejected');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `blocked_dates`
--
ALTER TABLE `blocked_dates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blocked_date` (`blocked_date`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservation_code` (`reservation_code`),
  ADD UNIQUE KEY `uq_reservations_active_event_date` (`active_event_date`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blocked_dates`
--
ALTER TABLE `blocked_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
