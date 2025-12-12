-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 12, 2025 at 11:33 AM
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
-- Database: `interactive-umair_db`
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
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `agent_business_resources`
--

CREATE TABLE `agent_business_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `type` enum('idea','template','market_research','startup_resource','financial_resource') NOT NULL,
  `resource_type` enum('plan','framework','opportunity') DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `category_id` int(11) DEFAULT NULL,
  `agent_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_business_resources`
--

INSERT INTO `agent_business_resources` (`id`, `title`, `description`, `file_url`, `amount`, `source`, `link`, `type`, `resource_type`, `status`, `category_id`, `agent_id`, `created_at`, `updated_at`) VALUES
(1, 'Eco-Friendly Packaging Idea', 'A sustainable packaging business idea focusing on biodegradable materials.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', NULL, NULL, NULL, 'idea', NULL, 'draft', 2, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11'),
(2, 'Organic Food Delivery Service', 'A startup concept for delivering organic meals locally.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', NULL, NULL, NULL, 'idea', NULL, 'published', 3, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11'),
(3, 'Restaurant Business Template', 'Template for planning and running a small restaurant, including finances and operations.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', NULL, NULL, NULL, 'template', NULL, 'draft', 4, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11'),
(4, 'E-Commerce Store Template', 'Template for an online store selling handmade crafts, with inventory management and marketing guide.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', NULL, NULL, NULL, 'template', NULL, 'published', 5, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11'),
(5, 'Local Market Research Report', 'Market analysis of local demand for eco-friendly products and small-scale manufacturing opportunities.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', NULL, NULL, NULL, 'market_research', NULL, 'draft', 6, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11'),
(6, 'Startup Business Plan - Organic Juice', 'Complete business plan for a startup selling cold-pressed organic juices.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', NULL, NULL, NULL, 'startup_resource', 'plan', 'draft', 7, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11'),
(7, 'Startup Framework - Online Tutoring', 'Framework for setting up an online tutoring platform including tech stack and marketing plan.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', NULL, NULL, NULL, 'startup_resource', 'framework', 'published', 8, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11'),
(8, 'Opportunity - Urban Farming', 'Exploration of business opportunity in urban vertical farming with potential investors.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', NULL, NULL, NULL, 'startup_resource', 'opportunity', 'draft', 9, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11'),
(9, 'Small Business Loan Guide', 'Guide for applying and obtaining small business loans from banks and microfinance institutions.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', 5000.00, 'Bank of Pakistan', 'https://bankofpakistan.example.com', 'financial_resource', NULL, 'draft', 10, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11'),
(10, 'Investor Pitch Deck Template', 'Template for preparing investor pitch decks including financial projections and market analysis.', 'uploads/resources/1765530484_Fall 2025_CS619_11713_2 (1).docx', 10000.00, 'Private Investor', 'https://investor.example.com', 'financial_resource', NULL, 'published', 11, 1, '2025-12-12 09:12:11', '2025-12-12 09:12:11');

-- --------------------------------------------------------

--
-- Table structure for table `agent_profiles`
--

CREATE TABLE `agent_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expertise` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_profiles`
--

INSERT INTO `agent_profiles` (`id`, `user_id`, `expertise`, `bio`, `company_name`, `website`, `profile_picture`, `linkedin`, `experience_years`) VALUES
(1, 1, 'XYZ', 'XYZ', 'Miniclip ', 'www.mininclip.com', 'uploads/profile_pictures/1765519012_App.jpg', 'http://localhost/fall25/interactive-umair/agent/profile.php', 2);

-- --------------------------------------------------------

--
-- Table structure for table `business_categories`
--

CREATE TABLE `business_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_categories`
--

INSERT INTO `business_categories` (`id`, `name`, `description`) VALUES
(2, 'Technology', 'Companies providing technology products and services'),
(3, 'Healthcare', 'Healthcare providers, medical services, and products'),
(4, 'Finance', 'Banks, investment firms, and financial services'),
(5, 'Education', 'Schools, colleges, and educational services'),
(6, 'Hospitality', 'Hotels, restaurants, and travel services'),
(7, 'Retail', 'Shops, e-commerce platforms, and consumer goods'),
(8, 'Real Estate', 'Property development, agencies, and management'),
(9, 'Marketing', 'Advertising, branding, and digital marketing agencies'),
(10, 'Entertainment', 'Media, movies, music, and recreational services'),
(11, 'Transportation', 'Logistics, transport services, and delivery solutions');

-- --------------------------------------------------------

--
-- Table structure for table `educational_resources`
--

CREATE TABLE `educational_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `type` enum('tutorial','event') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `educational_resources`
--

INSERT INTO `educational_resources` (`id`, `title`, `description`, `content`, `video_url`, `event_date`, `location`, `link`, `type`, `created_at`, `updated_at`) VALUES
(2, 'Startup Basics', 'Introduction to starting a business', 'This tutorial covers the basics of starting a business...', 'uploads/tutorials/startup_basics.mp4', NULL, NULL, NULL, 'tutorial', '2025-12-12 09:43:36', '2025-12-12 09:43:36'),
(3, 'Marketing 101', 'Learn marketing fundamentals', 'This tutorial focuses on marketing strategies for startups...', 'uploads/tutorials/marketing_101.mp4', NULL, NULL, NULL, 'tutorial', '2025-12-12 09:43:36', '2025-12-12 09:43:36'),
(4, 'Financial Planning', 'Manage your finances', 'Tutorial on budgeting, financial planning, and managing expenses...', 'uploads/tutorials/financial_planning.mp4', NULL, NULL, NULL, 'tutorial', '2025-12-12 09:43:36', '2025-12-12 09:43:36'),
(5, 'Business Plan Writing', 'How to write a business plan', 'Step by step tutorial for writing a business plan...', 'uploads/tutorials/business_plan.mp4', NULL, NULL, NULL, 'tutorial', '2025-12-12 09:43:36', '2025-12-12 09:43:36'),
(6, 'Tech Tools for Startups', 'Useful software for startups', 'Tutorial on essential tech tools and software for new businesses...', 'uploads/tutorials/tech_tools.mp4', NULL, NULL, NULL, 'tutorial', '2025-12-12 09:43:36', '2025-12-12 09:43:36'),
(7, 'Webinar on Funding', 'Learn about funding opportunities', NULL, NULL, '2025-12-20 15:00:00', 'Zoom', 'https://zoom.us/funding_webinar', 'event', '2025-12-12 09:43:36', '2025-12-12 09:43:36'),
(8, 'Entrepreneurship Q&A', 'Ask experienced entrepreneurs', NULL, NULL, '2025-12-22 17:00:00', 'Google Meet', 'https://meet.google.com/entrepreneur_qa', 'event', '2025-12-12 09:43:36', '2025-12-12 09:43:36'),
(9, 'Digital Marketing Workshop', 'Hands-on marketing session', NULL, NULL, '2025-12-25 14:00:00', 'Online', 'https://webinarlink.com/digital_marketing', 'event', '2025-12-12 09:43:36', '2025-12-12 09:43:36'),
(10, 'Pitch Deck Essentials', 'Creating a compelling pitch deck', 'Tutorial covering the key elements of a pitch deck...', 'uploads/tutorials/pitch_deck.mp4', NULL, NULL, NULL, 'tutorial', '2025-12-12 09:43:36', '2025-12-12 09:43:36'),
(11, 'Investor Networking Event', 'Meet potential investors', NULL, NULL, '2025-12-30 16:00:00', 'Startup Hub', 'https://eventlink.com/investor_networking', 'event', '2025-12-12 09:43:36', '2025-12-12 09:43:36');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `agent_id`, `subject`, `message`, `created_at`) VALUES
(1, 2, 1, 'CS606', 'fdsad', '2025-12-12 09:19:51');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`id`, `resource_id`, `message`, `user_id`, `created_at`) VALUES
(1, 10, 'fsdfsd', 2, '2025-12-12 09:23:23');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `status`, `created_at`) VALUES
(1, 2, 1, 'hy', 'unread', '2025-12-12 09:29:48'),
(2, 1, 2, 'Yes', 'unread', '2025-12-12 09:32:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('user','agent') NOT NULL DEFAULT 'user',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'agent1', 'agent@gmail.com', '$2y$10$J.iFk4A/baBidq8.COsfl.F/C7drv5Bq6WxG27VNDrEHcELwutUfC', 'Agent Nasir', 'agent', 'approved', '2025-12-12 05:43:35', '2025-12-12 05:45:46'),
(2, 'user1', 'user@gmail.com', '$2y$10$rhTFmyGxMu00oMNpedMZ2uRyb3Z3LCYCD5npA.Zewt1HK2HweQf/G', 'NASIR ABBAS', 'user', 'approved', '2025-12-12 05:44:01', '2025-12-12 05:45:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `agent_business_resources`
--
ALTER TABLE `agent_business_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `agent_profiles`
--
ALTER TABLE `agent_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `business_categories`
--
ALTER TABLE `business_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `educational_resources`
--
ALTER TABLE `educational_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agent_business_resources`
--
ALTER TABLE `agent_business_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `agent_profiles`
--
ALTER TABLE `agent_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `business_categories`
--
ALTER TABLE `business_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `educational_resources`
--
ALTER TABLE `educational_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agent_business_resources`
--
ALTER TABLE `agent_business_resources`
  ADD CONSTRAINT `agent_business_resources_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `business_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `agent_business_resources_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agent_profiles`
--
ALTER TABLE `agent_profiles`
  ADD CONSTRAINT `agent_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `agent_business_resources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
