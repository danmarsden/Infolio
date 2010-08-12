-- Database: `infolio`

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
 
-- Table structure for table `assets` 
DROP TABLE IF EXISTS `assets`; 
 CREATE TABLE `assets` ( `id` int(11) NOT NULL auto_increment, `title` varchar(255) NOT NULL, `description` varchar(255) default NULL, `href` varchar(255) NOT NULL, `width` mediumint(9) NOT NULL default '0', `height` mediumint(9) NOT NULL default '0', `type` varchar(8) default NULL, `public` tinyint(1) NOT NULL default '0', `enabled` tinyint(1) NOT NULL default '1', `updated_by` int(11) NOT NULL, `updated_time` datetime NOT NULL, `created_by` int(11) NOT NULL, `created_time` datetime NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='tag is comma separated list' AUTO_INCREMENT= 0;

 -- Table structure for table `attachments` 
DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (`id` int(11) NOT NULL auto_increment,`href` varchar(256) NOT NULL, `enabled` tinyint(1) NOT NULL default '1', `page_id` int(11) NOT NULL, `updated_by` int(11) NOT NULL, `updated_time` datetime NOT NULL,`created_by` int(11) NOT NULL, `created_time` datetime NOT NULL, PRIMARY KEY (`id`),KEY `page_id` (`page_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT= 0;

-- Table structure for table `block` 
DROP TABLE IF EXISTS `block`;
CREATE TABLE `block` ( `id` int(11) NOT NULL auto_increment, `title` varchar(255) default NULL, `weight` mediumint(9) NOT NULL default '100', `words0` text, `words1` text, `picture0` int(11) default NULL, `picture1` int(11) default NULL, `page_id` int(11) NOT NULL, `user_id` int(11) default NULL, `block_layout_id` int(11) NOT NULL default '1', `created_by` int(11) default NULL, `created_time` datetime default NULL, `updated_by` int(11) default NULL, `updated_time` datetime default NULL, PRIMARY KEY (`id`), KEY `page_id` (`page_id`), KEY `user_id` (`user_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT= 0;

-- Table structure for table `block_layout` 
DROP TABLE IF EXISTS `block_layout`;
CREATE TABLE `block_layout` ( `id` int(11) NOT NULL auto_increment, `html` varchar(512) NOT NULL, `description` varchar(64) NOT NULL, `created_by` int(11) default NULL, `created_time` datetime default NULL, `updated_by` int(11) default NULL, `updated_time` datetime default NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT= 0;

-- Default data for table `block_layout`
INSERT INTO `block_layout` (`id`, `html`, `description`, `created_by`, `created_time`, `updated_by`, `updated_time`) VALUES
(1, '<div class="box-image"><image0></div><div class="box-text"><words0></div>', 'Picture on the left. Text on the right.', 2, NULL, 2, '2009-10-29 16:10:27'),
(2, '<div class="box-text"><words0></div><div class="box-image"><image0></div>', 'Text on the left. Picture on the right.', 2, NULL, 2, '2009-10-29 16:11:02'),
(3, '<div class="box-text"><words0></div>', 'Just text.', 2, '2009-05-27 23:31:16', 2, '2009-10-29 16:31:00'),
(4, '<div class="box-image0"><image0></div><div class="box-image1"><image1></div><div class="box-text"><words0></div>', 'Two pictures. One on the left and the right.', 2, '2009-05-29 10:39:06', 2, '2009-10-29 16:14:06');

-- Table structure for table `collection` 
DROP TABLE IF EXISTS `collection`;
CREATE TABLE `collection` ( `id` int(11) NOT NULL auto_increment, `asset_id` int(11) default NULL, `user_id` int(11) default NULL, `group_id` int(11) default NULL, `created_by` int(11) NOT NULL, `created_time` datetime NOT NULL, PRIMARY KEY (`id`), KEY `user_id` (`user_id`), KEY `group_id` (`group_id`), KEY `asset_id` (`asset_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT= 0;

-- Table structure for table `favourite_assets` 
DROP TABLE IF EXISTS `favourite_assets`;
CREATE TABLE `favourite_assets` ( `id` int(11) NOT NULL auto_increment, `user_id` int(11) NOT NULL, `asset_id` int(11) NOT NULL, PRIMARY KEY (`id`), KEY `user_id` (`user_id`), KEY `asset_id` (`asset_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- Default data for table `favourite_assets`
INSERT INTO `favourite_assets` (`id`, `user_id`, `asset_id`) VALUES (1, 2, 100);

-- Table structure for table `graphical_passwords` 
DROP TABLE IF EXISTS `graphical_passwords`;
CREATE TABLE `graphical_passwords` ( `id` int(11) NOT NULL auto_increment, `user_id` int(11) NOT NULL, `picture_asset_id` int(11) default NULL, `click_accuracy` int(11) default NULL, `click_number_of` int(11) NOT NULL default '1', PRIMARY KEY (`id`), KEY `user_id` (`user_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- Table structure for table `graphical_password_coords` 
DROP TABLE IF EXISTS `graphical_password_coords`;
CREATE TABLE `graphical_password_coords` ( `id` int(11) NOT NULL auto_increment, `graphical_passwords_id` int(11) NOT NULL, `x` int(11) NOT NULL, `y` int(11) NOT NULL, PRIMARY KEY (`id`), KEY `graphical_passwords_id` (`graphical_passwords_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT= 0;

-- Table structure for table `groups` 
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (`id` int(11) NOT NULL auto_increment, `title` varchar(255) default NULL, `description` text, `institution_id` int(11) default NULL, `created_by` int(11) default NULL, `updated_by` int(11) default NULL, `created_time` datetime default NULL, `updated_time` datetime default NULL, PRIMARY KEY (`id`), KEY `institution_id` (`institution_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT= 0;

-- Table structure for table `group_members` 
DROP TABLE IF EXISTS `group_members`;
CREATE TABLE `group_members` (`id` int(11) NOT NULL auto_increment, `user_id` int(11) NOT NULL,`group_id` int(11) NOT NULL, PRIMARY KEY (`id`), KEY `user_id` (`user_id`), KEY `group_id` (`group_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT= 0;
-- Table structure for table `institution`
DROP TABLE IF EXISTS `institution`;
CREATE TABLE `institution` ( `id` int(11) NOT NULL auto_increment, `name` varchar(255) default NULL, `url` varchar(64) default NULL, `asset_id` int(11) default NULL, `created_by` int(11) default NULL, `created_time` datetime default NULL, `updated_by` int(11) default NULL, `updated_time` datetime default NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- Default data for table `institution`
INSERT INTO `institution` (`id`, `name`, `url`, `asset_id`, `created_by`, `created_time`, `updated_by`, `updated_time`) VALUES (1, 'Rix Centre', 'rix', 96, 2, NULL, 2, NULL);

-- Table structure for table `page` 
DROP TABLE IF EXISTS `page`;
CREATE TABLE `page` (`id` int(11) NOT NULL auto_increment, `title` varchar(255) default NULL, `tab_id` int(11) NOT NULL, `user_id` int(11) default NULL,`enabled` tinyint(1) NOT NULL default '1',`updated_by` int(11) default NULL,`updated_time` datetime default NULL,`created_by` int(11) NOT NULL,`created_time` datetime default NULL,PRIMARY KEY (`id`),KEY `tab_id` (`tab_id`), KEY `user_id` (`user_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT= 0;

-- Table structure for table `system_log` 
DROP TABLE IF EXISTS `system_log`;
CREATE TABLE `system_log` (`id` int(10) unsigned NOT NULL auto_increment, `ip` varchar(32) NOT NULL,`user_id` int(11) default NULL,`institution_id` int(11) default NULL, `username` varchar(64) default NULL,`created_time` datetime NOT NULL,`message_type` varchar(16) NOT NULL,`message` varchar(256) NOT NULL,PRIMARY KEY (`id`), KEY `user_id` (`user_id`),KEY `institution_id` (`institution_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT= 0;

-- Table structure for table `tab` 
DROP TABLE IF EXISTS `tab`;
CREATE TABLE `tab` (`ID` int(11) NOT NULL auto_increment, `name` varchar(255) default NULL, `slug` varchar(255) NOT NULL COMMENT 'URL safe version of name', `description` varchar(255) default NULL,`weight` smallint(6) NOT NULL default '0', `owner` int(11) NOT NULL default '0' COMMENT 'for permissions',`template_id` int(11) default NULL,`user_id` int(11) default NULL,`asset_id` int(11) default NULL, `enabled` tinyint(1) NOT NULL default '1',`created_by` int(11) default NULL,`created_time` datetime default NULL,`updated_by` int(11) default NULL,`updated_time` datetime default NULL,PRIMARY KEY (`ID`),KEY `template_id` (`template_id`),KEY `user_id` (`user_id`),KEY `asset_id` (`asset_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=162 ;

-- Default data for table `tab`
INSERT INTO `tab` (`ID`, `name`, `slug`, `description`, `weight`, `owner`, `template_id`, `user_id`, `asset_id`, `enabled`, `created_by`, `created_time`, `updated_by`, `updated_time`) VALUES (1, 'About me', 'About-me', 'Information about me', -999, 2, 0, NULL, 97, 1, 2, '2008-12-08 09:22:19', 2, '2008-12-08 09:22:19');

-- Table structure for table `tags` 
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` ( `id` int(11) NOT NULL auto_increment,`name` varchar(28) NOT NULL,`institution_id` int(11) NOT NULL, `created_by` int(11) NOT NULL, `created_time` datetime NOT NULL,`updated_by` int(11) NOT NULL,`updated_time` datetime NOT NULL, PRIMARY KEY (`id`), KEY `institution_id` (`institution_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- Table structure for table `tags_assets` 
DROP TABLE IF EXISTS `tags_assets`;
CREATE TABLE `tags_assets` (`id` int(11) NOT NULL auto_increment,`asset_id` int(11) NOT NULL,`tag_id` int(11) NOT NULL,`user_id` int(11) default NULL,PRIMARY KEY (`id`), KEY `asset_id` (`asset_id`),KEY `tag_id` (`tag_id`),KEY `user_id` (`user_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- Table structure for table `templates` 
DROP TABLE IF EXISTS `templates`;
CREATE TABLE `templates` ( `id` int(11) NOT NULL auto_increment,`title` varchar(55) default NULL, `description` text, `locked` tinyint(1) NOT NULL default '0',`institution_id` int(11) NOT NULL,`enabled` tinyint(1) NOT NULL default '1',`created_time` datetime default NULL,`created_by` int(11) NOT NULL,`updated_time` datetime default NULL,`updated_by` int(11) default NULL,PRIMARY KEY (`id`),KEY `institution_id` (`institution_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- Table structure for table `template_viewers` 
DROP TABLE IF EXISTS `template_viewers`;
CREATE TABLE `template_viewers` ( `id` int(11) NOT NULL auto_increment,`template_id` int(11) NOT NULL, `user_id` int(11) default NULL,`group_id` int(11) default NULL,PRIMARY KEY (`id`),KEY `template_id` (`template_id`),KEY `user_id` (`user_id`),KEY `group_id` (`group_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- Table structure for table `user` 
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (`ID` int(11) NOT NULL auto_increment, `firstName` varchar(55) default NULL,`lastName` varchar(55) default NULL, `email` varchar(55) default NULL, `description` varchar(512) default NULL, `colour` varchar(16) NOT NULL default 'red', `size` varchar(8) default NULL, `username` varchar(55) default NULL, `password` varchar(55) default NULL, `userType` enum('admin','super admin','teacher','supporter','student') default NULL, `institution_id` int(11) default NULL, `switch_enabled` tinyint(1) NOT NULL default '0', `switch_shape` int(11) NOT NULL default '-1', `switch_photo` int(11) NOT NULL default '-1', `blocked` tinyint(1) NOT NULL default '0', `enabled` tinyint(1) NOT NULL default '1', `created_time` datetime default NULL, `updated_time` datetime default NULL, `updated_by` int(11) default NULL,`created_by` int(11) default NULL,`profile_picture_id` int(11) NOT NULL,PRIMARY KEY (`ID`),KEY `institution_id` (`institution_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=227 ;

-- Default data for table `user`
INSERT INTO `user` (`ID`, `firstName`, `lastName`, `email`, `description`, `colour`, `size`, `username`, `password`, `userType`, `institution_id`, `switch_enabled`, `switch_shape`, `switch_photo`, `blocked`, `enabled`, `created_time`, `updated_time`, `updated_by`, `created_by`, `profile_picture_id`) VALUES (1, 'Main', 'Admin', 'admin@', '', 'red', '', 'Admin', '01cfcd4f6b8770febfb40cb906715822', 'super admin', 1, 0, 0, 0, 0, 1, '2008-07-29 10:45:04', '2009-10-26 02:43:17', 1, NULL, 0);

-- Structure for view `vassetcounts` 
DROP TABLE IF EXISTS `vassetcounts`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `infolio`.`vassetcounts` AS select `infolio`.`collection`.`asset_id` AS `asset_id`,count(`infolio`.`collection`.`asset_id`) AS `count` from `infolio`.`collection` group by `infolio`.`collection`.`asset_id`;

-- Structure for view `vassetswithcounts` 
DROP TABLE IF EXISTS `vassetswithcounts`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `infolio`.`vassetswithcounts` AS select `infolio`.`assets`.`id` AS `id`,`infolio`.`assets`.`title` AS `title`,`infolio`.`assets`.`description` AS `description`,`infolio`.`assets`.`href` AS `href`,`infolio`.`assets`.`width` AS `width`,`infolio`.`assets`.`height` AS `height`,`infolio`.`assets`.`type` AS `type`,`infolio`.`assets`.`public` AS `public`,`infolio`.`assets`.`enabled` AS `enabled`,`infolio`.`assets`.`updated_by` AS `updated_by`,`infolio`.`assets`.`updated_time` AS `updated_time`,`infolio`.`assets`.`created_by` AS `created_by`,`infolio`.`assets`.`created_time` AS `created_time`,ifnull(`vassetcounts`.`count`,0) AS `count`,`infolio`.`institution`.`id` AS `institution_id`,`infolio`.`institution`.`url` AS `institution_url` from (((`infolio`.`assets` left join `infolio`.`vassetcounts` on((`infolio`.`assets`.`id` = `vassetcounts`.`asset_id`))) join `infolio`.`user` on((`infolio`.`user`.`ID` = `infolio`.`assets`.`created_by`))) join `infolio`.`institution` on((`infolio`.`user`.`institution_id` = `infolio`.`institution`.`id`)));