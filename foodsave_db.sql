-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2026 at 08:36 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `foodsave_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `donation_feedback`
--

CREATE TABLE `donation_feedback` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `food_quality` text NOT NULL,
  `system_efficiency` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `donation_feedback`
--

INSERT INTO `donation_feedback` (`id`, `request_id`, `food_quality`, `system_efficiency`, `created_at`) VALUES
(2, 3, 'good quality', 'best setup, policies and team thankyou', '2025-11-21 17:22:32'),
(4, 5, 'good', '123', '2026-01-04 10:25:10');

-- --------------------------------------------------------

--
-- Table structure for table `donation_requests`
--

CREATE TABLE `donation_requests` (
  `id` int(11) NOT NULL,
  `donation_id` int(11) NOT NULL,
  `ngo_id` int(11) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `pickup_quantity` varchar(50) DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `pickup_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `donation_requests`
--

INSERT INTO `donation_requests` (`id`, `donation_id`, `ngo_id`, `request_date`, `status`, `pickup_quantity`, `pickup_date`, `pickup_time`) VALUES
(3, 2, 5, '2025-11-21 17:08:32', 'completed', '2', '2025-11-21', '18:08:00'),
(4, 10, 5, '2025-12-25 20:46:40', 'completed', '4', '2025-12-25', '21:52:00'),
(5, 1, 5, '2025-12-25 21:47:55', 'completed', '7', '2025-12-25', '22:49:00');

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_type` enum('restaurant','grocery_store','bakery','catering','hotel','cafe','individual','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_license` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operating_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`operating_hours`)),
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donors`
--

INSERT INTO `donors` (`id`, `user_id`, `business_name`, `contact_name`, `email`, `phone`, `business_type`, `address`, `description`, `password`, `business_license`, `operating_hours`, `status`, `email_verified`, `created_at`, `updated_at`, `latitude`, `longitude`) VALUES
(2, 2, 'malik stores', 'ali', 'ali@gmail.com', '03023324375', 'grocery_store', '123 Main St, Lahore, Pakistan', 'nill', '$2y$10$J7KdZFUqpehnGlNddfMWrOD.dUmBfkKVKGjiNv.4y6pFlBqTuOyw.', NULL, NULL, 'active', 0, '2025-09-10 09:02:08', '2025-09-10 09:02:08', NULL, NULL),
(3, 4, 'ABC', 'Diya Shezadi', 'diya@gmail.com', '03015401809', 'hotel', 'PWD Mareee Road', 'imp', '$2y$10$r5aoa6zPQKsG3EUkf/qQ4ugCuDvH3gPYd5GYo2/OMn2DzClsf4GvC', NULL, NULL, 'active', 0, '2025-11-21 11:12:00', '2025-11-22 03:04:03', NULL, NULL),
(4, 6, 'Great Public NGO', 'Ali khan', 'donor@gmail.com', '03015401809', 'bakery', 'Tarbela Road, Hazro, Hazro Tehsil, Attock District, Rawalpindi Division, Punjab, Pakistan', '', '$2y$10$MSAkl5Sqlt.olTejy.XfiefQVRPiCwBE1Mspevzg6UBwAuHpEa23e', NULL, NULL, 'active', 0, '2025-12-25 21:20:17', '2025-12-25 21:22:17', '33.90056666', '72.48666243');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `license_number` varchar(100) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `full_name`, `phone`, `license_number`, `vehicle_type`, `status`, `created_at`) VALUES
(1, 'Ali', '0305-1122334', 'LIC-004', 'Van', 'active', '2026-01-04 08:26:29');

-- --------------------------------------------------------

--
-- Table structure for table `driver_assignments`
--

CREATE TABLE `driver_assignments` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `driver_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `receipt_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `driver_assignments`
--

INSERT INTO `driver_assignments` (`id`, `request_id`, `driver_id`, `assigned_at`, `status`, `receipt_file`) VALUES
(12, 4, 1, '2026-01-04 09:38:13', 'completed', 'receipt_12_1767516117.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `food_donations`
--

CREATE TABLE `food_donations` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `food_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('vegetables','fruits','dairy','meat','grains','prepared_food','beverages','snacks','frozen','canned','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` enum('kg','lbs','servings','pieces','liters','boxes','bags','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration_date` date NOT NULL,
  `pickup_time_start` time DEFAULT NULL,
  `pickup_time_end` time DEFAULT NULL,
  `special_instructions` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `food_safety_info` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urgent` tinyint(1) NOT NULL DEFAULT 0,
  `estimated_value` decimal(10,2) DEFAULT NULL,
  `status` enum('available','requested','reserved','completed','expired','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `pickup_address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `food_donations`
--

INSERT INTO `food_donations` (`id`, `donor_id`, `food_name`, `category`, `quantity`, `unit`, `expiration_date`, `pickup_time_start`, `pickup_time_end`, `special_instructions`, `food_safety_info`, `images`, `description`, `urgent`, `estimated_value`, `status`, `location_lat`, `location_lng`, `pickup_address`, `created_at`, `updated_at`) VALUES
(1, 2, 'grains', 'grains', '40', 'kg', '2026-09-18', NULL, NULL, NULL, NULL, NULL, 'nill nill', 0, NULL, 'requested', NULL, NULL, NULL, '2025-09-10 09:18:34', '2025-12-25 21:47:55'),
(2, 3, 'karachi biryani', 'meat', '20', 'pieces', '2026-11-21', NULL, NULL, NULL, NULL, NULL, 'imp', 1, NULL, 'requested', NULL, NULL, NULL, '2025-11-21 11:12:56', '2025-12-25 21:46:46'),
(3, 3, 'Fruits', 'fruits', '100', 'kg', '2025-11-23', NULL, NULL, NULL, NULL, NULL, '', 1, NULL, '', NULL, NULL, NULL, '2025-11-22 02:04:34', '2025-11-22 03:09:25'),
(4, 2, 'Fresh Vegetables Pack', 'vegetables', '25', 'kg', '2026-12-10', NULL, NULL, NULL, NULL, NULL, 'Mixed fresh vegetables', 0, NULL, 'available', NULL, NULL, '123 Main St, Lahore', '2025-12-25 20:42:52', '2025-12-25 21:46:51'),
(5, 2, 'Milk Cartons', 'dairy', '30', 'liters', '2025-12-05', NULL, NULL, NULL, NULL, NULL, 'Sealed milk cartons', 1, NULL, 'available', NULL, NULL, '123 Main St, Lahore', '2025-12-25 20:42:52', '2025-12-25 20:42:52'),
(6, 3, 'Chicken Biryani', 'prepared_food', '50', 'servings', '2026-11-30', NULL, NULL, NULL, NULL, NULL, 'Freshly cooked biryani', 1, NULL, 'available', NULL, NULL, 'PWD Maree Road', '2025-12-25 20:42:52', '2025-12-25 21:46:58'),
(7, 3, 'Apple Boxes', 'fruits', '10', 'boxes', '2025-12-15', NULL, NULL, NULL, NULL, NULL, 'Red apples in boxes', 0, NULL, 'available', NULL, NULL, 'PWD Maree Road', '2025-12-25 20:42:52', '2025-12-25 20:42:52'),
(8, 2, 'Bread Packs', 'grains', '40', 'pieces', '2026-11-28', NULL, NULL, NULL, NULL, NULL, 'Daily baked bread', 0, NULL, 'available', NULL, NULL, '123 Main St, Lahore', '2025-12-25 20:42:52', '2025-12-25 21:47:19'),
(9, 2, 'Fresh Vegetables Pack', 'vegetables', '25', 'kg', '2025-12-10', NULL, NULL, NULL, NULL, NULL, 'Mixed fresh vegetables', 0, NULL, 'available', NULL, NULL, '123 Main St, Lahore', '2025-12-25 20:43:04', '2025-12-25 20:43:04'),
(10, 2, 'Milk Cartons', 'dairy', '30', 'liters', '2026-12-05', NULL, NULL, NULL, NULL, NULL, 'Sealed milk cartons', 1, NULL, 'requested', NULL, NULL, '123 Main St, Lahore', '2025-12-25 20:43:04', '2025-12-25 21:47:28'),
(11, 3, 'Chicken Biryani', 'prepared_food', '50', 'servings', '2026-11-30', NULL, NULL, NULL, NULL, NULL, 'Freshly cooked biryani', 1, NULL, 'available', NULL, NULL, 'PWD Maree Road', '2025-12-25 20:43:04', '2025-12-25 21:47:05'),
(12, 3, 'Apple Boxes', 'fruits', '10', 'boxes', '2025-12-15', NULL, NULL, NULL, NULL, NULL, 'Red apples in boxes', 0, NULL, 'available', NULL, NULL, 'PWD Maree Road', '2025-12-25 20:43:04', '2025-12-25 20:43:04'),
(13, 2, 'Bread Packs', 'grains', '40', 'pieces', '2026-11-28', NULL, NULL, NULL, NULL, NULL, 'Daily baked bread', 0, NULL, 'available', NULL, NULL, '123 Main St, Lahore', '2025-12-25 20:43:04', '2025-12-25 21:47:11'),
(14, 4, 'Sweet Merrige Cakes', 'prepared_food', '20', 'pieces', '2026-01-31', NULL, NULL, NULL, NULL, NULL, 'The sweetest works of art a girl could ever wish for. See more ideas about wedding cakes, wedding, cake.', 0, NULL, 'available', NULL, NULL, NULL, '2025-12-25 21:21:46', '2025-12-25 21:21:46');

-- --------------------------------------------------------

--
-- Table structure for table `food_safety_guidelines`
--

CREATE TABLE `food_safety_guidelines` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `food_safety_guidelines`
--

INSERT INTO `food_safety_guidelines` (`id`, `title`, `description`, `created_at`) VALUES
(1, 'Proper Food Storage', 'Store perishable items in the refrigerator at 4°C (40°F) or below, and frozen items at -18°C (0°F) or below. Always separate raw and cooked foods to avoid cross-contamination.', '2025-11-22 03:28:27'),
(2, 'Hand Hygiene', 'Wash hands thoroughly with soap and warm water for at least 20 seconds before handling food. Ensure nails are clean and avoid touching face or hair while preparing food.', '2025-11-22 03:28:27'),
(3, 'Safe Food Handling', 'Use separate cutting boards and utensils for raw meat, poultry, seafood, and vegetables. Always sanitize surfaces and tools after use.', '2025-11-22 03:28:27'),
(4, 'Temperature Control', 'Cook foods to their recommended internal temperatures: poultry 74°C (165°F), ground meat 71°C (160°F), fish 63°C (145°F). Keep hot foods above 60°C (140°F) and cold foods below 4°C (40°F).', '2025-11-22 03:28:27'),
(5, 'Avoiding Cross-Contamination', 'Never place cooked food back on a plate that held raw food. Store raw meats on the lowest shelf in the fridge. Clean all spills immediately.', '2025-11-22 03:28:27'),
(6, 'Personal Protective Measures', 'Wear gloves or use utensils when serving food. Cover any cuts or wounds on hands with waterproof bandages. Avoid handling food when sick.', '2025-11-22 03:28:27'),
(7, 'Proper Food Labeling', 'Label all prepared and packaged food with the name, date of preparation, and expiration date. Rotate stock using the First-In-First-Out (FIFO) method.', '2025-11-22 03:28:27'),
(8, 'Safe Water and Ingredients', 'Use potable water for washing food and cooking. Ensure all ingredients are fresh and check expiration dates before use.', '2025-11-22 03:28:27'),
(9, 'Cleaning and Sanitization', 'Regularly clean all kitchen surfaces, utensils, and equipment. Use approved sanitizers and follow manufacturer guidelines for cleaning.', '2025-11-22 03:28:27'),
(10, 'Allergen Awareness', 'Clearly label foods that contain common allergens such as nuts, dairy, eggs, or gluten. Keep allergen-containing foods separate to avoid cross-contact.', '2025-11-22 03:28:27');

-- --------------------------------------------------------

--
-- Table structure for table `ngos`
--

CREATE TABLE `ngos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `organization_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_type` enum('ngo','food_bank','charity','community_center','religious','government','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `mission` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verification_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`verification_documents`)),
  `service_areas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_areas`)),
  `capacity_info` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','active','rejected','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ngos`
--

INSERT INTO `ngos` (`id`, `user_id`, `organization_name`, `registration_number`, `contact_name`, `email`, `phone`, `organization_type`, `website`, `address`, `mission`, `password`, `verification_documents`, `service_areas`, `capacity_info`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `email_verified`, `created_at`, `updated_at`) VALUES
(2, 3, 'royals ngos', '2222', 'ali', 'royals@gmail.com', '03023324375', 'ngo', '', '123 Main St, Lahore, Pakistan', 'nill', '', NULL, NULL, NULL, 'active', 1, '2025-09-10 09:53:55', NULL, 0, '2025-09-10 09:32:21', '2025-09-10 09:53:55'),
(3, 5, 'diya', '033', 'Diya Shezadi', 'user@gmail.com', '03015401809', 'food_bank', '', 'PWD Mareee Road', 'impmmmmmmm', '', NULL, NULL, NULL, 'active', 1, '2025-11-21 11:16:08', NULL, 0, '2025-11-21 11:15:50', '2025-11-30 10:07:23');

-- --------------------------------------------------------

--
-- Table structure for table `urgent_alerts`
--

CREATE TABLE `urgent_alerts` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `donation_id` int(11) NOT NULL,
  `alert_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `urgent_alerts`
--

INSERT INTO `urgent_alerts` (`id`, `donor_id`, `donation_id`, `alert_message`, `created_at`) VALUES
(1, 3, 3, 'ABC marked the donation \"Fruits\" as URGENT.', '2025-11-22 02:16:21'),
(2, 3, 2, 'ABC marked the donation \"karachi biryani\" as URGENT.', '2025-11-30 10:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('admin','donor','ngo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','pending','rejected','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `email_verified` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `phone`, `address`, `status`, `email_verified`, `last_login`, `created_at`, `updated_at`, `latitude`, `longitude`) VALUES
(1, 'System Administrator', 'admin@gmail.com', '$2y$10$Aj7T0rkIrigoSFr.kBw.GeukrigUw4JgEKEnOgdCPqaDmuNdUIWYO', 'admin', NULL, NULL, 'active', 1, '2026-01-04 11:01:21', '2025-09-10 07:51:32', '2026-01-04 11:01:21', NULL, NULL),
(2, 'ali', 'ali@gmail.com', '$2y$10$J7KdZFUqpehnGlNddfMWrOD.dUmBfkKVKGjiNv.4y6pFlBqTuOyw.', 'donor', '03023324375', '123 Main St, Lahore, Pakistan', 'active', 0, '2025-09-10 09:56:24', '2025-09-10 09:02:08', '2025-09-10 09:56:24', NULL, NULL),
(3, 'royals ngos', 'royals@gmail.com', '$2y$10$cJmFfZkAHwQKP/.LaHLWAel8ryR7W3eZC6c3tmn1PUSweLvphQmSa', 'ngo', NULL, NULL, 'active', 0, '2025-09-10 09:54:27', '2025-09-10 09:32:21', '2025-09-10 09:54:27', NULL, NULL),
(4, 'Diya Shezadi', 'diya@gmail.com', '$2y$10$r5aoa6zPQKsG3EUkf/qQ4ugCuDvH3gPYd5GYo2/OMn2DzClsf4GvC', 'donor', '03015401809', 'PWD Mareee Road', 'active', 0, '2025-12-25 19:49:41', '2025-11-21 11:12:00', '2025-12-25 19:49:41', NULL, NULL),
(5, 'diya', 'user@gmail.com', '$2y$10$OBpvsB5Mhfgju5pSCqVFouW9kDloPPONjBcrIw262avapzsqpJIUy', 'ngo', NULL, NULL, 'active', 0, '2026-01-04 09:24:26', '2025-11-21 11:15:50', '2026-01-04 09:24:26', NULL, NULL),
(6, 'Ali khan', 'ngo@gmail.com', '$2y$10$MSAkl5Sqlt.olTejy.XfiefQVRPiCwBE1Mspevzg6UBwAuHpEa23e', 'donor', '03015401809', 'Tarbela Road, Hazro, Hazro Tehsil, Attock District, Rawalpindi Division, Punjab, Pakistan', 'active', 0, '2025-12-25 21:20:33', '2025-12-25 21:20:17', '2025-12-25 21:20:33', '33.90056666', '72.48666243');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `donation_feedback`
--
ALTER TABLE `donation_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `donation_requests`
--
ALTER TABLE `donation_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request` (`donation_id`,`ngo_id`);

--
-- Indexes for table `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_business_type` (`business_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `driver_assignments`
--
ALTER TABLE `driver_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `food_donations`
--
ALTER TABLE `food_donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_donor_id` (`donor_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expiration_date` (`expiration_date`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_location` (`location_lat`,`location_lng`);

--
-- Indexes for table `food_safety_guidelines`
--
ALTER TABLE `food_safety_guidelines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ngos`
--
ALTER TABLE `ngos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_registration_number` (`registration_number`),
  ADD KEY `idx_organization_type` (`organization_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `urgent_alerts`
--
ALTER TABLE `urgent_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_user_type` (`user_type`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `donation_feedback`
--
ALTER TABLE `donation_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `donation_requests`
--
ALTER TABLE `donation_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `donors`
--
ALTER TABLE `donors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `driver_assignments`
--
ALTER TABLE `driver_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `food_donations`
--
ALTER TABLE `food_donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `food_safety_guidelines`
--
ALTER TABLE `food_safety_guidelines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ngos`
--
ALTER TABLE `ngos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `urgent_alerts`
--
ALTER TABLE `urgent_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donation_feedback`
--
ALTER TABLE `donation_feedback`
  ADD CONSTRAINT `donation_feedback_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `donation_requests` (`id`);

--
-- Constraints for table `donors`
--
ALTER TABLE `donors`
  ADD CONSTRAINT `donors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_assignments`
--
ALTER TABLE `driver_assignments`
  ADD CONSTRAINT `fk_driver_assignments_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_driver_assignments_request` FOREIGN KEY (`request_id`) REFERENCES `donation_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `food_donations`
--
ALTER TABLE `food_donations`
  ADD CONSTRAINT `food_donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ngos`
--
ALTER TABLE `ngos`
  ADD CONSTRAINT `ngos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ngos_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
