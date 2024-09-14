-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 12, 2024 at 11:27 AM
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
-- Database: `it_suport_ticket_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `type` enum('Email','SMS') NOT NULL,
  `message` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `title` varchar(255) NOT NULL DEFAULT 'Notification'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `ticket_id`, `type`, `message`, `status`, `created_at`, `title`) VALUES
(1, 8, 48, 'Email', 'A new comment has been added to the ticket with ID 48.', 0, '2024-08-05 05:59:32', 'Notification'),
(2, 4, 30, 'Email', 'A new comment has been added to the ticket with ID 30.', 1, '2024-08-05 06:01:02', 'Notification'),
(3, 1, 53, 'Email', 'A new comment has been added to the ticket with ID 53.', 1, '2024-08-05 06:02:20', 'Notification'),
(4, 1, 53, 'Email', 'A new comment has been added to the ticket with ID 53.', 1, '2024-08-05 06:15:30', 'Notification'),
(5, 1, 53, 'Email', 'A new comment has been added to the ticket with ID 53.', 1, '2024-08-05 06:21:52', 'Notification'),
(6, 1, 53, 'Email', 'A new comment has been added to the ticket with ID 53.', 1, '2024-08-05 06:24:07', 'Notification'),
(7, 4, 53, 'Email', 'A new comment has been added to the ticket with ID 53.', 1, '2024-08-05 06:24:09', 'Notification'),
(8, 1, 28, 'Email', 'The status of your ticket with ID 28 has been updated to \'On Hold\' by  \'mohammd\'.', 1, '2024-08-05 06:52:24', 'Notification'),
(9, 1, 28, 'Email', 'The status of your ticket with ID 28 has been updated to \'In Progress\' by  \'mohammd\'.', 1, '2024-08-05 06:53:11', 'Notification'),
(10, 1, 28, 'Email', 'The status of your ticket with ID 28 has been updated to \'Resolved\' by  \'mohammd\'.', 1, '2024-08-05 06:54:01', 'Notification'),
(11, 1, 55, 'Email', 'Your ticket with ID 55 has been created. You will be kept in touch for further updates.', 1, '2024-08-05 10:10:07', 'Notification'),
(12, 6, 55, 'Email', 'You have been automatically assigned a new ticket with ID 55.', 0, '2024-08-05 10:10:51', 'Notification'),
(13, 1, 55, 'Email', 'Ticket with ID 55 has been automatically assigned to \'6\'.', 1, '2024-08-05 10:10:52', 'Notification'),
(14, 1, 56, 'Email', 'Your ticket with ID 56 has been created. You will be kept in touch for further updates.', 1, '2024-08-05 10:14:10', 'Notification'),
(15, 4, 56, 'Email', 'You have been assigned a ticket with ID 56. Please take Action \'.', 1, '2024-08-05 10:15:03', 'Notification'),
(16, 1, 56, 'Email', 'The ticket with ID 56 has been assigned to \'mohammd\'.', 1, '2024-08-05 10:15:05', 'Notification'),
(17, 1, 56, 'Email', 'The status of your ticket with ID 56 has been updated to \'In Progress\' by  \'mohammd\'.', 1, '2024-08-05 10:16:32', 'Notification'),
(18, 1, 56, 'Email', 'The status of your ticket with ID 56 has been updated to \'On Hold\' by  \'mohammd\'.', 1, '2024-08-05 10:29:24', 'Notification'),
(19, 1, 56, 'Email', 'The status of your ticket with ID 56 has been updated to \'In Progress\' by  \'mohammd\'.', 1, '2024-08-05 10:31:37', 'Notification'),
(20, 1, 56, 'Email', 'A new comment has been added to the ticket with ID 56.', 1, '2024-08-05 10:32:36', 'Notification'),
(21, 1, 57, 'Email', 'Your ticket with ID 57 has been created. You will be kept in touch for further updates.', 1, '2024-08-05 10:34:19', 'Notification'),
(22, 4, 57, 'Email', 'You have been assigned a ticket with ID 57. Please take Action \'.', 1, '2024-08-05 10:35:25', 'Notification'),
(23, 1, 57, 'Email', 'The ticket with ID 57 has been assigned to \'mohammd\'.', 1, '2024-08-05 10:35:27', 'Notification'),
(24, 1, 58, 'Email', 'Your ticket with ID 58 has been created. You will be kept in touch for further updates.', 1, '2024-08-05 10:42:22', 'Notification'),
(25, 4, 58, 'Email', 'You have been assigned a ticket with ID 58. Please take Action \'.', 1, '2024-08-05 10:43:58', 'Notification'),
(26, 1, 58, 'Email', 'The ticket with ID 58 has been assigned to \'mohammd\'.', 1, '2024-08-05 10:44:01', 'Notification'),
(27, 1, 59, 'Email', 'Your ticket with ID 59 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 05:37:17', 'Notification'),
(28, 1, 60, 'Email', 'Your ticket with ID 60 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 05:38:15', 'Notification'),
(29, 1, 56, 'Email', 'The status of your ticket with ID 56 has been updated to \'On Hold\' by  \'mohammd\'.', 1, '2024-08-06 05:39:25', 'Notification'),
(30, 4, 59, 'Email', 'You have been assigned a ticket with ID 59. Please take Action \'.', 1, '2024-08-06 05:40:43', 'Notification'),
(31, 1, 59, 'Email', 'The ticket with ID 59 has been assigned to \'mohammd\'.', 1, '2024-08-06 05:40:45', 'Notification'),
(32, 4, 60, 'Email', 'You have been assigned a ticket with ID 60. Please take Action \'.', 1, '2024-08-06 05:49:58', 'Notification'),
(33, 1, 60, 'Email', 'The ticket with ID 60 has been assigned to \'mohammd\'.', 1, '2024-08-06 05:49:58', 'Notification'),
(34, 1, 60, 'Email', 'The status of your ticket with ID 60 has been updated to \'In Progress\' by  \'mohammd\'.', 1, '2024-08-06 05:53:22', 'Notification'),
(35, 1, 61, 'Email', 'Your ticket with ID 61 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 05:54:43', 'Notification'),
(36, 4, 61, 'Email', 'You have been assigned a ticket with ID 61. Please take Action \'.', 1, '2024-08-06 05:56:26', 'Notification'),
(37, 1, 61, 'Email', 'The ticket with ID 61 has been assigned to \'mohammd\'.', 1, '2024-08-06 05:56:26', 'Notification'),
(38, 1, 56, 'Email', 'The status of your ticket with ID 56 has been updated to \'In Progress\' by  \'mohammd\'.', 1, '2024-08-06 06:02:05', 'Notification'),
(39, 1, 62, 'Email', 'Your ticket with ID 62 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 06:02:43', 'Notification'),
(40, 1, 63, 'Email', 'Your ticket with ID 63 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 06:02:59', 'Notification'),
(41, 4, 62, 'Email', 'You have been assigned a ticket with ID 62. Please take Action \'.', 1, '2024-08-06 06:06:54', 'Notification'),
(42, 1, 62, 'Email', 'The ticket with ID 62 has been assigned to \'mohammd\'.', 1, '2024-08-06 06:06:55', 'Notification'),
(43, 4, 63, 'Email', 'You have been assigned a ticket with ID 63. Please take Action \'.', 1, '2024-08-06 06:08:46', 'Notification'),
(44, 1, 63, 'Email', 'The ticket with ID 63 has been assigned to \'mohammd\'.', 1, '2024-08-06 06:08:46', 'Notification'),
(45, 1, 64, 'Email', 'Your ticket with ID 64 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 06:11:52', 'Notification'),
(46, 4, 64, 'Email', 'You have been assigned a ticket with ID 64. Please take Action \'.', 1, '2024-08-06 06:12:44', 'Notification'),
(47, 1, 64, 'Email', 'The ticket with ID 64 has been assigned to \'mohammd\'.', 1, '2024-08-06 06:12:44', 'Notification'),
(48, 1, 65, 'Email', 'Your ticket with ID 65 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 06:23:30', 'Notification'),
(49, 1, 65, 'Email', 'The status of your ticket with ID 65 has been updated to \'On Hold\' by \'mohammd\'.', 1, '2024-08-06 06:24:09', 'Notification'),
(50, 1, 65, 'Email', 'The status of your ticket with ID 65 has been updated to \'On Hold\' by \'mohammd\'.', 1, '2024-08-06 06:24:29', 'Notification'),
(51, 4, 65, 'Email', 'You have been assigned a ticket with ID 65. Please take Action \'.', 1, '2024-08-06 06:29:43', 'Notification'),
(52, 1, 65, 'Email', 'The ticket with ID 65 has been assigned to \'mohammd\'.', 1, '2024-08-06 06:29:43', 'Notification'),
(53, 1, 66, 'Email', 'Your ticket with ID 66 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 06:30:13', 'Notification'),
(54, 1, 67, 'Email', 'Your ticket with ID 67 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 06:30:28', 'Notification'),
(55, 4, 66, 'Email', 'You have been assigned a ticket with ID 66. Please take Action \'.', 1, '2024-08-06 06:30:57', 'Notification'),
(56, 1, 66, 'Email', 'The ticket with ID 66 has been assigned to \'mohammd\'.', 1, '2024-08-06 06:30:57', 'Notification'),
(57, 1, 67, 'Email', 'The status of your ticket with ID 67 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 06:33:31', 'Notification'),
(58, 1, 67, 'Email', 'The status of your ticket with ID 67 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 06:34:16', 'Notification'),
(59, 1, 67, 'Email', 'The status of your ticket with ID 67 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 06:35:01', 'Notification'),
(60, 1, 67, 'Email', 'The status of your ticket with ID 67 has been updated to \'On Hold\' by \'mohammd\'.', 1, '2024-08-06 06:39:01', 'Notification'),
(61, 4, 67, 'Email', 'You have been assigned a ticket with ID 67. Please take Action \'.', 1, '2024-08-06 06:39:35', 'Notification'),
(62, 1, 67, 'Email', 'The ticket with ID 67 has been assigned to \'mohammd\'.', 1, '2024-08-06 06:39:35', 'Notification'),
(63, 1, 68, 'Email', 'Your ticket with ID 68 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 06:40:00', 'Notification'),
(64, 4, 68, 'Email', 'You have been assigned a ticket with ID 68. Please take Action \'.', 1, '2024-08-06 06:40:14', 'Notification'),
(65, 1, 68, 'Email', 'The ticket with ID 68 has been assigned to \'mohammd\'.', 1, '2024-08-06 06:40:14', 'Notification'),
(66, 1, 68, 'Email', 'The status of your ticket with ID 68 has been updated to \'Resolved\' by \'mohammd\'.', 1, '2024-08-06 06:42:52', 'Notification'),
(67, 4, 68, 'Email', 'A new comment has been added to the ticket with ID 68.', 1, '2024-08-06 06:43:29', 'Notification'),
(68, 1, 69, 'Email', 'Your ticket with ID 69 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 06:51:46', 'Notification'),
(69, 4, 69, 'Email', 'You have been assigned a ticket with ID 69. Please take Action \'.', 1, '2024-08-06 07:04:50', 'Notification'),
(70, 1, 69, 'Email', 'The ticket with ID 69 has been assigned to \'mohammd\'.', 1, '2024-08-06 07:04:50', 'Notification'),
(71, 1, 69, 'Email', 'The status of your ticket with ID 69 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 07:06:11', 'Notification'),
(72, 1, 67, 'Email', 'The status of your ticket with ID 67 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 07:06:30', 'Notification'),
(73, 1, 67, 'Email', 'The status of your ticket with ID 67 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 07:07:32', 'Notification'),
(74, 1, 65, 'Email', 'The status of your ticket with ID 65 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 07:07:36', 'Notification'),
(75, 1, 69, 'Email', 'The status of your ticket with ID 69 has been updated to \'On Hold\' by \'mohammd\'.', 1, '2024-08-06 07:07:42', 'Notification'),
(76, 1, 39, 'Email', 'The status of your ticket with ID 39 has been updated to \'On Hold\' by \'mohammd\'.', 1, '2024-08-06 07:07:47', 'Notification'),
(77, 1, 66, 'Email', 'The status of your ticket with ID 66 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 07:09:12', 'Notification'),
(78, 1, 70, 'Email', 'Your ticket with ID 70 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 07:11:22', 'Notification'),
(79, 4, 70, 'Email', 'You have been assigned a ticket with ID 70. Please take Action \'.', 1, '2024-08-06 07:11:32', 'Notification'),
(80, 1, 70, 'Email', 'The ticket with ID 70 has been assigned to \'mohammd\'.', 1, '2024-08-06 07:11:32', 'Notification'),
(81, 4, 70, 'Email', 'You have been assigned a ticket with ID 70. Please take Action \'.', 1, '2024-08-06 07:13:06', 'Notification'),
(82, 1, 70, 'Email', 'The ticket with ID 70 has been assigned to \'mohammd\'.', 1, '2024-08-06 07:13:06', 'Notification'),
(83, 1, 70, 'Email', 'The status of your ticket with ID 70 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 07:13:15', 'Notification'),
(84, 1, 71, 'Email', 'Your ticket with ID 71 has been created. You will be kept in touch for further updates.', 1, '2024-08-06 07:13:41', 'Notification'),
(85, 4, 71, 'Email', 'You have been assigned a ticket with ID 71. Please take Action \'.', 1, '2024-08-06 07:13:57', 'Notification'),
(86, 1, 71, 'Email', 'The ticket with ID 71 has been assigned to \'mohammd\'.', 1, '2024-08-06 07:13:57', 'Notification'),
(87, 1, 71, 'Email', 'The status of your ticket with ID 71 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-06 07:13:58', 'Notification'),
(88, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 05:52:45', 'Notification'),
(89, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 05:52:45', 'Notification'),
(90, 1, 71, 'Email', 'Ticket with ID 71 has been updated. Priority: \'Medium\'..', 1, '2024-08-07 05:52:45', 'Notification'),
(91, 4, 71, 'Email', 'Ticket with ID 71 has been updated. Priority: \'Medium\'..', 1, '2024-08-07 05:52:45', 'Notification'),
(92, 4, 70, 'Email', 'You have been assigned a new ticket with ID 70.', 1, '2024-08-07 05:56:41', 'Notification'),
(93, 4, 70, 'Email', 'The ticket with ID 70 has been reassigned.', 1, '2024-08-07 05:56:41', 'Notification'),
(94, 1, 70, 'Email', 'Ticket with ID 70 has been updated. Status: \'On Hold\'..', 1, '2024-08-07 05:56:41', 'Notification'),
(95, 4, 70, 'Email', 'Ticket with ID 70 has been updated. Status: \'On Hold\'..', 1, '2024-08-07 05:56:41', 'Notification'),
(96, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:02:49', 'Notification'),
(97, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:02:49', 'Notification'),
(98, 1, 71, 'Email', 'Ticket with ID 71 has been updated. Priority: \'High\'..', 1, '2024-08-07 06:02:49', 'Notification'),
(99, 4, 71, 'Email', 'Ticket with ID 71 has been updated. Priority: \'High\'..', 1, '2024-08-07 06:02:49', 'Notification'),
(100, 1, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:02:49', 'Notification'),
(101, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:02:49', 'Notification'),
(102, 1, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:02:49', 'Notification'),
(103, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:02:49', 'Notification'),
(104, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:03:53', 'Notification'),
(105, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:03:53', 'Notification'),
(106, 1, 71, 'Email', 'Ticket with ID 71 has been updated. Status: \'On Hold\'..', 1, '2024-08-07 06:03:53', 'Notification'),
(107, 4, 71, 'Email', 'Ticket with ID 71 has been updated. Status: \'On Hold\'..', 1, '2024-08-07 06:03:53', 'Notification'),
(108, 1, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:03:53', 'Notification'),
(109, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:03:53', 'Notification'),
(110, 1, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:03:53', 'Notification'),
(111, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:03:53', 'Notification'),
(112, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:05:36', 'Notification'),
(113, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:05:36', 'Notification'),
(114, 1, 71, 'Email', 'Ticket with ID 71 has been updated. Status: \'In Progress\'..', 1, '2024-08-07 06:05:36', 'Notification'),
(115, 4, 71, 'Email', 'Ticket with ID 71 has been updated. Status: \'In Progress\'..', 1, '2024-08-07 06:05:36', 'Notification'),
(116, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:07:59', 'Notification'),
(117, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:07:59', 'Notification'),
(118, 1, 71, 'Email', 'Ticket with ID 71 has been updated. Priority: \'Medium\'..', 1, '2024-08-07 06:07:59', 'Notification'),
(119, 4, 71, 'Email', 'Ticket with ID 71 has been updated. Priority: \'Medium\'..', 1, '2024-08-07 06:07:59', 'Notification'),
(120, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:11:04', 'Notification'),
(121, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:11:04', 'Notification'),
(128, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:19:25', 'Notification'),
(129, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:19:25', 'Notification'),
(130, 4, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:25:23', 'Notification'),
(131, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:25:23', 'Notification'),
(132, 11, 71, 'Email', 'You have been assigned a new ticket with ID 71.', 1, '2024-08-07 06:28:25', 'Notification'),
(133, 4, 71, 'Email', 'The ticket with ID 71 has been reassigned.', 1, '2024-08-07 06:28:25', 'Notification'),
(134, 1, 71, 'Email', 'Ticket with ID 71 has been updated. Status: \'On Hold\'. Priority: \'High\'.', 1, '2024-08-07 06:28:58', 'Notification'),
(135, 11, 71, 'Email', 'Ticket with ID 71 has been updated. Status: \'On Hold\'. Priority: \'High\'.', 1, '2024-08-07 06:28:58', 'Notification'),
(136, 1, 72, 'Email', 'Your ticket with ID 72 has been created. You will be kept in touch for further updates.', 1, '2024-08-07 07:37:44', 'Notification'),
(137, 4, 72, 'Email', 'You have been assigned a ticket with ID 72. Please take Action \'.', 1, '2024-08-07 07:49:51', 'Notification'),
(138, 1, 72, 'Email', 'The ticket with ID 72 has been assigned to \'mohammd\'.', 1, '2024-08-07 07:49:51', 'Notification'),
(139, 1, 72, 'Email', 'The status of your ticket with ID 72 has been updated to \'On Hold\' by \'mohammd\'.', 1, '2024-08-07 07:49:51', 'Notification'),
(140, 1, 64, 'Email', 'A new comment has been added to the ticket with ID 64.', 1, '2024-08-07 08:19:55', 'Notification'),
(141, 1, 64, 'Email', 'A new comment has been added to the ticket with ID 64.', 1, '2024-08-07 08:31:31', 'Notification'),
(142, 4, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by .', 1, '2024-08-07 10:05:56', 'Notification'),
(143, 1, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by .', 1, '2024-08-07 10:05:56', 'Notification'),
(144, 4, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by .', 1, '2024-08-07 10:06:02', 'Notification'),
(145, 1, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by .', 1, '2024-08-07 10:06:03', 'Notification'),
(146, 4, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by .', 1, '2024-08-07 10:06:07', 'Notification'),
(147, 1, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by .', 1, '2024-08-07 10:06:07', 'Notification'),
(148, 4, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:07:01', 'Notification'),
(149, 1, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:07:01', 'Notification'),
(150, 4, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:07:02', 'Notification'),
(151, 1, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:07:02', 'Notification'),
(152, 11, 71, 'Email', 'The solution for ticket ID 71 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:11:49', 'Notification'),
(153, 1, 71, 'Email', 'The solution for ticket ID 71 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:11:50', 'Notification'),
(154, 11, 71, 'Email', 'The solution for ticket ID 71 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:13:05', 'Notification'),
(155, 1, 71, 'Email', 'The solution for ticket ID 71 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:13:05', 'Notification'),
(156, 11, 71, 'Email', 'The solution for ticket ID 71 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:13:17', 'Notification'),
(157, 1, 71, 'Email', 'The solution for ticket ID 71 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:13:17', 'Notification'),
(158, 4, 69, 'Email', 'The solution for ticket ID 69 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:14:16', 'Notification'),
(159, 1, 69, 'Email', 'The solution for ticket ID 69 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:14:16', 'Notification'),
(160, 4, 67, 'Email', 'The solution for ticket ID 67 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:19:00', 'Notification'),
(161, 1, 67, 'Email', 'The solution for ticket ID 67 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-07 10:19:00', 'Notification'),
(162, 1, 72, 'Email', 'A new comment has been added to the ticket with ID 72.', 1, '2024-08-08 05:44:01', 'Notification'),
(163, 4, 72, 'Email', 'A new comment has been added to the ticket with ID 72.', 1, '2024-08-08 05:44:01', 'Notification'),
(164, 4, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 05:44:22', 'Notification'),
(165, 1, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 05:44:22', 'Notification'),
(166, 1, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 05:44:36', 'Notification'),
(167, 4, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 05:44:36', 'Notification'),
(168, 4, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 05:44:49', 'Notification'),
(169, 1, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 05:44:49', 'Notification'),
(170, 1, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 06:56:01', 'Notification'),
(171, 4, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 06:56:01', 'Notification'),
(172, 4, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 06:56:07', 'Notification'),
(173, 1, 72, 'Email', 'The solution for ticket ID 72 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 06:56:07', 'Notification'),
(174, 1, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 07:11:14', 'Notification'),
(175, 4, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 07:11:14', 'Notification'),
(176, 1, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 07:11:39', 'Notification'),
(177, 4, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 07:11:39', 'Notification'),
(178, 1, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Priority: \'High\'.', 1, '2024-08-08 07:12:09', 'Notification'),
(179, 4, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Priority: \'High\'.', 1, '2024-08-08 07:12:09', 'Notification'),
(180, 1, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Priority: \'Low\'.', 1, '2024-08-08 07:15:00', 'Notification'),
(181, 4, 72, 'Email', 'Ticket with ID 72 has been updated by adminstartion . Priority: \'Low\'.', 1, '2024-08-08 07:15:00', 'Notification'),
(182, 1, 73, 'Email', 'Your ticket with ID 73 has been created. You will be kept in touch for further updates.', 1, '2024-08-08 07:25:29', 'Notification'),
(183, 1, 74, 'Email', 'Your ticket with ID 74 has been created. You will be kept in touch for further updates.', 1, '2024-08-08 07:26:09', 'Notification'),
(184, 4, 74, 'Email', 'You have been assigned a ticket with ID 74. Please take Action \'.', 1, '2024-08-08 07:26:46', 'Notification'),
(185, 1, 74, 'Email', 'The ticket with ID 74 has been assigned to \'mohammd\'.', 1, '2024-08-08 07:26:46', 'Notification'),
(186, 1, 74, 'Email', 'The status of your ticket with ID 74 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-08 07:26:46', 'Notification'),
(187, 1, 74, 'Email', 'The solution for ticket ID 74 has been updated and the status set to \'Resolved\' by mohammd.', 1, '2024-08-08 07:29:44', 'Notification'),
(188, 4, 73, 'Email', 'You have been assigned a ticket with ID 73. Please take Action \'.', 1, '2024-08-08 07:30:40', 'Notification'),
(189, 1, 73, 'Email', 'The ticket with ID 73 has been assigned to \'mohammd\'.', 1, '2024-08-08 07:30:40', 'Notification'),
(190, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:07:49', 'Notification'),
(191, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:07:49', 'Notification'),
(192, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:08:22', 'Notification'),
(193, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:08:22', 'Notification'),
(194, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'Resolved\'.', 1, '2024-08-08 08:15:15', 'Notification'),
(195, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'Resolved\'.', 1, '2024-08-08 08:15:15', 'Notification'),
(196, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:23:57', 'Notification'),
(197, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:23:57', 'Notification'),
(198, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:24:54', 'Notification'),
(199, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:24:54', 'Notification'),
(200, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:27:22', 'Notification'),
(201, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:27:22', 'Notification'),
(202, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'Resolved\'.', 1, '2024-08-08 08:32:21', 'Notification'),
(203, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'Resolved\'.', 1, '2024-08-08 08:32:21', 'Notification'),
(204, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:33:04', 'Notification'),
(205, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:33:04', 'Notification'),
(206, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:34:02', 'Notification'),
(207, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:34:02', 'Notification'),
(208, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:37:11', 'Notification'),
(209, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:37:11', 'Notification'),
(210, 1, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:37:47', 'Notification'),
(211, 4, 73, 'Email', 'Ticket with ID 73 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:37:47', 'Notification'),
(212, 1, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:45:00', 'Notification'),
(213, 4, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 08:45:00', 'Notification'),
(214, 1, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:45:22', 'Notification'),
(215, 4, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 08:45:22', 'Notification'),
(216, 4, 70, 'Email', 'The solution for ticket ID 70 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 08:46:23', 'Notification'),
(217, 1, 70, 'Email', 'The solution for ticket ID 70 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 08:46:23', 'Notification'),
(218, 1, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Priority: \'High\'.', 1, '2024-08-08 08:51:43', 'Notification'),
(219, 4, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Priority: \'High\'.', 1, '2024-08-08 08:51:43', 'Notification'),
(220, 1, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 09:03:59', 'Notification'),
(221, 4, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 09:03:59', 'Notification'),
(222, 1, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'Resolved\'.', 1, '2024-08-08 09:09:20', 'Notification'),
(223, 4, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'Resolved\'.', 1, '2024-08-08 09:09:20', 'Notification'),
(224, 1, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 09:10:12', 'Notification'),
(225, 4, 70, 'Email', 'Ticket with ID 70 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 09:10:12', 'Notification'),
(226, 1, 66, 'Email', 'Ticket with ID 66 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 09:10:32', 'Notification'),
(227, 4, 66, 'Email', 'Ticket with ID 66 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 09:10:32', 'Notification'),
(228, 1, 66, 'Email', 'A new comment has been added to the ticket with ID 66.', 1, '2024-08-08 09:10:42', 'Notification'),
(229, 4, 66, 'Email', 'A new comment has been added to the ticket with ID 66.', 1, '2024-08-08 09:10:42', 'Notification'),
(230, 1, 66, 'Email', 'Ticket with ID 66 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 09:12:20', 'Notification'),
(231, 4, 66, 'Email', 'Ticket with ID 66 has been updated by adminstartion . Status: \'In Progress\'.', 1, '2024-08-08 09:12:20', 'Notification'),
(232, 1, 66, 'Email', 'Ticket with ID 66 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 09:13:46', 'Notification'),
(233, 4, 66, 'Email', 'Ticket with ID 66 has been updated by adminstartion . Status: \'On Hold\'.', 1, '2024-08-08 09:13:46', 'Notification'),
(234, 4, 66, 'Email', 'The solution for ticket ID 66 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 09:14:17', 'Notification'),
(235, 1, 66, 'Email', 'The solution for ticket ID 66 has been updated and the status set to \'Resolved\' by it_admin.', 1, '2024-08-08 09:14:17', 'Notification'),
(236, 1, 75, 'Email', 'Your ticket with ID 75 has been created. You will be kept in touch for further updates.', 1, '2024-08-08 10:14:48', 'Notification'),
(237, 4, 75, 'Email', 'You have been assigned a ticket with ID 75. Please take Action \'.', 1, '2024-08-08 10:16:05', 'Notification'),
(238, 1, 75, 'Email', 'The ticket with ID 75 has been assigned to \'mohammd\'.', 1, '2024-08-08 10:16:05', 'Notification'),
(239, 1, 75, 'Email', 'The status of your ticket with ID 75 has been updated to \'In Progress\' by \'mohammd\'.', 1, '2024-08-08 10:16:05', 'Notification'),
(240, 1, 75, 'Email', 'A new comment has been added to the ticket with ID 75.', 1, '2024-08-08 10:16:18', 'Notification'),
(241, 1, 75, 'Email', 'The status of your ticket with ID 75 has been updated to \'Resolved\' by \'mohammd\'.', 1, '2024-08-08 10:16:39', 'Notification'),
(242, 1, 75, 'Email', 'A new comment has been added to the ticket with ID 75.', 0, '2024-08-09 08:43:34', 'Notification'),
(243, 3, 70, 'Email', 'hi', 1, '2024-08-12 08:04:48', 'Notification');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=244;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
