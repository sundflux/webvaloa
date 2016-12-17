-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 04, 2014 at 08:19 PM
-- Server version: 5.5.38
-- PHP Version: 5.4.4-14+deb7u14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `vanillavaloa`
--

-- --------------------------------------------------------

--
-- Table structure for table `alias`
--

CREATE TABLE IF NOT EXISTS `alias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(32) NOT NULL COMMENT 'Alias for controller',
  `controller` varchar(32) NOT NULL COMMENT 'Controller name',
  `method` varchar(32) DEFAULT NULL COMMENT 'Method in controller (optional)',
  `locale` varchar(6) NOT NULL DEFAULT 'en_US' COMMENT 'Locale for this alias. en_US aliases work with all other languages too.',
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`,`controller`,`method`),
  KEY `locale` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='URL aliases' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `category` varchar(48) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`category`,`deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Categories' AUTO_INCREMENT=2 ;

ALTER TABLE `category` ADD `layout` VARCHAR( 64 ) NULL DEFAULT NULL AFTER `category` ;
ALTER TABLE `category` ADD `layout_list` VARCHAR( 64 ) NULL DEFAULT NULL AFTER `layout` ;
ALTER TABLE `category` ADD `template` VARCHAR( 64 ) NULL AFTER `category` ;
--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `parent_id`, `category`, `deleted`) VALUES
(1, NULL, 'Uncategorized', 0);

-- --------------------------------------------------------

--
-- Table structure for table `category_field_group`
--

CREATE TABLE IF NOT EXISTS `category_field_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_group_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `recursive` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_group_id` (`field_group_id`,`category_id`,`recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `category_field_group`
--

INSERT INTO `category_field_group` (`id`, `field_group_id`, `category_id`, `recursive`) VALUES
(1, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `category_tag`
--

CREATE TABLE IF NOT EXISTS `category_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`category_id`,`tag_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Links tags to content' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `category_tag`
--

INSERT INTO `category_tag` (`id`, `category_id`, `tag_id`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `component`
--

CREATE TABLE IF NOT EXISTS `component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller` varchar(32) NOT NULL COMMENT 'Controller name',
  `system_component` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'System components cannot be directly edited or deleted.',
  `blocked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Blocked disables the component',
  PRIMARY KEY (`id`),
  UNIQUE KEY `controller` (`controller`),
  KEY `system_component` (`system_component`,`blocked`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Components' AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `component_role`
--

CREATE TABLE IF NOT EXISTS `component_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `component_id` int(11) NOT NULL COMMENT 'Component ID',
  `role_id` int(11) NOT NULL COMMENT 'Role ID',
  PRIMARY KEY (`id`),
  KEY `component_id` (`component_id`,`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Links components to roles' AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `configuration`
--

CREATE TABLE IF NOT EXISTS `configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `component_id` int(11) DEFAULT NULL,
  `type` enum('int','select','checkbox','text') NOT NULL DEFAULT 'text',
  `key` varchar(48) NOT NULL,
  `value` varchar(255) NOT NULL,
  `values` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `component` (`component_id`),
  KEY `values` (`values`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `configuration`
--

INSERT INTO `configuration` (`id`, `component_id`, `type`, `key`, `value`, `values`) VALUES
(1, NULL, 'select', 'webvaloa_fixed_administrator_bar', 'yes', 'yes,no'),
(2, NULL, 'select', 'webvaloa_hide_developer_tools', 'no', 'yes,no'),
(3, NULL, 'text', 'webmaster_email', '', NULL),
(4, NULL, 'text', 'sitename', '', NULL),
(5, NULL, 'text', 'webvaloa_branding', 'webvaloa-logo.png', NULL),
(7, NULL, 'select', 'template', 'default', 'default'),
(6, NULL, 'select', 'template_backend', 'no', 'yes,no');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Content ID',
  `title` varchar(128) DEFAULT NULL,
  `publish_up` datetime NOT NULL COMMENT 'Start publishing at this time',
  `publish_down` datetime NOT NULL COMMENT 'Stop publishing at this time',
  `published` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is this article published or not?',
  `associated_content_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL COMMENT 'User ID of content creator',
  `locale` varchar(6) NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`publish_up`,`publish_down`,`published`,`user_id`),
  KEY `associated_content_id` (`associated_content_id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Content' AUTO_INCREMENT=1 ;

ALTER TABLE `content` ADD `alias` VARCHAR( 64 ) NULL DEFAULT NULL AFTER `title` ,
ADD INDEX ( `alias` ) ;

-- --------------------------------------------------------

--
-- Table structure for table `content_category`
--

CREATE TABLE IF NOT EXISTS `content_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Link content to categories' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `content_field_value`
--

CREATE TABLE IF NOT EXISTS `content_field_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `value` text NOT NULL,
  `locale` varchar(6) NOT NULL DEFAULT '*',
  `ordering` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`,`field_id`),
  KEY `locale` (`locale`),
  KEY `ordering` (`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `content_tag`
--

CREATE TABLE IF NOT EXISTS `content_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Links tags to content' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `field`
--

CREATE TABLE IF NOT EXISTS `field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_group_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `translation` varchar(64) NOT NULL,
  `repeatable` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(64) NOT NULL DEFAULT 'text',
  `ordering` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `field_group_id` (`field_group_id`,`name`),
  KEY `type` (`type`),
  KEY `field_translation` (`translation`),
  KEY `repeatable` (`repeatable`),
  KEY `ordering` (`ordering`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Extra fields' AUTO_INCREMENT=2 ;
ALTER TABLE  `field` ADD  `settings` TEXT NULL AFTER  `type`;
ALTER TABLE  `field` ADD  `help_text` TEXT NULL AFTER  `settings`;

--
-- Dumping data for table `field`
--

INSERT INTO `field` (`id`, `field_group_id`, `name`, `translation`, `repeatable`, `type`, `ordering`) VALUES
(1, 1, 'content', 'Content', 0, 'Wysiwyg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `field_group`
--

CREATE TABLE IF NOT EXISTS `field_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `translation` varchar(64) NOT NULL,
  `repeatable` tinyint(1) NOT NULL DEFAULT '0',
  `global` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `field_group` (`name`),
  KEY `group_translation` (`translation`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Field groups' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `field_group`
--

INSERT INTO `field_group` (`id`, `name`, `translation`, `repeatable`, `global`) VALUES
(1, 'uncategorized', 'Uncategorized', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `plugin`
--

CREATE TABLE IF NOT EXISTS `plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin` varchar(32) NOT NULL COMMENT 'Plugin name',
  `system_plugin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'System plugins cannot be directly edited or deleted.',
  `blocked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Blocked disables the plugin',
  `ordering` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `plugin` (`plugin`),
  KEY `system_plugin` (`system_plugin`,`blocked`,`ordering`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Plugins' AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Role ID',
  `role` varchar(64) NOT NULL COMMENT 'Role (group) name',
  `system_role` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'System role 0 or 1. System roles cannot be directly edited or deleted',
  `parent_of` int(11) DEFAULT NULL COMMENT 'ID of parent role',
  `meta` text COMMENT 'JSON metadata',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Roles (groups)' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `role`, `system_role`, `parent_of`, `meta`) VALUES
(1, 'Administrator', 1, NULL, NULL),
(2, 'Registered', 1, NULL, NULL),
(3, 'Public', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `structure`
--

CREATE TABLE IF NOT EXISTS `structure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `type` enum('content_listing','content','component','alias','url') NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `target_url` varchar(512) DEFAULT NULL,
  `translation` varchar(512) NOT NULL,
  `locale` varchar(6) NOT NULL DEFAULT '*',
  `ordering` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`parent_id`,`type`,`target_id`,`locale`),
  KEY `translation` (`translation`),
  KEY `ordering` (`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Site structure' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `structure_tag`
--

CREATE TABLE IF NOT EXISTS `structure_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `structure_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`structure_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Links tags to content' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `tag` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tag` (`tag`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Tags' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `tag`
--

INSERT INTO `tag` (`id`, `parent_id`, `tag`) VALUES
(1, NULL, 'Starred');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'User ID',
  `login` varchar(48) NOT NULL,
  `email` varchar(48) NOT NULL COMMENT 'Email / Username',
  `password` varchar(128) DEFAULT NULL,
  `firstname` varchar(32) DEFAULT NULL COMMENT 'Firstname',
  `lastname` varchar(32) DEFAULT NULL COMMENT 'Lastname',
  `blocked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Block account',
  `locale` varchar(6) NOT NULL DEFAULT '*',
  `meta` text COMMENT 'JSON metadata',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Created',
  `last_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last modified',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `firstname` (`firstname`,`lastname`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Users' AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'User ID',
  `role_id` int(11) NOT NULL COMMENT 'Role ID',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Links roles to users' AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_sso`
--

CREATE TABLE IF NOT EXISTS `user_sso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service` varchar(64) NOT NULL,
  `ext_user_id` varchar(128) NOT NULL,
  `ext_auth_url` varchar(255) DEFAULT NULL,
  `meta` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`service`,`ext_user_id`,`ext_auth_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='External Single Sign on information for users' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `version_history`
--

CREATE TABLE IF NOT EXISTS `version_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_table` varchar(32) NOT NULL COMMENT 'Target table',
  `target_id` int(11) NOT NULL COMMENT 'Target id',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Revision timestamp',
  `content` longtext NOT NULL COMMENT 'Old revision as JSON',
  `user_id` int(11) NOT NULL COMMENT 'Created by user id',
  PRIMARY KEY (`id`),
  KEY `target_table` (`target_table`,`target_id`,`created`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Version history' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/* 1.0.2 schema update */

ALTER TABLE `structure`
ADD `alias` varchar(128) NOT NULL AFTER `id`,
ADD INDEX ( `alias` ) ;

/* 1.0.3 schema update */

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `filename` varchar(255) NOT NULL,
  `alt` varchar(255) NULL,
  `title` varchar(255) NULL,
  `meta` text NULL
) ENGINE='InnoDB' COLLATE 'utf8_general_ci';

CREATE TABLE IF NOT EXISTS `category_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `category_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
) ENGINE='InnoDB' COLLATE 'utf8_general_ci';

ALTER TABLE `category`
ADD `apply_permissions` tinyint(1) NOT NULL DEFAULT '0' AFTER `layout_list`;

