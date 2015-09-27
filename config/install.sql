CREATE TABLE IF NOT EXISTS `account` (
  `id` char(32) NOT NULL,
  `user` char(32) NOT NULL,
  `pwd` char(64) NOT NULL,
  `name` char(32) NOT NULL,
  `email` char(64) NOT NULL,
  `power` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `borrow` (
  `userid` char(32) NOT NULL,
  `roomid` char(32) NOT NULL,
  `date` date NOT NULL,
  `class` int(11) NOT NULL,
  `valid` int(11) NOT NULL DEFAULT '0',
  `updatetime` datetime NOT NULL,
  `message` char(255) NOT NULL DEFAULT '',
  `hash` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `category` (
  `id` char(32) NOT NULL,
  `name` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roomlist` (
  `id` char(32) NOT NULL,
  `name` char(32) NOT NULL,
  `cate` char(32) NOT NULL,
  `admin` char(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `session` (
  `id` char(32) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cookie` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
