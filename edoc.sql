-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 19, 2026 at 06:30 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edoc`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `aemail` varchar(255) NOT NULL,
  `apassword` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`aemail`, `apassword`, `profile_image`) VALUES
('admin@edoc.com', '123', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appoid` int(11) NOT NULL,
  `pid` int(10) DEFAULT NULL,
  `apponum` int(3) DEFAULT NULL,
  `scheduleid` int(10) DEFAULT NULL,
  `appodate` date DEFAULT NULL,
  `slot_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appoid`, `pid`, `apponum`, `scheduleid`, `appodate`, `slot_id`) VALUES
(8, 1, 13, 9, '2026-04-19', NULL),
(9, 1, 41, 11, '2026-04-19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `docid` int(11) NOT NULL,
  `docemail` varchar(255) DEFAULT NULL,
  `docname` varchar(255) DEFAULT NULL,
  `docpassword` varchar(255) DEFAULT NULL,
  `docnic` varchar(15) DEFAULT NULL,
  `doctel` varchar(15) DEFAULT NULL,
  `specialties` int(2) DEFAULT NULL,
  `doc_max_tokens` int(11) DEFAULT 20,
  `doc_slot_duration` int(11) DEFAULT 15,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`docid`, `docemail`, `docname`, `docpassword`, `docnic`, `doctel`, `specialties`, `doc_max_tokens`, `doc_slot_duration`, `profile_image`, `created_at`) VALUES
(1, 'doctor@edoc.com', 'Test Doctor', '123', '000000000', '0110000000', 1, 20, 15, 'img/uploads/doctor_1_1776597171.jpg', '2026-02-18 11:44:13'),
(2, 'ramyadav@mediciti.com', 'Ram Yadav', '123', '1234', '9809800989', 1, 15, 10, NULL, '2025-11-19 11:44:13'),
(3, 'doctor1@example.com', 'Ramesh Sharma', '123', '111222001', '0112340001', 3, 20, 15, NULL, '2025-10-30 11:44:13'),
(4, 'doctor2@example.com', 'Bikash Thapa', '123', '111222002', '0112340002', 19, 20, 15, NULL, '2026-01-12 11:44:13'),
(5, 'doctor3@example.com', 'Suman Shrestha', '123', '111222003', '0112340003', 38, 20, 15, NULL, '2025-11-07 11:44:13'),
(6, 'doctor4@example.com', 'Roshan Karki', '123', '111222004', '0112340004', 3, 20, 15, NULL, '2026-01-06 11:44:13'),
(7, 'doctor5@example.com', 'Sushil Joshi', '123', '111222005', '0112340005', 14, 20, 15, NULL, '2026-01-10 11:44:13'),
(8, 'doctor6@example.com', 'Binod Khadka', '123', '111222006', '0112340006', 1, 20, 15, NULL, '2025-11-16 11:44:13'),
(9, 'doctor7@example.com', 'Nabin Magar', '123', '111222007', '0112340007', 16, 20, 15, NULL, '2026-01-20 11:44:13'),
(10, 'doctor8@example.com', 'Kiran Gurung', '123', '111222008', '0112340008', 45, 20, 15, NULL, '2025-12-11 11:44:13'),
(11, 'doctor9@example.com', 'Dinesh Adhikari', '123', '111222009', '0112340009', 28, 20, 15, NULL, '2026-01-17 11:44:13'),
(12, 'doctor10@example.com', 'Pradeep Rai', '123', '111222010', '0112340010', 32, 20, 15, NULL, '2025-12-18 11:44:13'),
(13, 'doctor11@example.com', 'Sagar Poudel', '123', '111222011', '0112340011', 1, 20, 15, NULL, '2026-03-01 11:44:13'),
(14, 'doctor12@example.com', 'Anil Basnet', '123', '111222012', '0112340012', 45, 20, 15, NULL, '2026-01-27 11:44:13'),
(15, 'doctor13@example.com', 'Rajan Lama', '123', '111222013', '0112340013', 2, 20, 15, NULL, '2026-03-15 11:44:13'),
(16, 'doctor14@example.com', 'Gopal Bhattarai', '123', '111222014', '0112340014', 23, 20, 15, NULL, '2025-11-29 11:44:13'),
(17, 'doctor15@example.com', 'Suraj Tamang', '123', '111222015', '0112340015', 29, 20, 15, NULL, '2026-02-25 11:44:13'),
(18, 'doctor16@example.com', 'Ram Bista', '123', '111222016', '0112340016', 18, 20, 15, NULL, '2026-03-02 11:44:13'),
(19, 'doctor17@example.com', 'Hari Dahal', '123', '111222017', '0112340017', 16, 20, 15, NULL, '2026-01-24 11:44:13'),
(20, 'doctor18@example.com', 'Prakash Neupane', '123', '111222018', '0112340018', 19, 20, 15, NULL, '2025-12-02 11:44:13'),
(21, 'doctor19@example.com', 'Rajesh Ghimire', '123', '111222019', '0112340019', 23, 20, 15, NULL, '2025-11-09 11:44:13'),
(22, 'doctor20@example.com', 'Dipendra Khatri', '123', '111222020', '0112340020', 12, 20, 15, NULL, '2026-01-14 11:44:13'),
(23, 'doctor21@example.com', 'Sunita Rai', '123', '111222021', '0112340021', 38, 20, 15, NULL, '2026-02-27 11:44:13'),
(24, 'doctor22@example.com', 'Sabina Shrestha', '123', '111222022', '0112340022', 35, 20, 15, NULL, '2026-02-12 11:44:13'),
(25, 'doctor23@example.com', 'Pooja Thapa', '123', '111222023', '0112340023', 2, 20, 15, NULL, '2025-10-22 11:44:13'),
(26, 'doctor24@example.com', 'Anju Karki', '123', '111222024', '0112340024', 13, 20, 15, NULL, '2025-11-24 11:44:13'),
(27, 'doctor25@example.com', 'Sita Sharma', '123', '111222025', '0112340025', 45, 20, 15, NULL, '2025-12-17 11:44:13'),
(28, 'doctor26@example.com', 'Saraswati Gurung', '123', '111222026', '0112340026', 10, 20, 15, NULL, '2026-02-27 11:44:13'),
(29, 'doctor27@example.com', 'Gita Magar', '123', '111222027', '0112340027', 14, 20, 15, NULL, '2025-12-01 11:44:13'),
(30, 'doctor28@example.com', 'Nita Khadka', '123', '111222028', '0112340028', 38, 20, 15, NULL, '2025-12-03 11:44:13'),
(31, 'doctor29@example.com', 'Sangita Lama', '123', '111222029', '0112340029', 18, 20, 15, NULL, '2025-10-30 11:44:13'),
(32, 'doctor30@example.com', 'Bimala Adhikari', '123', '111222030', '0112340030', 47, 20, 15, NULL, '2025-12-07 11:44:13'),
(33, 'doctor31@example.com', 'Nisha Poudel', '123', '111222031', '0112340031', 2, 20, 15, NULL, '2025-10-30 11:44:13'),
(34, 'doctor32@example.com', 'Manisha Joshi', '123', '111222032', '0112340032', 10, 20, 15, NULL, '2026-01-06 11:44:13'),
(35, 'doctor33@example.com', 'Sushma Basnet', '123', '111222033', '0112340033', 18, 20, 15, NULL, '2025-10-30 11:44:13'),
(36, 'doctor34@example.com', 'Asmita Bhattarai', '123', '111222034', '0112340034', 10, 20, 15, NULL, '2026-01-29 11:44:13'),
(37, 'doctor35@example.com', 'Pratima Tamang', '123', '111222035', '0112340035', 18, 20, 15, NULL, '2025-11-07 11:44:13'),
(38, 'doctor36@example.com', 'Rina Bista', '123', '111222036', '0112340036', 45, 20, 15, NULL, '2025-11-14 11:44:13'),
(39, 'doctor37@example.com', 'Shobha Dahal', '123', '111222037', '0112340037', 28, 20, 15, NULL, '2026-01-28 11:44:13'),
(40, 'doctor38@example.com', 'Priyanka Neupane', '123', '111222038', '0112340038', 28, 20, 15, NULL, '2026-04-11 11:44:13'),
(41, 'doctor39@example.com', 'Karishma Ghimire', '123', '111222039', '0112340039', 10, 20, 15, NULL, '2026-01-29 11:44:13'),
(42, 'doctor40@example.com', 'Anushka Khatri', '123', '111222040', '0112340040', 47, 20, 15, NULL, '2025-11-23 11:44:13');

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `pid` int(11) NOT NULL,
  `pemail` varchar(255) DEFAULT NULL,
  `pname` varchar(255) DEFAULT NULL,
  `ppassword` varchar(255) DEFAULT NULL,
  `paddress` varchar(255) DEFAULT NULL,
  `pnic` varchar(15) DEFAULT NULL,
  `pdob` date DEFAULT NULL,
  `ptel` varchar(15) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`pid`, `pemail`, `pname`, `ppassword`, `paddress`, `pnic`, `pdob`, `ptel`, `profile_image`, `created_at`) VALUES
(1, 'patient@edoc.com', 'Test Patient', '123', 'Sri Lanka', '0000000000', '2000-01-01', '0120000000', 'img/uploads/patient_1_1776597398.png', '2026-02-10 11:44:13'),
(2, 'emhashenudara@gmail.com', 'Hashen Udara', '123', 'Sri Lanka', '0110000000', '2022-06-03', '0700000000', NULL, '2025-11-14 11:44:13'),
(3, 'test1@test.com', 'Test1 Test1', '123', 'test1@test.com', '12', '2026-04-07', '0712342343', NULL, '2025-12-27 11:44:13');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `appoid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `docid` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `scheduleid` int(11) NOT NULL,
  `docid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL,
  `nop` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`scheduleid`, `docid`, `title`, `scheduledate`, `scheduletime`, `nop`) VALUES
(2, '1', '1', '2022-06-10', '20:36:00', 1),
(3, '1', '12', '2022-06-10', '20:33:00', 1),
(4, '1', '1', '2022-06-10', '12:32:00', 1),
(6, '1', '12', '2022-06-10', '20:35:00', 1),
(7, '1', '1', '2022-06-24', '20:36:00', 1),
(8, '1', '12', '2022-06-10', '13:33:00', 1),
(9, '2', 'Allergries', '2026-05-03', '12:09:00', 15),
(10, '2', 'Allegris', '2026-04-22', '15:10:00', 15),
(11, '2', 'Allergies', '2026-04-30', '07:30:00', 50);

-- --------------------------------------------------------

--
-- Table structure for table `specialties`
--

CREATE TABLE `specialties` (
  `id` int(2) NOT NULL,
  `sname` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `specialties`
--

INSERT INTO `specialties` (`id`, `sname`) VALUES
(1, 'Accident and emergency medicine'),
(2, 'Allergology'),
(3, 'Anaesthetics'),
(4, 'Biological hematology'),
(5, 'Cardiology'),
(6, 'Child psychiatry'),
(7, 'Clinical biology'),
(8, 'Clinical chemistry'),
(9, 'Clinical neurophysiology'),
(10, 'Clinical radiology'),
(11, 'Dental, oral and maxillo-facial surgery'),
(12, 'Dermato-venerology'),
(13, 'Dermatology'),
(14, 'Endocrinology'),
(15, 'Gastro-enterologic surgery'),
(16, 'Gastroenterology'),
(17, 'General hematology'),
(18, 'General Practice'),
(19, 'General surgery'),
(20, 'Geriatrics'),
(21, 'Immunology'),
(22, 'Infectious diseases'),
(23, 'Internal medicine'),
(24, 'Laboratory medicine'),
(25, 'Maxillo-facial surgery'),
(26, 'Microbiology'),
(27, 'Nephrology'),
(28, 'Neuro-psychiatry'),
(29, 'Neurology'),
(30, 'Neurosurgery'),
(31, 'Nuclear medicine'),
(32, 'Obstetrics and gynecology'),
(33, 'Occupational medicine'),
(34, 'Ophthalmology'),
(35, 'Orthopaedics'),
(36, 'Otorhinolaryngology'),
(37, 'Paediatric surgery'),
(38, 'Paediatrics'),
(39, 'Pathology'),
(40, 'Pharmacology'),
(41, 'Physical medicine and rehabilitation'),
(42, 'Plastic surgery'),
(43, 'Podiatric Medicine'),
(44, 'Podiatric Surgery'),
(45, 'Psychiatry'),
(46, 'Public health and Preventive Medicine'),
(47, 'Radiology'),
(48, 'Radiotherapy'),
(49, 'Respiratory medicine'),
(50, 'Rheumatology'),
(51, 'Stomatology'),
(52, 'Thoracic surgery'),
(53, 'Tropical medicine'),
(54, 'Urology'),
(55, 'Vascular surgery'),
(56, 'Venereology');

-- --------------------------------------------------------

--
-- Table structure for table `timeslots`
--

CREATE TABLE `timeslots` (
  `slot_id` int(11) NOT NULL,
  `scheduleid` int(11) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `slot_time` time NOT NULL,
  `is_booked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webuser`
--

CREATE TABLE `webuser` (
  `email` varchar(255) NOT NULL,
  `usertype` char(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `webuser`
--

INSERT INTO `webuser` (`email`, `usertype`) VALUES
('admin@edoc.com', 'a'),
('doctor@edoc.com', 'd'),
('patient@edoc.com', 'p'),
('emhashenudara@gmail.com', 'p'),
('test1@test.com', 'p'),
('ramyadav@mediciti.com', 'd'),
('doctor1@example.com', 'd'),
('doctor2@example.com', 'd'),
('doctor3@example.com', 'd'),
('doctor4@example.com', 'd'),
('doctor5@example.com', 'd'),
('doctor6@example.com', 'd'),
('doctor7@example.com', 'd'),
('doctor8@example.com', 'd'),
('doctor9@example.com', 'd'),
('doctor10@example.com', 'd'),
('doctor11@example.com', 'd'),
('doctor12@example.com', 'd'),
('doctor13@example.com', 'd'),
('doctor14@example.com', 'd'),
('doctor15@example.com', 'd'),
('doctor16@example.com', 'd'),
('doctor17@example.com', 'd'),
('doctor18@example.com', 'd'),
('doctor19@example.com', 'd'),
('doctor20@example.com', 'd'),
('doctor21@example.com', 'd'),
('doctor22@example.com', 'd'),
('doctor23@example.com', 'd'),
('doctor24@example.com', 'd'),
('doctor25@example.com', 'd'),
('doctor26@example.com', 'd'),
('doctor27@example.com', 'd'),
('doctor28@example.com', 'd'),
('doctor29@example.com', 'd'),
('doctor30@example.com', 'd'),
('doctor31@example.com', 'd'),
('doctor32@example.com', 'd'),
('doctor33@example.com', 'd'),
('doctor34@example.com', 'd'),
('doctor35@example.com', 'd'),
('doctor36@example.com', 'd'),
('doctor37@example.com', 'd'),
('doctor38@example.com', 'd'),
('doctor39@example.com', 'd'),
('doctor40@example.com', 'd');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`aemail`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appoid`),
  ADD UNIQUE KEY `uq_patient_session` (`pid`,`scheduleid`),
  ADD UNIQUE KEY `uq_slot_booking` (`slot_id`),
  ADD UNIQUE KEY `uq_schedule_apponum` (`scheduleid`,`apponum`),
  ADD KEY `pid` (`pid`),
  ADD KEY `scheduleid` (`scheduleid`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`docid`),
  ADD KEY `specialties` (`specialties`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `uq_appo_review` (`appoid`),
  ADD KEY `idx_doctor_rating` (`docid`,`rating`),
  ADD KEY `idx_patient` (`pid`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`scheduleid`),
  ADD KEY `docid` (`docid`);

--
-- Indexes for table `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timeslots`
--
ALTER TABLE `timeslots`
  ADD PRIMARY KEY (`slot_id`),
  ADD UNIQUE KEY `uq_schedule_slot` (`scheduleid`,`slot_number`),
  ADD KEY `idx_schedule_booked` (`scheduleid`,`is_booked`);

--
-- Indexes for table `webuser`
--
ALTER TABLE `webuser`
  ADD PRIMARY KEY (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appoid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `docid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `scheduleid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `timeslots`
--
ALTER TABLE `timeslots`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `timeslots`
--
ALTER TABLE `timeslots`
  ADD CONSTRAINT `fk_ts_schedule` FOREIGN KEY (`scheduleid`) REFERENCES `schedule` (`scheduleid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
