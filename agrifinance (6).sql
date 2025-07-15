-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 10, 2025 at 08:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agrifinance`
--

-- --------------------------------------------------------

--
-- Table structure for table `crops`
--

CREATE TABLE `crops` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `crop_name` varchar(100) DEFAULT NULL,
  `crop_type` varchar(100) DEFAULT NULL,
  `area_planted` float DEFAULT NULL,
  `planting_date` date DEFAULT NULL,
  `expected_harvest_date` date DEFAULT NULL,
  `actual_harvest_date` date DEFAULT NULL,
  `expected_yield` float DEFAULT NULL,
  `actual_yield` float DEFAULT NULL,
  `yield_unit` varchar(50) DEFAULT NULL,
  `season` varchar(100) DEFAULT NULL,
  `farming_method` varchar(100) DEFAULT NULL,
  `expected_income` float DEFAULT NULL,
  `actual_income` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crops`
--

INSERT INTO `crops` (`id`, `farmer_id`, `crop_name`, `crop_type`, `area_planted`, `planting_date`, `expected_harvest_date`, `actual_harvest_date`, `expected_yield`, `actual_yield`, `yield_unit`, `season`, `farming_method`, `expected_income`, `actual_income`) VALUES
(1, 1, 'Maize', 'Fruit', 2.5, '2025-05-08', '2025-07-16', '2025-07-23', 1500, 1800, 'Kg', 'Season C', 'Organic', 7500000, 900000);

-- --------------------------------------------------------

--
-- Table structure for table `farmer_profiles`
--

CREATE TABLE `farmer_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `sector` varchar(100) DEFAULT NULL,
  `cell` varchar(100) DEFAULT NULL,
  `bank_account` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer_profiles`
--

INSERT INTO `farmer_profiles` (`id`, `user_id`, `id_number`, `district`, `sector`, `cell`, `bank_account`, `profile_image`) VALUES
(1, 5, '1200956565666673', 'gasabo', 'kacyiru', 'kibaza', '20012008776', '');

-- --------------------------------------------------------

--
-- Table structure for table `financial_institutions`
--

CREATE TABLE `financial_institutions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `institution_name` varchar(200) DEFAULT NULL,
  `institution_type` enum('bank','microfinance','cooperative','other') DEFAULT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `office_location` varchar(200) DEFAULT NULL,
  `min_loan_amount` float DEFAULT NULL,
  `max_loan_amount` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_institutions`
--

INSERT INTO `financial_institutions` (`id`, `user_id`, `institution_name`, `institution_type`, `license_number`, `contact_person`, `office_location`, `min_loan_amount`, `max_loan_amount`) VALUES
(1, 3, 'King1', 'bank', 'ASD', '0789000990', 'Muhanga', 500000, 10000000),
(2, 6, 'I&M Bank', 'bank', '123', '0791741573', 'Kigali', 1000000, 100000000);

-- --------------------------------------------------------

--
-- Table structure for table `harvests`
--

CREATE TABLE `harvests` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `crop_name` varchar(100) DEFAULT NULL,
  `date_harvested` date DEFAULT NULL,
  `yield` float DEFAULT NULL,
  `yield_unit` varchar(50) DEFAULT NULL,
  `price_kg` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `livestock`
--

CREATE TABLE `livestock` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `animal_type` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `value_all_animals` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `livestock`
--

INSERT INTO `livestock` (`id`, `farmer_id`, `animal_type`, `quantity`, `value_all_animals`) VALUES
(1, 1, 'Goats', 5, 2000000),
(2, 5, 'Pigs', 10, 1000);

-- --------------------------------------------------------

--
-- Table structure for table `loan_interest_calculations`
--

CREATE TABLE `loan_interest_calculations` (
  `id` int(11) NOT NULL,
  `financial_institutions_id` int(11) DEFAULT NULL,
  `min_amount` decimal(10,2) NOT NULL,
  `max_amount` decimal(10,2) NOT NULL,
  `interest_rate_permonth` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_interest_calculations`
--

INSERT INTO `loan_interest_calculations` (`id`, `financial_institutions_id`, `min_amount`, `max_amount`, `interest_rate_permonth`, `created_at`) VALUES
(1, 1, 500000.00, 1000000.00, 12.00, '2025-07-08 16:26:31'),
(2, 1, 1000000.00, 2000000.00, 13.00, '2025-07-08 16:27:26'),
(3, 1, 1000000.00, 2000000.00, 13.00, '2025-07-08 16:33:08');

-- --------------------------------------------------------

--
-- Table structure for table `loan_requests`
--

CREATE TABLE `loan_requests` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `institution_id` int(11) DEFAULT NULL,
  `loan_type` varchar(100) DEFAULT NULL,
  `amount_requested` float DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `collateral_type` varchar(100) DEFAULT NULL,
  `collateral_value` float DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `amount_approved` float DEFAULT NULL,
  `interest_rate` float DEFAULT NULL,
  `total_repaid` float DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `monthly_payment` float DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `amount_to_pay` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_requests`
--

INSERT INTO `loan_requests` (`id`, `farmer_id`, `institution_id`, `loan_type`, `amount_requested`, `purpose`, `collateral_type`, `collateral_value`, `status`, `amount_approved`, `interest_rate`, `total_repaid`, `rejection_reason`, `monthly_payment`, `approval_date`, `amount_to_pay`) VALUES
(1, 1, 1, 'cee', 1000000, 'buy goat', 'dd333', 12000000, 'rejected', NULL, NULL, NULL, 'jjjdhh', NULL, NULL, NULL),
(2, 1, 1, 'Crop Production', 600000, 'hehrbjh', 'Land Title', 1000000, 'pending', NULL, NULL, NULL, NULL, 0, NULL, 0),
(3, 1, 1, 'Equipment Purchase', 700000, 'ghjb', 'Bank Guarantee', 1000000, 'pending', NULL, 12, NULL, NULL, 84000, NULL, 1708000),
(4, 1, 1, 'Equipment Purchase', 1500000, 'ghdjk', 'Harvest/Crops', 600000, 'approved', 1500000, 13, NULL, NULL, 195000, '2025-07-09', 3840000),
(5, 5, 1, 'Land Improvement', 500000, 'hh', 'Farm Equipment', 100000000, 'approved', 500000, 12, NULL, NULL, 60000, '2025-07-09', 1220000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('farmer','institution','admin') DEFAULT 'farmer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'teta', 'teta@gmail.com', '$2y$10$JS6ON6eGlt9hNELmbbVGWelsdzmXNs5y72.JaAa1ZikWQBY4pcoWm', 'farmer'),
(2, 'admin', 'admin@gmail.com', '$2y$10$JS6ON6eGlt9hNELmbbVGWelsdzmXNs5y72.JaAa1ZikWQBY4pcoWm', 'admin'),
(3, 'King1', 'king1@gmail.com', '$2y$10$DmB8zd5nkF0dttc6RuTsEuYzMrAV/ydhmj1QAAMqV3Rs6cbHduRgS', 'institution'),
(4, 'kazungu', 'kazungu@gmail.com', '$2y$10$9dxRu6Aw5Tyq6BhwWdrWeO/pyD8/IlMbrgN.S.bVCOqwEBBQWDF0i', 'farmer'),
(5, 'stacey', 'tetaa@gmail.com', '$2y$10$1FpeX6t.YE1LO8h2SlcfT.rgpbmRCMD36PsEsJYsuvEQmPEhr7S/G', 'farmer'),
(6, 'I&M', 'i&mbank@gmail.com', '$2y$10$vQD.jYWO.Q.mOfhUkaxrP.qbHcj3S3hzdSbhf/kTwjBn4weYH9WaK', 'institution');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `crops`
--
ALTER TABLE `crops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `financial_institutions`
--
ALTER TABLE `financial_institutions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `harvests`
--
ALTER TABLE `harvests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `livestock`
--
ALTER TABLE `livestock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `loan_interest_calculations`
--
ALTER TABLE `loan_interest_calculations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_requests`
--
ALTER TABLE `loan_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `institution_id` (`institution_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `crops`
--
ALTER TABLE `crops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `financial_institutions`
--
ALTER TABLE `financial_institutions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `harvests`
--
ALTER TABLE `harvests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `livestock`
--
ALTER TABLE `livestock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_interest_calculations`
--
ALTER TABLE `loan_interest_calculations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loan_requests`
--
ALTER TABLE `loan_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `crops`
--
ALTER TABLE `crops`
  ADD CONSTRAINT `crops_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  ADD CONSTRAINT `farmer_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `financial_institutions`
--
ALTER TABLE `financial_institutions`
  ADD CONSTRAINT `financial_institutions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `harvests`
--
ALTER TABLE `harvests`
  ADD CONSTRAINT `harvests_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `livestock`
--
ALTER TABLE `livestock`
  ADD CONSTRAINT `livestock_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `loan_requests`
--
ALTER TABLE `loan_requests`
  ADD CONSTRAINT `loan_requests_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `loan_requests_ibfk_2` FOREIGN KEY (`institution_id`) REFERENCES `financial_institutions` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Farm Tasks Table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE,
    status ENUM('pending','done') DEFAULT 'pending',
    related_crop_id INT DEFAULT NULL,
    related_livestock_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id),
    FOREIGN KEY (related_crop_id) REFERENCES crops(id),
    FOREIGN KEY (related_livestock_id) REFERENCES livestock(id)
);
