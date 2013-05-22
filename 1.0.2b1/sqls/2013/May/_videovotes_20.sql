-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2013 at 06:17 PM
-- Server version: 5.5.27
-- PHP Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `nathan_videovotes`
--

-- --------------------------------------------------------

--
-- Table structure for table `vv_admins`
--

CREATE TABLE IF NOT EXISTS `vv_admins` (
  `uname` varchar(100) NOT NULL,
  `upass` varchar(300) NOT NULL,
  `status` enum('active','suspended','deleted','pending') NOT NULL,
  `logged_in` enum('yes','no') NOT NULL,
  `logged_ip` varchar(100) NOT NULL,
  `logged_session` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `vv_admins`
--

INSERT INTO `vv_admins` (`uname`, `upass`, `status`, `logged_in`, `logged_ip`, `logged_session`) VALUES
('admin', 'b739d195ad7192bbd6a223a67645e3a766c239073667821cec0e07257f74410f', 'active', 'yes', '10.10.10.241', '023..1111.792-80114908');

-- --------------------------------------------------------

--
-- Table structure for table `vv_entries`
--

CREATE TABLE IF NOT EXISTS `vv_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `source` varchar(100) NOT NULL,
  `originator` varchar(100) NOT NULL,
  `type` enum('youtube','vimeo','image') NOT NULL,
  `totalvotes` int(100) NOT NULL,
  `rate` int(10) NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `story` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `vv_entries`
--

INSERT INTO `vv_entries` (`id`, `name`, `source`, `originator`, `type`, `totalvotes`, `rate`, `status`, `story`) VALUES
(1, 'Original Army Version', '4hpEnLtqUDg', '', 'youtube', 23, 23, 'active', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry''s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'),
(2, 'The Harlem Shake v1 (TSCS original)', '384IUU43bfQ', '', 'youtube', 8, 8, 'active', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry''s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'),
(3, 'Some other name that I do not know', '95vZ0-C1Kho', '', 'youtube', 10, 10, 'active', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry''s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'),
(4, 'Wedding Harlem Shake (San Antonio, Texas) by Expose the Heart', 'KvO4VEkhqaE', '', 'youtube', 9, 9, 'inactive', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry''s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'),
(5, 'Test Kitten', 'testKITTEN.jpg', 'Nathan', 'image', 2, 2, 'active', 'I created a random image and this is the dummy text that goes along with it. '),
(6, 'Test Kitten', 'testKITTEN.jpg', 'Nathan', 'image', 0, 0, 'deleted', 'blah');

-- --------------------------------------------------------

--
-- Table structure for table `vv_stats`
--

CREATE TABLE IF NOT EXISTS `vv_stats` (
  `total_voted` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `vv_stats`
--

INSERT INTO `vv_stats` (`total_voted`) VALUES
(52);

-- --------------------------------------------------------

--
-- Table structure for table `vv_who`
--

CREATE TABLE IF NOT EXISTS `vv_who` (
  `ip` varchar(100) NOT NULL,
  `session` varchar(100) NOT NULL,
  `has_voted_times` int(10) NOT NULL,
  `lastvote` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `vv_who`
--

INSERT INTO `vv_who` (`ip`, `session`, `has_voted_times`, `lastvote`) VALUES
('10.10.10.241', '741024611.0-5.15.011013', 1, '2013-05-20');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
