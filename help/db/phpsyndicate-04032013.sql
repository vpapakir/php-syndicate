--
-- MySQL 5.1.66
-- Sat, 02 Mar 2013 14:19:59 +0000
--

CREATE TABLE `phpsyndicate_metadata` (
   `metadata_id` int(10) not null auto_increment,
   `record_id` int(11) not null,
   `mediatype_id` int(11) not null,
   `user_id` int(11) not null,
   `type_id` int(11) not null,
   `metadata_value` varchar(250) not null,
   PRIMARY KEY (`metadata_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicate_metadata` is empty]

CREATE TABLE `phpsyndicate_settings` (
   `settings_id` int(10) not null auto_increment,
   `settings_key` varchar(50) not null,
   `settings_value` varchar(255) not null,
   `settings_description` varchar(255),
   `isProtected` tinyint(4),
   `settings_type` varchar(20),
   PRIMARY KEY (`settings_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=18;

INSERT INTO `phpsyndicate_settings` (`settings_id`, `settings_key`, `settings_value`, `settings_description`, `isProtected`, `settings_type`) VALUES 
('1', 'SMTP_SERVER', 'localhost', 'Default SMTP Server that the application uses', '1', ''),
('2', 'SITE_HOME', 'http://localhost', 'Base url of phpsyndicate web', '1', ''),
('3', 'SITE_NAME', 'phpsyndicate', 'The name of your website', '1', ''),
('4', 'SMTP_FROM', 'user@domain.com', 'Emails address the application uses to send from', '1', ''),
('5', 'SMTP_USER', 'username', 'Set the user name if the server requires authentication', '1', ''),
('6', 'SMTP_PASS', 'password', 'Set the authentication password', '1', ''),
('7', 'SMTP_REALM', 'domain.com', 'Set to the authentication realm. Usually the authentication user e-mail domain', '1', ''),
('8', 'SMTP_DEBUG', '0', 'Set to 1 to output the communication with the SMTP server', '1', 'bool'),
('9', 'SITE_ADULT', '1', 'Show/Allow adult content on the site or not', '1', 'bool'),
('10', 'SITE_ROOT', '/', 'Path within the wwwroot (set to / for root)', '1', ''),
('11', 'DB_COVERS', '0', 'Store Cover images in  DB', '1', 'bool'),
('12', 'SESSION_LIFETIME', '6', 'For how many hours should the session stay alive', '1', ''),
('13', 'PAGE_COUNT', '25', 'Number of Records to display in the category view', '1', ''),
('14', 'ALLOW_REGISTRATION', '1', 'Allow new users to register or not', '1', 'bool'),
('15', 'RSS_SITE', '1', 'Allow RSS feeds from this phpsyndicate database', '1', 'bool'),
('16', 'RSS_USERS', '1', 'Allow RSS feeds from individual users from this phpsyndicate Database', '1', 'bool'),
('17', 'MOD_REWRITE', '0', 'Use friendly urls? This requires mod_rewrite installed.', '1', 'bool');

CREATE TABLE `phpsyndicateadmin` (
   `id` int(11) not null auto_increment,
   `loginname` varchar(25) not null,
   `namelc` varchar(255),
   `email` varchar(255),
   `created` datetime,
   `modified` datetime,
   `modifiedby` varchar(25),
   `password` varchar(255) not null,
   `passwordchanged` date,
   `superuser` int(4),
   `disabled` int(4),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateadmin` is empty]

CREATE TABLE `phpsyndicateattachment` (
   `id` int(11) not null auto_increment,
   `filename` varchar(255),
   `remotefile` varchar(255),
   `mimetype` varchar(255),
   `description` text,
   `size` int(11),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateattachment` is empty]

CREATE TABLE `phpsyndicatebccmail` (
   `idbccMail` int(11) not null auto_increment,
   `name` varchar(45),
   `surname` varchar(45),
   `email` varchar(45) not null,
   `MailMessage_idMailMessage` int(11) not null,
   PRIMARY KEY (`idbccMail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicatebccmail` is empty]

CREATE TABLE `phpsyndicateccmail` (
   `idccMail` int(11) not null auto_increment,
   `name` varchar(45),
   `surname` varchar(45),
   `email` varchar(45) not null,
   `MailMessage_idMailMessage` int(11),
   PRIMARY KEY (`idccMail`),
   UNIQUE KEY (`MailMessage_idMailMessage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateccmail` is empty]

CREATE TABLE `phpsyndicateconfig` (
   `item` int(11) not null auto_increment,
   `value` text,
   `editable` int(11),
   `type` varchar(25),
   PRIMARY KEY (`item`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateconfig` is empty]

CREATE TABLE `phpsyndicatedo_not_send_list` (
   `id` int(20) not null auto_increment,
   `email` varchar(20) not null,
   `domain` varchar(20) not null,
   `name` varchar(20),
   `comments` text,
   PRIMARY KEY (`id`),
   KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=25;

INSERT INTO `phpsyndicatedo_not_send_list` (`id`, `email`, `domain`, `name`, `comments`) VALUES 
('13', 'chirag1990', 'gmail.com\r', 'Name', 'Comments'),
('14', 'dgardner', 'reingold.com\r', 'Name', 'Comments'),
('15', 'pursuingwealth', 'gmail.com\r', 'Name', 'Comments'),
('16', 'anna.foster', 'pfeg.org\r', 'Name', 'Comments'),
('17', 'goldstein', 'lexmarc.us\r', 'Name', 'Comments'),
('18', 'info', 'crewind.com\r', 'Name', 'Comments'),
('19', 'admin', 'identity-secured.com', 'Name', 'Comments'),
('20', 'joyner00', 'gmail.com\r', 'Name', 'Comments'),
('21', 'istechi', 'yahoo.com\r', 'Name', 'Comments'),
('22', 'shumfp', 'masteryasia.com', 'Name', 'Comments'),
('23', '', '', 'Name', 'Comments'),
('24', 'vpapakir', 'yahoo.gr', 'Name', 'Comments');

CREATE TABLE `phpsyndicateeventlog` (
   `id` int(11) not null auto_increment,
   `entered` date,
   `page` varchar(255),
   `entry` text,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateeventlog` is empty]

CREATE TABLE `phpsyndicateinterrupted_sessions` (
   `session_id` int(10) not null auto_increment,
   `username` varchar(10),
   `output` text,
   `progress` varchar(10),
   `event_id` int(10),
   PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2131402657;

INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`, `username`, `output`, `progress`, `event_id`) VALUES 
('1288393874', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:06 PM] - (INFO) Email Sent Successfully to [papakiru@cti.gr]\n', '100', '9'),
('2008651162', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:05 PM] - (INFO) Sending from vpapakir@gmail.com to papakiru@cti.gr.\n', '100', '8'),
('1325934578', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:04 PM] - (INFO) Email Sent Successfully to [papakiru@cti.gr]\n', '80', '7'),
('207233170', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:04 PM] - (INFO) Sending from vpapakir@gmail.com to papakiru@cti.gr.\n', '80', '6'),
('247532579', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:03 PM] - (INFO) Email Sent Successfully to [papakiru@cti.gr]\n', '60', '5'),
('524603367', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:03 PM] - (INFO) Sending from vpapakir@gmail.com to papakiru@cti.gr.\n', '60', '4'),
('976919220', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:01 PM] - (INFO) Email Sent Successfully to [papakiru@cti.gr]\n', '40', '3'),
('1174570932', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:00 PM] - (INFO) Sending from vpapakir@gmail.com to papakiru@cti.gr.\n', '20', '0'),
('6352577', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:01 PM] - (INFO) Sending from vpapakir@gmail.com to papakiru@cti.gr.\n', '40', '2'),
('429416263', 'user9', '\n[ Saturday 2nd of March 2013 | 02:17:00 PM] - (INFO) Email Sent Successfully to [papakiru@cti.gr]\n', '20', '1');

CREATE TABLE `phpsyndicatelinktrack` (
   `linkid` int(11) not null auto_increment,
   `messageid` int(11) not null,
   `userid` int(11) not null,
   `url` varchar(255) not null,
   `forward` text,
   `firstclick` date not null,
   `latestclick` datetime not null,
   `clicked` int(11) not null,
   PRIMARY KEY (`linkid`),
   UNIQUE KEY (`messageid`,`userid`,`url`),
   UNIQUE KEY (`messageid`,`userid`,`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicatelinktrack` is empty]

CREATE TABLE `phpsyndicatemailmessage` (
   `idMailMessage` int(11) not null auto_increment,
   `title` varchar(50),
   `body` text,
   `User_idUser` int(11) not null,
   PRIMARY KEY (`idMailMessage`),
   KEY `fk_MailMessage_User` (`User_idUser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicatemailmessage` is empty]

CREATE TABLE `phpsyndicatemessage` (
   `id` int(11) not null auto_increment,
   `subject` varchar(255) not null,
   `fromfield` varchar(255) not null,
   `tofield` varchar(255) not null,
   `replyto` varchar(255) not null,
   `message` text,
   `footer` text,
   `entered` date,
   `modified` datetime not null,
   `status` varchar(255),
   `processed` bigint(20),
   `userselection` text,
   `sent` date,
   `htmlformatted` int(11),
   `sendformat` varchar(20),
   `template` int(11),
   `ashtml` int(11),
   `astext` int(11),
   `astextandhtml` int(11),
   `viewed` int(11),
   `bouncecount` int(11),
   `sendstart` date,
   `aspdf` int(11),
   `astextandpdf` int(11),
   `rsstemplate` varchar(100),
   `owner` int(11),
   `embargo` date,
   `repeatinterval` int(11),
   `repeatuntil` date,
   `textmessage` text,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicatemessage` is empty]

CREATE TABLE `phpsyndicatemessage_attachment` (
   `id` int(10) not null auto_increment,
   `messageid` int(11) not null,
   `attachmentid` int(11) not null,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicatemessage_attachment` is empty]

CREATE TABLE `phpsyndicatemessagedata` (
   `name` varchar(100),
   `id` int(11) not null auto_increment,
   `data` text,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicatemessagedata` is empty]

CREATE TABLE `phpsyndicatephpsyndicate_user` (
   `id` int(11) not null auto_increment,
   `email` varchar(255) not null,
   `confirmed` int(11),
   `entered` date,
   `modified` datetime not null,
   `uniqid` varchar(255),
   `htmlemail` int(11),
   `bouncecount` int(11),
   `subscribepage` int(11),
   `rssfrequency` varchar(100),
   `password` varchar(255),
   `passwordchanged` date,
   `disabled` int(11),
   `extradata` text,
   `foreignkey` varchar(100),
   `blacklisted` int(11),
   PRIMARY KEY (`id`),
   KEY `fkey` (`foreignkey`),
   KEY `index_uniqid` (`uniqid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicatephpsyndicate_user` is empty]

CREATE TABLE `phpsyndicatesendprocess` (
   `id` int(11) not null auto_increment,
   `started` date,
   `modified` datetime not null,
   `alive` int(11),
   `ipaddress` varchar(50),
   `page` varchar(100),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicatesendprocess` is empty]

CREATE TABLE `phpsyndicatesmtp_profile` (
   `profileid` int(20) not null auto_increment,
   `profilenickname` varchar(20) not null,
   `username` varchar(20) not null,
   `smtp_server` varchar(20) not null,
   `smtp_port` varchar(10) not null,
   `smtp_user` varchar(20) not null,
   `smtp_pass` varchar(99) not null,
   `email` varchar(30) not null,
   PRIMARY KEY (`profileid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1602355372;

INSERT INTO `phpsyndicatesmtp_profile` (`profileid`, `profilenickname`, `username`, `smtp_server`, `smtp_port`, `smtp_user`, `smtp_pass`, `email`) VALUES 
('1602355371', 'prof6', 'user9', 'smtp.gmail.com', '587', 'vpapakir', '1985P@ssw0rd1985', 'vpapakir@gmail.com'),
('666400217', 'prof4', 'user9', 'smtp.gmail.com', '587', 'vpapakir', '1985P@ssw0rd1985', 'vpapakir@gmail.com'),
('169977213', 'prof3', 'user9', 'smtp.gmail.com', '587', 'vpapakir', '1985P@ssw0rd1985', 'vpapakir@gmail.com'),
('1272040695', 'prof2', 'user9', 'smtp.gmail.com', '587', 'vpapakir', '1985P@ssw0rd1985', 'vpapakir@gmail.com'),
('5', 'prof1', 'user9', 'smtp.gmail.com', '587', 'vpapakir', '1985P@ssw0rd1985', 'vpapakir@gmail.com');

CREATE TABLE `phpsyndicateuser` (
   `idUser` int(20) not null auto_increment,
   `username` varchar(20) not null,
   `name` varchar(45) not null,
   `surname` varchar(45) not null,
   `email` varchar(45) not null,
   `password` varchar(45) not null,
   `isActivated` tinyint(4) not null,
   `usertype` varchar(45) not null,
   `smtp_server` varchar(255) not null,
   `smtp_port` int(10) not null,
   `smtp_user` varchar(20) not null,
   `smtp_pass` varchar(20) not null,
   PRIMARY KEY (`idUser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=4;

INSERT INTO `phpsyndicateuser` (`idUser`, `username`, `name`, `surname`, `email`, `password`, `isActivated`, `usertype`, `smtp_server`, `smtp_port`, `smtp_user`, `smtp_pass`) VALUES 
('1', 'user9', 'user', '9', 'vpapakir@gmail.com', '8808a13b854c2563da1a5f6cb2130868', '1', 'admin', 'smtp.gmail.com', '587', 'vpapakir', '1985P@ssw0rd1985'),
('2', 'user8', 'user', '8', 'vpapakir@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', '0', 'admin', 'smtp.gmail.com', '587', 'vpapakir', '1985P@ssw0rd1985'),
('3', 'user7', '', '', 'vpapakir@live.com', '3e0469fb134991f8f75a2760e409c6ed', '0', 'user', '', '0', '', '');

CREATE TABLE `phpsyndicateuser_blacklist_data` (
   `email` int(11) not null auto_increment,
   `name` varchar(100) not null,
   `data` text,
   PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateuser_blacklist_data` is empty]

CREATE TABLE `phpsyndicateuser_message_forward` (
   `id` int(11) not null auto_increment,
   `user` int(11) not null,
   `message` int(11) not null,
   `forward` varchar(255),
   `status` varchar(255),
   `time` datetime not null,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateuser_message_forward` is empty]

CREATE TABLE `phpsyndicateuser_user_history` (
   `id` int(11) not null auto_increment,
   `userid` int(11) not null,
   `ip` varchar(255),
   `date` date,
   `summary` varchar(255),
   `detail` text,
   `systeminfo` text,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateuser_user_history` is empty]

CREATE TABLE `phpsyndicateusermessage` (
   `messageid` int(11) not null auto_increment,
   `userid` int(11) not null,
   `entered` date,
   `viewed` date,
   `status` varchar(255),
   PRIMARY KEY (`messageid`,`userid`),
   UNIQUE KEY (`userid`),
   UNIQUE KEY (`messageid`),
   UNIQUE KEY (`entered`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateusermessage` is empty]

CREATE TABLE `phpsyndicateuserstats` (
   `id` int(11) not null auto_increment,
   `unixdate` int(11),
   `item` varchar(255),
   `listid` int(11),
   `value` int(11),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- [Table `phpsyndicateuserstats` is empty]
