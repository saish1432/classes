-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 08, 2025 at 05:24 AM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u261459251_classes`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT 'admin',
  `password` varchar(255) NOT NULL DEFAULT 'admin123',
  `bypass_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `bypass_token`, `created_at`) VALUES
(1, 'admin', 'admin123', NULL, '2025-08-08 04:58:41');

-- --------------------------------------------------------

--
-- Table structure for table `assigned_videos`
--

CREATE TABLE `assigned_videos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `purchase_date` timestamp NULL DEFAULT current_timestamp(),
  `expiry_date` timestamp NOT NULL,
  `status` enum('active','expired') DEFAULT 'active',
  `payment_status` enum('pending','completed','failed') DEFAULT 'completed',
  `payment_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT 'UPI',
  `transaction_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'whatsapp_number', '+919876543210', '2025-08-08 04:58:42'),
(2, 'upi_id', 'admin@paytm', '2025-08-08 04:58:42'),
(3, 'site_title', 'GT Online Class', '2025-08-08 04:58:42'),
(4, 'welcome_message', 'Welcome to the Learning World', '2025-08-08 04:58:42'),
(5, 'video_price', '99', '2025-08-08 04:58:42'),
(6, 'default_video_duration', '30', '2025-08-08 04:58:42');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `review` text NOT NULL,
  `rating` int(1) DEFAULT 5,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `mobile`, `review`, `rating`, `status`, `submitted_at`, `approved_at`, `approved_by`) VALUES
(1, 'Anita Singh', '9876543210', 'Excellent teaching methods and very helpful instructors. The online classes are well structured and easy to understand. I highly recommend GT Online Class to all students.', 5, 'approved', '2025-08-08 04:58:42', '2025-08-08 04:58:42', 1),
(2, 'Vikash Gupta', '9876543211', 'The recorded lectures are of high quality and the study material provided is comprehensive. The teachers are very supportive and always ready to help students with their queries.', 5, 'approved', '2025-08-08 04:58:42', '2025-08-08 04:58:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `top_students`
--

CREATE TABLE `top_students` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `achievement` varchar(255) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `rank` int(3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `top_students`
--

INSERT INTO `top_students` (`id`, `name`, `image`, `description`, `achievement`, `year`, `rank`, `created_at`, `status`) VALUES
(1, 'Rahul Sharma', 'https://images.pexels.com/photos/1516680/pexels-photo-1516680.jpeg?auto=compress&cs=tinysrgb&w=150&h=150&fit=crop', 'Topper in Mathematics', 'Mathematics Topper', 2024, 1, '2025-08-08 04:58:42', 'active'),
(2, 'Priya Patel', 'https://images.pexels.com/photos/1587009/pexels-photo-1587009.jpeg?auto=compress&cs=tinysrgb&w=150&h=150&fit=crop', 'Science Excellence Award', 'Science Excellence', 2024, 2, '2025-08-08 04:58:42', 'active'),
(3, 'Amit Kumar', 'https://images.pexels.com/photos/1542085/pexels-photo-1542085.jpeg?auto=compress&cs=tinysrgb&w=150&h=150&fit=crop', 'English Literature Champion', 'Literature Champion', 2024, 3, '2025-08-08 04:58:42', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registered_on` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `mobile_verified` tinyint(1) DEFAULT 0,
  `total_spent` decimal(10,2) DEFAULT 0.00,
  `referral_code` varchar(20) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password`, `registered_on`, `status`, `last_login`, `email_verified`, `mobile_verified`, `total_spent`, `referral_code`, `referred_by`) VALUES
(1, 'Vikram', 'ucworld@yahoo.com', '7767834383', '$2y$10$8Z5JMj.X3C1bfLd1lTCkLuAL/CWeNOekAjiczx9B9LGwD55Beag3.', '2025-08-08 05:15:31', 'active', NULL, 0, 0, 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `type` enum('training','recorded') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `uploaded_on` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive','processing') DEFAULT 'active',
  `views` int(11) DEFAULT 0,
  `downloads` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `tags` text DEFAULT NULL,
  `instructor` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `title`, `description`, `filename`, `file_size`, `duration`, `thumbnail`, `type`, `category`, `price`, `discount_price`, `uploaded_on`, `status`, `views`, `downloads`, `featured`, `tags`, `instructor`) VALUES
(1, 'Introduction to Physics', 'Basic concepts of physics for beginners', 'sample_video1.mp4', NULL, NULL, NULL, 'training', 'Physics', 0.00, NULL, '2025-08-08 04:58:42', 'active', 0, 0, 0, 'physics,basics,introduction', NULL),
(2, 'Advanced Mathematics', 'Complex mathematical problems and solutions', 'sample_video2.mp4', NULL, NULL, NULL, 'training', 'Mathematics', 0.00, NULL, '2025-08-08 04:58:42', 'active', 0, 0, 0, 'mathematics,advanced,problems', NULL),
(3, 'Chemistry Fundamentals', 'Essential chemistry concepts', 'sample_video3.mp4', NULL, NULL, NULL, 'training', 'Chemistry', 0.00, NULL, '2025-08-08 04:58:42', 'active', 0, 0, 0, 'chemistry,fundamentals,concepts', NULL),
(4, 'Complete Algebra Course', 'Comprehensive algebra course with practice problems', 'lecture1.mp4', NULL, NULL, NULL, 'recorded', 'Mathematics', 99.00, NULL, '2025-08-08 04:58:42', 'active', 0, 0, 1, 'algebra,mathematics,course', NULL),
(5, 'Physics Laws and Theories', 'Detailed explanation of physics laws', 'lecture2.mp4', NULL, NULL, NULL, 'recorded', 'Physics', 99.00, NULL, '2025-08-08 04:58:42', 'active', 0, 0, 1, 'physics,laws,theories', NULL),
(6, 'Organic Chemistry Mastery', 'Master organic chemistry with examples', 'lecture3.mp4', NULL, NULL, NULL, 'recorded', 'Chemistry', 99.00, NULL, '2025-08-08 04:58:42', 'active', 0, 0, 1, 'chemistry,organic,mastery', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `video_analytics`
--

CREATE TABLE `video_analytics` (
  `id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('view','download','share','like','dislike') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `assigned_videos`
--
ALTER TABLE `assigned_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `video_id` (`video_id`),
  ADD KEY `idx_expiry_status` (`expiry_date`,`status`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_approved_by` (`approved_by`);

--
-- Indexes for table `top_students`
--
ALTER TABLE `top_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_rank` (`status`,`rank`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_referred_by` (`referred_by`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_status` (`type`,`status`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `video_analytics`
--
ALTER TABLE `video_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_id` (`video_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_action_date` (`action`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assigned_videos`
--
ALTER TABLE `assigned_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `top_students`
--
ALTER TABLE `top_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `video_analytics`
--
ALTER TABLE `video_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assigned_videos`
--
ALTER TABLE `assigned_videos`
  ADD CONSTRAINT `assigned_videos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assigned_videos_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`approved_by`) REFERENCES `admin` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `video_analytics`
--
ALTER TABLE `video_analytics`
  ADD CONSTRAINT `video_analytics_ibfk_1` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_analytics_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;