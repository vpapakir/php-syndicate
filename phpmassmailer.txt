CREATE TABLE IF NOT EXISTS `mailsenders` (
  `port` int(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `account` varchar(20) NOT NULL,
  `host` varchar(20) NOT NULL,
  `id` int(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account` (`account`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='mail senders' AUTO_INCREMENT=5 ;

INSERT INTO `mailsenders` (`port`, `username`, `password`, `account`, `host`, `id`) VALUES
(25, 'user1', 'user1pass', 'user1@mydomain1.com', 'mail.mydomain1.com', 1),
(25, 'user2', 'user2pass', 'user2@mydomain1.com', 'mail.mydomain1.com', 2),
(25, 'user3', 'user3pass', 'user3@mydomain1.com', 'mail.mydomain1.com', 3),
(25, 'user4', 'user4pass', 'user4@mydomain1.com', 'localhost', 4);

CREATE TABLE IF NOT EXISTS `mailsubjects` (
  `subid` int(20) NOT NULL AUTO_INCREMENT,
  `subjecttext` varchar(20) NOT NULL,
  PRIMARY KEY (`subid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `mymail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

INSERT INTO `mymail` (`id`, `mail`) VALUES
(1, 'user1@mydomain1.com');

CREATE TABLE IF NOT EXISTS `socks` (
  `sockip` varchar(20) NOT NULL,
  `id` int(20) NOT NULL,
  `proxy` varchar(20) NOT NULL,
  `port` int(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `users` (
  `password` varchar(15) NOT NULL,
  `username` varchar(15) NOT NULL,
  `name` varchar(15) NOT NULL,
  `surname` varchar(15) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `users` (`password`, `username`, `name`, `surname`) VALUES
('admin', 'admin', 'admin', 'admin');

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
