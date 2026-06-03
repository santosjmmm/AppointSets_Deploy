-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2026 at 04:16 AM
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
-- Database: `db_appsets`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `admin_id` int(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `email` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `contact_num` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`admin_id`, `name`, `email`, `password`, `contact_num`) VALUES
(1, 'JM Santos', 'jmsantos@pormaran', 'jmpormaran', 954749574);

-- --------------------------------------------------------

--
-- Table structure for table `tb_appointment`
--

CREATE TABLE `tb_appointment` (
  `appointment_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time(6) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `status` enum('Completed','Incomplete','Cancelled') NOT NULL DEFAULT 'Incomplete',
  `notes` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_appointment`
--

INSERT INTO `tb_appointment` (`appointment_id`, `date`, `time`, `patient_id`, `dentist_id`, `service_id`, `status`, `notes`) VALUES
(14, '2026-04-30', '02:00:00.000000', 22, 1, 2, 'Completed', ''),
(15, '2026-05-14', '10:30:00.000000', 22, 1, 2, 'Incomplete', '');

-- --------------------------------------------------------

--
-- Table structure for table `tb_dentist`
--

CREATE TABLE `tb_dentist` (
  `dentist_id` int(20) NOT NULL,
  `dentist_name` varchar(20) NOT NULL,
  `contact_num` int(11) NOT NULL,
  `email` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `avail` set('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_dentist`
--

INSERT INTO `tb_dentist` (`dentist_id`, `dentist_name`, `contact_num`, `email`, `password`, `status`, `avail`) VALUES
(1, 'Dr. Calimbahin', 978454481, 'dezza@beltran', 'beltrandezza', 'Active', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');

-- --------------------------------------------------------

--
-- Table structure for table `tb_patient`
--

CREATE TABLE `tb_patient` (
  `patient_id` int(20) NOT NULL,
  `name` char(30) NOT NULL,
  `age` int(2) NOT NULL,
  `address` varchar(50) NOT NULL,
  `email` varchar(20) NOT NULL,
  `contact_num` int(11) NOT NULL,
  `points` int(5) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_patient`
--

INSERT INTO `tb_patient` (`patient_id`, `name`, `age`, `address`, `email`, `contact_num`, `points`, `password`) VALUES
(17, 'Allen Dulay', 3, 'bacoor', 'Allen@dulay', 91813841, 0, '$2y$10$IyNFcKCAyyb/s'),
(19, 'Derod Andrelou', 4, 'Imus', 'andrelou@16', 2147483647, 0, '$2y$10$BHNgqK0MG4m5/'),
(20, 'JM Santos', 21, 'Bacoor', 'Santos@JM', 91683184, 0, '$2y$10$.SfNFUY/qo3Fb'),
(22, 'Jessie Chris Santos', 21, 'Las Pinas', 'Jessie@Chris', 91384311, 0, '$2y$10$LLHz31vmE2s3vsFPaT9g2OkQXnE73gepF.PddOLRW1.OYfKQtfLwu');

-- --------------------------------------------------------

--
-- Table structure for table `tb_service`
--

CREATE TABLE `tb_service` (
  `service_id` int(20) NOT NULL,
  `service_name` varchar(20) NOT NULL,
  `service_image` varchar(255) NOT NULL,
  `description` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `duration` time DEFAULT NULL,
  `status` enum('Available','Unavailable') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_service`
--

INSERT INTO `tb_service` (`service_id`, `service_name`, `service_image`, `description`, `price`, `duration`, `status`) VALUES
(1, 'Cleaning', '1774506285_cleaning.jpg', 'This is for cleaning teeth', 500, '00:30:00', 'Available'),
(2, 'Orthodontics', '1774504566_ortho.jpg', 'This is Orthodontics', 350, '01:30:00', 'Available'),
(3, 'Endodontics', '1774614847_endo.jpg', 'This is Endodontic', 150, '01:00:00', 'Available'),
(4, 'Dentures', '1774683484_dentures.jpg', 'This is Dentures', 2500, '03:00:00', 'Available'),
(5, 'Extraction', '1774688881_extraction.jpg', 'This is extraction', 1000, '01:00:00', 'Available'),
(11, 'Restorations', '1776498795_restorative.jpg', 'This is restoration foe teeth', 1500, '01:00:00', 'Unavailable');

-- --------------------------------------------------------

--
-- Table structure for table `tb_staff`
--

CREATE TABLE `tb_staff` (
  `staff_id` int(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `email` varchar(20) NOT NULL,
  `contact_num` int(11) NOT NULL,
  `password` varchar(20) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_staff`
--

INSERT INTO `tb_staff` (`staff_id`, `name`, `email`, `contact_num`, `password`, `status`) VALUES
(1, 'JC Santos', 'jc@santos', 978456123, 'santosjc', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `password` (`password`);

--
-- Indexes for table `tb_appointment`
--
ALTER TABLE `tb_appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `tb_dentist`
--
ALTER TABLE `tb_dentist`
  ADD PRIMARY KEY (`dentist_id`),
  ADD UNIQUE KEY `contact_num` (`contact_num`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tb_patient`
--
ALTER TABLE `tb_patient`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`) USING BTREE;

--
-- Indexes for table `tb_service`
--
ALTER TABLE `tb_service`
  ADD PRIMARY KEY (`service_id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- Indexes for table `tb_staff`
--
ALTER TABLE `tb_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `contact_num` (`contact_num`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `admin_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_appointment`
--
ALTER TABLE `tb_appointment`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tb_dentist`
--
ALTER TABLE `tb_dentist`
  MODIFY `dentist_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_patient`
--
ALTER TABLE `tb_patient`
  MODIFY `patient_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tb_service`
--
ALTER TABLE `tb_service`
  MODIFY `service_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tb_staff`
--
ALTER TABLE `tb_staff`
  MODIFY `staff_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_appointment`
--
ALTER TABLE `tb_appointment`
  ADD CONSTRAINT `tb_appointment_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `tb_patient` (`patient_id`),
  ADD CONSTRAINT `tb_appointment_ibfk_2` FOREIGN KEY (`dentist_id`) REFERENCES `tb_dentist` (`dentist_id`),
  ADD CONSTRAINT `tb_appointment_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `tb_service` (`service_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
