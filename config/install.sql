SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `account` (
  `id` char(32) NOT NULL,
  `user` char(32) NOT NULL,
  `pwd` char(64) NOT NULL,
  `name` char(32) NOT NULL,
  `email` char(64) NOT NULL,
  `power` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `borrow` (
  `userid` char(32) NOT NULL,
  `roomid` char(32) NOT NULL,
  `date` date NOT NULL,
  `class` int(11) NOT NULL,
  `valid` int(11) NOT NULL DEFAULT '0',
  `requesttime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatetime` datetime NOT NULL,
  `message` char(255) NOT NULL DEFAULT '',
  `hash` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `category` (
  `id` char(32) NOT NULL,
  `name` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `periodname` (
  `no` int(11) NOT NULL,
  `name` varchar(10) NOT NULL,
  `shortname` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `periodname` (`no`, `name`, `shortname`) VALUES
(5, '早自修', '早'),
(9, '早上', '早'),
(10, '第1節', '1'),
(20, '第2節', '2'),
(30, '第3節', '3'),
(40, '第4節', '4'),
(45, '午休', '午'),
(49, '下午', '下'),
(50, '第5節', '5'),
(60, '第6節', '6'),
(70, '第7節', '7'),
(80, '第8節', '8'),
(89, '晚上', '晚');

CREATE TABLE `roomlist` (
  `id` char(32) NOT NULL,
  `name` char(32) NOT NULL,
  `cate` char(32) NOT NULL,
  `admin` char(32) NOT NULL DEFAULT '',
  `borrow_daylimit_min` int(11) NOT NULL DEFAULT '7',
  `borrow_daylimit_max` int(11) NOT NULL DEFAULT '28',
  `borrow_accept_period` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `session` (
  `id` char(32) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cookie` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `account`
  ADD UNIQUE KEY `user` (`user`);

ALTER TABLE `borrow`
  ADD UNIQUE KEY `hash` (`hash`);

ALTER TABLE `category`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `periodname`
  ADD UNIQUE KEY `no` (`no`);

ALTER TABLE `roomlist`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `session`
  ADD UNIQUE KEY `cookie` (`cookie`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
