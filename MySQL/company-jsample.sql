
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE DATABASE IF NOT EXISTS `jsample`;
USE `jsample`;

CREATE TABLE IF NOT EXISTS `company` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

INSERT INTO `company` (`id`, `title`, `description`) VALUES
(1, 'Math Bar', 'An institution for math majors who teach aspiring students advanced mathematics.'),
(2, 'Craftables', 'A small arts and crafts shop in a green friendly down town area.'),
(3, 'Medic Ten', 'A medical care agency which takes care of the elderly.'),
(4, 'Programmers Anonymous', 'A social club for people who are addicted to programmer, pro-bono.');

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `age` int(3) NOT NULL,
  `gender` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `company` int(10) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

INSERT INTO `user` (`id`, `name`, `age`, `gender`, `company`, `created`) VALUES
(1, 'Joe', 28, 'm', 1, '2010-10-05 01:00:00'),
(2, 'Jenny', 26, 'f', 2, '2010-10-06 02:00:00'),
(3, 'Jesse', 27, 'm', 4, '2010-10-07 03:00:00'),
(4, 'Justine', 25, 'f', 3, '2010-10-08 04:00:00'),
(5, 'Ronald', 22, 'm', 4, '2010-10-09 05:00:00'),
(6, 'Anne', 19, 'f', 3, '2010-10-10 06:00:00');

CREATE TABLE IF NOT EXISTS `user_review` (
  `id` int(10) NOT NULL,
  `assign_user` int(10) NOT NULL,
  `message` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id` (`id`,`assign_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `user_review` (`id`, `assign_user`, `message`) VALUES
(1, 1, 'Reliable employee, never late and always has a positive attitude.'),
(2, 2, 'Creative person who enjoys her work.'),
(3, 3, 'An odd personality, yet gets his work done.'),
(4, 4, 'An ambitious girl with a bright future.');