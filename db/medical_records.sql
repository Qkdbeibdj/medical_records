-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2025 at 01:13 PM
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
-- Database: `medical_records`
--

-- --------------------------------------------------------

--
-- Table structure for table `medical_certificate`
--

CREATE TABLE `medical_certificate` (
  `certificate_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `certificate_file` varchar(255) DEFAULT NULL,
  `issued_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `bp` varchar(10) DEFAULT NULL,
  `hr` varchar(10) DEFAULT NULL,
  `rr` varchar(10) DEFAULT NULL,
  `temp` varchar(10) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `assessment` text DEFAULT NULL,
  `o2_sat` varchar(10) DEFAULT NULL,
  `lungs_findings` text DEFAULT NULL,
  `heart_findings` text DEFAULT NULL,
  `bones_findings` text DEFAULT NULL,
  `impression` text DEFAULT NULL,
  `hearing_normal` enum('Yes','No') DEFAULT NULL,
  `hearing_referral` enum('Yes','No') DEFAULT NULL,
  `hearing_reason` text DEFAULT NULL,
  `drug_test_positive` enum('Yes','No') DEFAULT NULL,
  `drug_test_negative` enum('Yes','No') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_certificate`
--

INSERT INTO `medical_certificate` (`certificate_id`, `student_id`, `status`, `certificate_file`, `issued_date`, `bp`, `hr`, `rr`, `temp`, `blood_type`, `assessment`, `o2_sat`, `lungs_findings`, `heart_findings`, `bones_findings`, `impression`, `hearing_normal`, `hearing_referral`, `hearing_reason`, `drug_test_positive`, `drug_test_negative`) VALUES
(53, 120, 'Approved', 'uploads/certificates/medical_certificate_120_1745324564.pdf', '2025-04-22 12:22:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `physicians`
--

CREATE TABLE `physicians` (
  `physician_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` varchar(10) NOT NULL DEFAULT 'Unknown',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `course` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_number`, `name`, `email`, `contact_number`, `birthdate`, `address`, `age`, `sex`, `created_at`, `course`) VALUES
(120, '34465', 'Mark Ian', 'mark@example.com', '0922222222', '2002-04-04', 'gais', 21, 'Male', '2025-04-15 03:51:10', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('physician','student','dean') DEFAULT 'physician',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `role`, `created_at`, `student_id`) VALUES
(2, 'Smith', 'smith@gmail.com', '$2y$10$uxKSekMduJBIy9vh5BR6muQx4KXTn5Zq98qibZFNQCZagkHVu5Ita', 'physician', '2025-03-16 04:03:32', NULL),
(3, 'Agapito', 'aga@example.com', '$2y$10$iCXFBfYJ1cb4MtcFCLB3JO3ntHbvtYXooGZwXyQIK55W1sMRzMY8y', 'dean', '2025-03-16 04:03:32', NULL),
(177, 'Mark Ian', 'mark@example.com', '$2y$10$D28DazPm6q7zHInWFZeFQueqnCrnIv8ebzqFMxFfZgGFyKSyH7jwK', 'student', '2025-04-15 03:51:10', 120);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `medical_certificate`
--
ALTER TABLE `medical_certificate`
  ADD PRIMARY KEY (`certificate_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `physicians`
--
ALTER TABLE `physicians`
  ADD PRIMARY KEY (`physician_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD KEY `fk_users_student` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `medical_certificate`
--
ALTER TABLE `medical_certificate`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `physicians`
--
ALTER TABLE `physicians`
  MODIFY `physician_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `medical_certificate`
--
ALTER TABLE `medical_certificate`
  ADD CONSTRAINT `medical_certificate_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `physicians`
--
ALTER TABLE `physicians`
  ADD CONSTRAINT `physicians_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
