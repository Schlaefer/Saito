# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.6.17-1~dotdeb.1)
# Database: default
# Generation Time: 2014-04-15 09:49:05 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table bookmarks
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bookmarks`;

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `comment` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
 	PRIMARY KEY (`id`),
  KEY `bookmarks_entryId_userId` (`entry_id`,`user_id`),
  KEY `bookmarks_userId` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;

INSERT INTO `bookmarks` (`id`, `user_id`, `entry_id`, `comment`, `created`, `modified`)
VALUES
	(1,3,1,'','2012-08-07 09:51:45','2012-08-07 09:51:45'),
	(2,3,3,'< Comment 2','2012-08-07 19:51:45','2012-08-07 19:51:45'),
	(3,1,1,'Comment 3','2012-08-07 09:51:45','2012-08-07 09:51:45'),
	(4,2,4,'Comment 4','2012-08-07 09:51:45','2012-08-07 09:51:45'),
	(5,3,11,'<BookmarkComment','2012-08-07 09:51:45','2012-08-07 09:51:45'),
	(6,1,12,'<script>alert(\'foo\');</script>','2014-03-12 08:01:25','2014-03-12 08:01:36');

/*!40000 ALTER TABLE `bookmarks` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_order` int(11) NOT NULL DEFAULT '0',
  `category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accession` int(4) NOT NULL DEFAULT '0',
  `thread_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;

INSERT INTO `categories` (`id`, `category_order`, `category`, `description`, `accession`, `thread_count`)
VALUES
	(1,1,'Admin','',2,1),
	(2,3,'Ontopic','',0,4),
	(3,2,'Another Ontopic','',0,0),
	(4,4,'Offtopic','',1,1),
	(5,4,'Trash','',1,0);

/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table ecaches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ecaches`;

CREATE TABLE `ecaches` (
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `key` varchar(128) NOT NULL,
  `value` mediumblob NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `ecaches` WRITE;
/*!40000 ALTER TABLE `ecaches` DISABLE KEYS */;

INSERT INTO `ecaches` (`created`, `modified`, `key`, `value`)
VALUES
	('2012-08-06 12:31:29','2012-08-06 12:31:29','Lorem ipsum dolor sit amet',X'4C6F72656D20697073756D20646F6C6F722073697420616D6574'),
	('2014-03-12 07:50:45','2014-04-15 09:46:42','EntrySub',X'613A303A7B7D');

/*!40000 ALTER TABLE `ecaches` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table entries
# ------------------------------------------------------------

DROP TABLE IF EXISTS `entries`;

CREATE TABLE `entries` (
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tid` int(11) NOT NULL DEFAULT '0',
  `uniqid` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_answer` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edited` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edited_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `text` text COLLATE utf8_unicode_ci,
  `email_notify` int(4) DEFAULT '0',
  `locked` int(4) DEFAULT '0',
  `fixed` int(4) DEFAULT '0',
  `views` int(11) DEFAULT '0',
  `flattr` tinyint(1) DEFAULT NULL,
  `nsfw` tinyint(1) DEFAULT NULL,
  `ip` varchar(39) COLLATE utf8_unicode_ci DEFAULT NULL,
  `solves` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `entries_userId` (`user_id`),
  KEY `last_answer` (`last_answer`),
  KEY `pft` (`pid`,`fixed`,`time`,`category`),
  KEY `pfl` (`pid`,`fixed`,`last_answer`,`category`),
  KEY `pid_category` (`pid`,`category`),
  KEY `entries_userId_time` (`time`,`user_id`),
  FULLTEXT KEY `fulltext_search` (`subject`,`name`,`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `entries` WRITE;
/*!40000 ALTER TABLE `entries` DISABLE KEYS */;

INSERT INTO `entries` (`created`, `modified`, `id`, `pid`, `tid`, `uniqid`, `time`, `last_answer`, `edited`, `edited_by`, `user_id`, `name`, `subject`, `category`, `text`, `email_notify`, `locked`, `fixed`, `views`, `flattr`, `nsfw`, `ip`, `solves`)
VALUES
	(NULL,NULL,1,0,1,NULL,'2000-01-01 20:00:00','2000-01-04 20:02:00','2014-03-11 12:45:48',NULL,3,NULL,'First_Subject',2,'First_Text',0,0,0,0,NULL,NULL,NULL,0),
	(NULL,NULL,2,1,1,NULL,'2000-01-01 20:01:00','2000-01-01 20:01:00','2014-03-11 12:45:48',NULL,2,NULL,'Second_Subject',2,'Second_Text',0,0,0,0,NULL,NULL,NULL,0),
	(NULL,NULL,3,2,1,NULL,'2000-01-01 20:02:00','2000-01-01 20:02:00','2000-01-01 20:04:00','Ulysses',3,'Ulysses','Third_Subject',2,'< Third_Text',0,0,0,0,NULL,NULL,'1.1.1.1',0),
	(NULL,NULL,7,9,1,NULL,'2000-01-02 20:03:00','2000-01-02 20:03:00','2014-03-11 12:45:48',NULL,3,'Ulysses','Fouth_Subject',2,'Fourth_Text',0,0,0,0,NULL,NULL,'1.1.1.1',0),
	(NULL,NULL,8,1,1,NULL,'2000-01-03 20:02:00','2000-01-03 20:02:00','2014-03-11 12:45:48',NULL,3,'Ulysses','Fifth_Subject',2,'Fifth_Text',0,0,0,0,NULL,NULL,'1.1.1.1',0),
	(NULL,NULL,9,2,1,NULL,'2000-01-04 20:02:00','2000-01-04 20:02:00','2014-03-11 12:45:48',NULL,3,'Ulysses','Sixth_Subject',2,'Sixth_Text',0,0,0,0,NULL,NULL,'1.1.1.1',0),
	(NULL,NULL,4,0,4,NULL,'2000-01-01 10:00:00','2000-01-04 20:02:00','2014-03-11 12:45:48',NULL,1,NULL,'Second Thread First_Subject',4,'',0,1,0,0,NULL,NULL,NULL,0),
	(NULL,NULL,5,4,4,NULL,'2000-01-04 20:02:00','2000-01-04 20:02:00','0000-00-00 00:00:00',NULL,3,'Ulysses','Second Thread Second_Subject',4,'',0,1,0,0,NULL,NULL,'1.1.1.1',0),
	(NULL,NULL,6,0,6,NULL,'2000-01-01 11:00:00','2000-01-01 11:00:00','0000-00-00 00:00:00',NULL,1,'Alice','Third Thread First_Subject',1,'',0,0,0,0,NULL,NULL,'1.1.1.3',0),
	(NULL,NULL,10,0,10,NULL,'2000-01-01 10:59:00','2000-01-01 10:59:00','0000-00-00 00:00:00',NULL,3,NULL,'First_Subject',2,'<script>alert(\'foo\');<script>',0,1,0,0,NULL,NULL,NULL,0),
	(NULL,NULL,11,0,11,NULL,'2000-01-01 10:59:00','2000-01-01 10:59:00','0000-00-00 00:00:00',NULL,7,NULL,'&<Subject',2,'&<Text',0,0,0,1,NULL,NULL,NULL,0),
	('2014-02-24 09:01:15','2014-03-02 16:15:13',12,0,12,NULL,'2014-02-24 09:01:15','2014-02-24 09:01:15','0000-00-00 00:00:00',NULL,100,'test&nbsp;bar<script>alert(\'foo\');</script>','<script>alert(\'foo\');</script>',2,'<script>alert(\'foo\');</script>',0,0,0,32,NULL,0,'::1',0);

/*!40000 ALTER TABLE `entries` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table esevents
# ------------------------------------------------------------

DROP TABLE IF EXISTS `esevents`;

CREATE TABLE `esevents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` int(11) NOT NULL,
  `event` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_event` (`subject`,`event`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `esevents` WRITE;
/*!40000 ALTER TABLE `esevents` DISABLE KEYS */;

INSERT INTO `esevents` (`id`, `subject`, `event`)
VALUES
	(1,1,1),
	(2,1,2),
	(3,2,1),
	(4,1,3);

/*!40000 ALTER TABLE `esevents` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table esnotifications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `esnotifications`;

CREATE TABLE `esnotifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `esevent_id` int(11) NOT NULL,
  `esreceiver_id` int(11) NOT NULL,
  `deactivate` int(8) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid_esreceiverid` (`user_id`,`esreceiver_id`),
  KEY `eseventid_esreceiverid_userid` (`esevent_id`,`esreceiver_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `esnotifications` WRITE;
/*!40000 ALTER TABLE `esnotifications` DISABLE KEYS */;

INSERT INTO `esnotifications` (`id`, `user_id`, `esevent_id`, `esreceiver_id`, `deactivate`)
VALUES
	(1,1,1,1,1234),
	(2,1,1,2,2234),
	(3,3,1,1,3234),
	(4,3,4,1,4234),
	(5,2,4,1,5234),
	(6,2,2,1,6234),
	(7,4,3,1,7234);

/*!40000 ALTER TABLE `esnotifications` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table settings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `value` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;

INSERT INTO `settings` (`name`, `value`)
VALUES
	('autolink','1'),
	('api_crossdomain',''),
	('api_enabled','1'),
	('bbcode_img','1'),
	('block_user_ui','1'),
	('category_chooser_global','0'),
	('category_chooser_user_override','1'),
	('edit_delay','3'),
	('edit_period','20'),
	('embedly_enabled','0'),
	('embedly_key',''),
	('email_contact', ''),
	('email_register', ''),
	('email_system', ''),
	('flattr_category','text'),
	('flattr_enabled','1'),
	('flattr_language','de_DE'),
	('forum_disabled','0'),
	('forum_disabled_text','We\'ll back soon'),
	('forum_email','forum_email@example.com'),
	('forum_name','macnemo'),
	('map_enabled','0'),
	('map_api_key',''),
	('quote_symbol','>'),
	('signature_separator','⁂'),
	('smilies','1'),
	('stopwatch_get','0'),
	('store_ip','0'),
	('store_ip_anonymized','1'),
	('text_word_maxlength','120'),
	('timezone','UTC'),
	('topics_per_page','20'),
	('tos_enabled','1'),
	('tos_url','http://example.com/tos-url.html/'),
	('subject_maxlength','100'),
	('thread_depth_indent','25'),
	('shoutbox_enabled','1'),
	('shoutbox_max_shouts','5'),
	('upload_max_img_size','300'),
	('upload_max_number_of_uploads','10'),
	('userranks_ranks','100=Rookie|101=Veteran'),
	('userranks_show','1'),
	('video_domains_allowed','youtube | youtube-nocookie | vimeo');

/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table shouts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `shouts`;

CREATE TABLE `shouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

LOCK TABLES `shouts` WRITE;
/*!40000 ALTER TABLE `shouts` DISABLE KEYS */;

INSERT INTO `shouts` (`id`, `created`, `modified`, `text`, `user_id`, `time`)
VALUES
	(2,NULL,NULL,'Lorem ipsum dolor sit amet',1,'2013-02-08 11:49:31'),
	(3,NULL,NULL,'Lorem ipsum dolor sit amet',1,'2013-02-08 11:49:31'),
	(4,NULL,NULL,'<script></script>[i]italic[/i]',1,'2013-02-08 11:49:31');

/*!40000 ALTER TABLE `shouts` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table smiley_codes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `smiley_codes`;

CREATE TABLE `smiley_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `smiley_id` int(11) NOT NULL DEFAULT '0',
  `code` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `smiley_codes` WRITE;
/*!40000 ALTER TABLE `smiley_codes` DISABLE KEYS */;

INSERT INTO `smiley_codes` (`id`, `smiley_id`, `code`)
VALUES
	(1,1,':-)'),
	(2,1,';-)'),
	(3,2,';)');

/*!40000 ALTER TABLE `smiley_codes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table smilies
# ------------------------------------------------------------

DROP TABLE IF EXISTS `smilies`;

CREATE TABLE `smilies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order` int(4) NOT NULL DEFAULT '0',
  `icon` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `image` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `smilies` WRITE;
/*!40000 ALTER TABLE `smilies` DISABLE KEYS */;

INSERT INTO `smilies` (`id`, `order`, `icon`, `image`, `title`)
VALUES
	(1,2,'smile_icon.png','smile_image.png','Smile'),
	(2,1,'wink.png','','Wink');

/*!40000 ALTER TABLE `smilies` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table uploads
# ------------------------------------------------------------

DROP TABLE IF EXISTS `uploads`;

CREATE TABLE `uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `uploads` WRITE;
/*!40000 ALTER TABLE `uploads` DISABLE KEYS */;

INSERT INTO `uploads` (`id`, `name`, `type`, `size`, `created`, `modified`, `user_id`)
VALUES
	(1,'3_upload_test.png','png',10000,NULL,NULL,3),
	(2,'1_upload_test.png','jpg',20000,NULL,NULL,1);

/*!40000 ALTER TABLE `uploads` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_read
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_read`;

CREATE TABLE `user_read` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `entry_id` (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table useronline
# ------------------------------------------------------------

DROP TABLE IF EXISTS `useronline`;

CREATE TABLE `useronline` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_id` int(11) DEFAULT NULL,
  `logged_in` tinyint(1) NOT NULL,
  `time` int(14) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `useronline_uuid` (`uuid`),
  KEY `useronline_userId` (`user_id`),
  KEY `useronline_loggedIn` (`logged_in`)
) ENGINE=MEMORY AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_real_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hide_email` int(4) DEFAULT '0',
  `user_hp` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_place` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_place_lat` float DEFAULT NULL,
  `user_place_lng` float DEFAULT NULL,
  `user_place_zoom` int(4) DEFAULT NULL,
  `signature` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `profile` text CHARACTER SET utf8,
  `entry_count` int(11) NOT NULL DEFAULT '0',
  `logins` int(11) NOT NULL DEFAULT '0',
  `last_login` timestamp DEFAULT NULL,
  `registered` timestamp DEFAULT NULL,
  `last_refresh` datetime DEFAULT NULL,
  `last_refresh_tmp` datetime DEFAULT NULL,
  `personal_messages` int(4) DEFAULT '0',
  `user_lock` int(4) DEFAULT '0',
  `activate_code` int(7) NOT NULL,
  `user_signatures_hide` int(4) DEFAULT '0',
  `user_signatures_images_hide` int(4) DEFAULT '0',
  `user_forum_refresh_time` int(11) DEFAULT '0',
  `user_automaticaly_mark_as_read` int(4) DEFAULT '1',
  `user_sort_last_answer` int(4) DEFAULT '0',
  `user_color_new_postings` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_color_actual_posting` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_color_old_postings` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_theme` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_show_own_signature` int(4) DEFAULT '0',
  `slidetab_order` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `show_userlist` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'stores if userlist is shown in front layout',
  `show_recentposts` tinyint(1) NOT NULL DEFAULT '0',
  `show_recententries` tinyint(1) NOT NULL,
  `show_shoutbox` tinyint(1) NOT NULL DEFAULT '0',
  `inline_view_on_click` tinyint(1) NOT NULL DEFAULT '0',
  `user_show_thread_collapsed` tinyint(1) NOT NULL DEFAULT '0',
  `flattr_uid` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
  `flattr_allow_user` tinyint(1) DEFAULT NULL,
  `flattr_allow_posting` tinyint(1) DEFAULT NULL,
  `user_category_override` tinyint(1) NOT NULL DEFAULT '0',
  `user_category_active` int(11) NOT NULL DEFAULT '0',
  `user_category_custom` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `user_type`, `username`, `user_real_name`, `password`, `user_email`, `hide_email`, `user_hp`, `user_place`, `user_place_lat`, `user_place_lng`, `user_place_zoom`, `signature`, `profile`, `entry_count`, `logins`, `last_login`, `last_logout`, `registered`, `last_refresh`, `last_refresh_tmp`, `personal_messages`, `user_lock`, `activate_code`, `user_signatures_hide`, `user_signatures_images_hide`, `user_forum_refresh_time`, `user_automaticaly_mark_as_read`, `user_sort_last_answer`, `user_color_new_postings`, `user_color_actual_posting`, `user_color_old_postings`, `user_show_own_signature`, `slidetab_order`, `show_userlist`, `show_recentposts`, `show_recententries`, `show_shoutbox`, `inline_view_on_click`, `user_show_thread_collapsed`, `flattr_uid`, `flattr_allow_user`, `flattr_allow_posting`, `user_category_override`, `user_category_active`, `user_category_custom`, `user_theme`)
VALUES
	(1, 'admin', 'Alice', NULL, '098f6bcd4621d373cade4e832627b4f6', 'alice@example.com', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2009-01-01 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, '', NULL),
	(2, 'mod', 'Mitch', NULL, '098f6bcd4621d373cade4e832627b4f6', 'mitch@example.com', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2009-01-01 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, '', NULL),
	(3, 'user', 'Ulysses', NULL, '098f6bcd4621d373cade4e832627b4f6', 'ulysses@example.com', 0, NULL, NULL, 21.61, -158.096, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2009-01-01 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, '', NULL),
	(4, 'user', 'Change Password Test', NULL, '098f6bcd4621d373cade4e832627b4f6', 'cpw@example.com', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2009-01-01 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 1, 0, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, '', NULL),
	(5, 'user', 'Uma', NULL, '098f6bcd4621d373cade4e832627b4f6', 'uma@example.com', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2009-01-01 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 1, 0, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, '', NULL),
	(6, 'admin', 'Second Admin', NULL, '$2a$10$7d0000dd8a37f797acb53OY.oaPgJ2vV4PE3.VBpmsm9OM/zMlzNq', 'second admin@example.com', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2010-09-01 11:12:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 1, 0, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, '', NULL),
	(7, 'user', '&<Username', '&<RealName', '098f6bcd4621d373cade4e832627b4f6', 'xss@example.com', 0, '&<Homepage', '&<Place', NULL, NULL, NULL, '&<Signature', '&<Profile', 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2010-09-02 11:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 1, 0, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, '', NULL),
	(8, 'user', 'Walt', NULL, '098f6bcd4621d373cade4e832627b4f6', 'walt@example.com', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2009-01-01 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 1, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, '', NULL),
	(9, 'user', 'Diane', NULL, '098f6bcd4621d373cade4e832627b4f6', 'diane@example.com', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2009-01-01 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 1548, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, '', NULL),
	(100, 'user', '<script>alert(\'foo\');</script>', '<script>alert(\'foo\');</script>', '$2a$10$Z.nfw.s8uJkC1h3IhSgbM.QPGdXqpfhgVcOyc6suTbg8qtRd0kV16', 'alert@example.com', 0, '<script>alert(\'foo\');</script>', '<script>alert(\'foo\');</script>', NULL, NULL, NULL, '<script>alert(\'foo\');</script>', '<script>alert(\'foo\');</script>', 230, 7, '2014-03-02 17:13:11', '0000-00-00 00:00:00', '2014-02-24 08:21:28', '0000-00-00 00:00:00', '2014-03-02 17:17:57', 0, 0, 0, 0, 0, 0, 1, 0, '#', '#', '#', 0, NULL, 1, 1, 1, 1, 0, 0, '', 0, 0, 0, 0, '', NULL),
	(101, 'user', 'test', '', '$2a$10$nBMsWfkWyXeZpBN8O0Gcb.x6ioj9cpbAkaULLXLLhI0E7opHJ4t3.', 'test@example.com', 0, '', '', NULL, NULL, NULL, '', '', 212, 405, '2014-04-27 07:58:42', '0000-00-00 00:00:00', '2012-09-11 05:01:46', '2014-03-04 15:04:57', '2014-04-27 08:05:58', 0, 0, 0, 0, 0, 0, 1, 0, '#', '#', '#', 0, 'a:4:{i:0;s:17:\"slidetab_userlist\";i:1;s:17:\"slidetab_shoutbox\";i:2;s:20:\"slidetab_recentposts\";i:3;s:22:\"slidetab_recententries\";}', 1, 0, 0, 1, 0, 0, '', 0, 0, 0, 0, '', 'Paz');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
