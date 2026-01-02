-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2025 at 05:41 PM
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
-- Database: `real_estate`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `Admin_name` varchar(20) NOT NULL,
  `Admin_Email` varchar(30) NOT NULL,
  `Admin_Pnumber` varchar(10) NOT NULL,
  `Admin_Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`Admin_name`, `Admin_Email`, `Admin_Pnumber`, `Admin_Password`) VALUES
('3_Brother', 'info@3brotherrealestate.com', '1234567890', 'admin@123');

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `image` varchar(255) NOT NULL,
  `email` varchar(30) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `linkedin` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `name`, `image`, `email`, `phone`, `linkedin`) VALUES
(1, 'Zaid Mansuri', 'Zaid.jpg', 'LzJLd1V3SmgzTEVhK2tpTmQxbUNoaj', 'YmwrRElNNj', 'WU1LNUNYck1yL3JWOWJKOHZsY3FjejhGS3BNNDMwbHhGSmo4eFB3R3ZhWGUrR3Fvb0M0UE5ZKzdJeld4dU94alRWNmQ6OpjsW1qkyXxZJtu85BlIQcw='),
(2, 'Sahal Patel', 'Sahal.jpg', 'RExrQWJkQXUxUlJnaTY1VkIvMmpGcm', 'R05lQW1tUU', 'ZFNDVWhENGFEdVJhRzdKQk1hZ2Vyem9MRlJxem9VS2ZUZ2pLdzlXTkNGa2pTM3B6TnNYNmtCUTlqQ1BnZDVoT01FV2YzRUFGSzZLdTJrSmRhVDAzbC9tYUJuSlR6R0M3NDB0RzUyaEdmZFlqSjRDMDZZbEpLamlickhEZVJaYUYrUXpaaTUremJZb1RXbHArSGM2ZmNSNDNicDlVS29DV21XYzRHTHBJNWFSOWdGaUw6OqvlO6WJGNfMeQBbSKA/9JU='),
(3, 'Maaj Nandoliya', 'Maaj.jpg', 'Ri9FMm9CalROaDhFS1FmM25QUTZUN2', 'aUJDMVg3Rk', 'OXhkL1h5dHRPZmV4TnlVcnZ1QUNTVVEvUHFLR0xmWVhmNi9uUVFIQkJEdFlWWEpyQXVkekhDR2Q6Ov9VO4C9geeEUyu6G28iNCg=');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(20) NOT NULL,
  `name` varchar(25) NOT NULL,
  `DT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `DT`) VALUES
(1, 'Buy', '2025-10-01 21:09:47'),
(2, 'Rent', '2025-10-01 21:09:47'),
(4, 'Flate', '2025-10-01 21:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` enum('Available','Not Available') NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `pincode` varchar(20) NOT NULL,
  `area` int(11) NOT NULL,
  `bedrooms` int(11) NOT NULL,
  `bathrooms` int(11) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `category_id`, `status`, `price`, `address`, `city`, `pincode`, `area`, `bedrooms`, `bathrooms`, `description`, `image`, `agent_id`, `created_at`) VALUES
(8, '52591 Union Boulevard', 1, 'Available', 580000.00, 'San Francisco', 'CA, USA', '380055', 1234, 4, 4, 'This item is connected to a text field in your content collection. Double click what you want to edit and then select \"Change Content\" to add your own content to the collection. Want to view and manage all your collections? Click the Content Manager icon on the add panel to your left. In the Content Manager, you can update items, add new fields, create dynamic pages and more.', '68cfbaedb3ffd.jpg', 3, '2025-09-21 08:44:29'),
(9, '33234 Washington Avenue', 1, 'Available', 770000.00, 'San Francisco', 'CA, USA', '380055', 4321, 4, 2, 'This item is connected to a text field in your content collection. Double click what you want to edit and then select \"Change Content\" to add your own content to the collection. Want to view and manage all your collections? Click the Content Manager icon on the add panel to your left. In the Content Manager, you can update items, add new fields, create dynamic pages and more.', '68cfbb5aa9be9.jpg', 2, '2025-09-21 08:46:18'),
(11, '11251 Terry Street', 2, 'Available', 1500.00, 'San Francisco', 'CA, USA', '380055', 1234, 5, 3, 'This item is connected to a text field in your content collection. Double click what you want to edit and then select \"Change Content\" to add your own content to the collection. Want to view and manage all your collections? Click the Content Manager icon on the add panel to your left. In the Content Manager, you can update items, add new fields, create dynamic pages and more.', '68cfbc0b41bc5.jpg', 3, '2025-09-21 08:49:15'),
(15, ' 22043 Columbus Avenue', 2, 'Available', 1200.00, 'San Francisco', 'CA, USA', '380055', 1234, 4, 2, 'This item is connected to a text field in your content collection. Double click what you want to edit and then select \"Change Content\" to add your own content to the collection. Want to view and manage all your collections? Click the Content Manager icon on the add panel to your left. In the Content Manager, you can update items, add new fields, create dynamic pages and more', '68d002c30504f.jpg', 1, '2025-09-21 13:50:59'),
(16, '15878 Mulberry Street', 2, 'Available', 1800.00, 'San Francisco', 'CA, USA', '380055', 1234, 6, 3, 'This item is connected to a text field in your content collection. Double click what you want to edit and then select \"Change Content\" to add your own content to the collection. Want to view and manage all your collections? Click the Content Manager icon on the add panel to your left. In the Content Manager, you can update items, add new fields, create dynamic pages and more.', '68d0032e7f8f8.Jpg', 2, '2025-09-21 13:52:46'),
(18, 'OG Flate ', 4, 'Available', 73291.86, 'Nava Vadaj', ' Ahmedabad', '380055', 1050, 2, 2, 'Check out this 2 BHK Flat for sale in Nava Vadaj, Ahmedabad. This 2 BHK Flat is perfect for a modern-day lifestyle. Nava Vadaj is a promising location in Ahmedabad and this is one of the finest properties in the area. Buy this Flat for sale now. It is located on floor 2. The total number of floors in this project is 13. The property\'s price is Rs 69.5 L. Residents in this property pay Rs 0 towards maintenance. This property is a modern-day abode, with 1200 square_feet built-up area. The carpet-area is 1050 square_feet. The unit has 2 bedrooms and 2 bathroom. Healthcare centres such as Sterling Hospitals - Memnagar, Maruti Orthopaedic Hospital and Joint Replacement Centre, BAPS Yogiji Maharaj Hospital are also easily accessible. The brokerage amount to be paid is Rs 0', '68daa2fcd828b.jpg', 1, '2025-09-29 15:17:16'),
(19, 'Luxury Flats', 4, 'Available', 422836.32, 'Sector 103', 'Gurugram', '12346', 1234, 3, 3, 'The legacy of Godrej Properties continues its stellar growth with its next momentous venture, Godrej Vrikshya. Inspired by the Tree and the sheer abundance of wonders it provides, Godrej Vrikshya is a treasure trove of surprises designed to astonish, amaze, delight and help you find peace of mind, whatever your state of mind.\r\n\r\n', '68daaa6c484a1.jpg', 3, '2025-09-29 15:49:00'),
(20, 'Godrej Zenith', 4, 'Available', 563803.00, 'Sector 89', 'Gurugram', '12345', 1234, 4, 3, 'Welcome to Godrej Zenith, where luxury living meets holistic wellbeing in the heart of Gurgaon\'s Sector 89. With a focus on premium amenities and lush green surroundings, Godrej Zenith offers residents a lifestyle of comfort, convenience, and wellness. Experience the pinnacle of modern living at Godrej Zenith, where every detail is crafted to elevate your everyday experience.', '68daab264c8c1.jpg', 2, '2025-09-29 15:52:06'),
(22, 'Ruhan Duplex', 1, 'Available', 15000.00, '4, Ruhan Duplex ', 'Ahmedabad', '380055', 1234, 2, 3, 'Duplex houses fall under the category of apartments and are widely preferred for having many house-like qualities. Nowadays, many people are choosing duplex houses as they are offering privacy to the housemates in terms of more space. One most important thing to be kept in mind while investing in duplexes is that not all the duplex units are the same. One unit differs from another in terms of the number of rooms, design, layout, and other factors. So, buyers should be very cautious while buying a duplex for the number of rooms the unit has and the design it is offering to the buyers.', '68dac5284ac86.jpg', 1, '2025-09-29 17:43:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `sno` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `number` varchar(10) NOT NULL,
  `city` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `dt` datetime NOT NULL DEFAULT current_timestamp(),
  `email_notifications` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `property_alerts` tinyint(1) DEFAULT 1,
  `newsletter` tinyint(1) DEFAULT 1,
  `profile_visibility` enum('public','agents','private') DEFAULT 'public',
  `show_email` tinyint(1) DEFAULT 0,
  `show_phone` tinyint(1) DEFAULT 0,
  `preferred_location` varchar(100) DEFAULT NULL,
  `min_price` decimal(12,2) DEFAULT NULL,
  `max_price` decimal(12,2) DEFAULT NULL,
  `preferred_property_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`sno`, `username`, `email`, `number`, `city`, `password`, `dt`, `email_notifications`, `sms_notifications`, `property_alerts`, `newsletter`, `profile_visibility`, `show_email`, `show_phone`, `preferred_location`, `min_price`, `max_price`, `preferred_property_type`) VALUES
(1, 'Zaid Mansuri', 'mansurizaid663@gmail.com', '9558601570', 'Ahmedabad', '$2y$10$teRILetIijnhfL8Cw3P0duLnxZRvcjL/2/Yy2VycaAatQs.q1PDwK', '2025-08-08 21:45:52', 1, 0, 1, 1, 'public', 0, 0, NULL, NULL, NULL, NULL),
(2, 'Maaj Nandoliya ', 'maajbhaiiqbalbhai@gmail.com', '7016781925', 'Palanpur ', '$2y$10$PRCgsCzByLGRebgIRSdwneAmuyqczIkrmtE/fUXxy/6qTk1acnu96', '2025-08-09 12:20:25', 1, 0, 1, 1, 'public', 0, 0, NULL, NULL, NULL, NULL),
(3, 'Sahal Patel', 'sahal233patel@gmail.com', '9409140383', 'Ahmedabad', '$2y$10$t..GTBZfHl5lBMnydVdkp.CfN3dZqcytMrVSSzAwEWSdXknAhQ./u', '2025-08-09 12:22:50', 1, 0, 1, 1, 'public', 0, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `viewing_requests`
--

CREATE TABLE `viewing_requests` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_phone` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `requested_datetime` datetime NOT NULL,
  `status` enum('Pending','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `agent_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `viewing_requests`
--

INSERT INTO `viewing_requests` (`id`, `property_id`, `user_name`, `user_email`, `user_phone`, `message`, `requested_datetime`, `status`, `agent_id`, `created_at`, `updated_at`) VALUES
(1, 19, 'Mansuri Zaid ', 'mansurizaid663@gmail.com', '9558601570', 'I Want To Buy This Flate In 2nd Floor ', '2025-09-30 10:30:00', 'Pending', 3, '2025-09-29 17:57:42', '2025-09-30 05:45:38'),
(2, 22, 'Mansuri Zaid', 'mansurizaid663@gmail.com', '9558601570', 'I Want To Buy This Duplex', '2025-10-01 02:30:00', 'Pending', 1, '2025-09-30 05:46:54', '2025-09-30 16:13:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD UNIQUE KEY `Admin_name` (`Admin_name`),
  ADD UNIQUE KEY `Admin_Email` (`Admin_Email`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`sno`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `viewing_requests`
--
ALTER TABLE `viewing_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `sno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `viewing_requests`
--
ALTER TABLE `viewing_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `viewing_requests`
--
ALTER TABLE `viewing_requests`
  ADD CONSTRAINT `viewing_requests_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `viewing_requests_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
