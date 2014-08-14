<?php defined('SYSPATH') || die('No direct access allowed.');
return array(
	"CREATE TABLE `{TABLE_PREFIX}activity` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `audit_id` bigint(11) unsigned NOT NULL,
	  `created` datetime DEFAULT NULL,
	  `actor` varchar(255) NOT NULL,
	  `target` varchar(255) NOT NULL,
	  `type` varchar(255) NOT NULL,
	  `verb` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `created_target` (`created`,`target`),
	  KEY `actor` (`actor`),
	  KEY `target` (`target`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}audit` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `item` int(11) NOT NULL,
	  `user_id` int(11) NOT NULL,
	  `created` datetime DEFAULT NULL,
	  `guid` varchar(255) NOT NULL,
	  `table` varchar(255) NOT NULL,
	  `data` longtext,
	  `description` text,
	  PRIMARY KEY (`id`),
	  KEY `link` (`table`,`item`),
	  KEY `guid` (`guid`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}global_items` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `guid` varchar(255) NOT NULL,
	  `title` varchar(255) NOT NULL,
	  `edit_url` varchar(255) NOT NULL,
	  `view_url` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `guid` (`guid`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}global_searches` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `guid` varchar(255) NOT NULL,
	  `created` datetime DEFAULT NULL,
	  `updated` datetime DEFAULT NULL,
	  `type` varchar(255) DEFAULT NULL,
	  `title_sort` varchar(255) NOT NULL,
	  `summary` varchar(255) DEFAULT NULL,
	  `content` text NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `guid` (`guid`),
	  FULLTEXT KEY `content` (`content`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}preferences` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `user_id` int(11) unsigned NOT NULL,
	  `key` varchar(255) NOT NULL,
	  `value` text NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `reference_by_user_id` (`user_id`,`key`),
	  KEY `reference_by_key` (`key`,`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}roles` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `obsolete` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `created` datetime DEFAULT NULL,
	  `created_by` int(11) unsigned DEFAULT '0',
	  `updated` datetime DEFAULT NULL,
	  `updated_by` int(11) unsigned DEFAULT '0',
	  `type` varchar(255) NOT NULL DEFAULT 'system',
	  `name` varchar(255) NOT NULL,
	  `permissions` text NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}sessions` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `session_id` varchar(127) NOT NULL,
	  `last_active` int(10) unsigned NOT NULL,
	  `contents` text NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `session_id` (`session_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}settings` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `key` varchar(255) NOT NULL,
	  `value` text DEFAULT NULL,
	  `updated` datetime DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `key` (`key`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}searches` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `views` int(11) NOT NULL,
	  `key` varchar(32) NOT NULL,
	  `model` varchar(255) NOT NULL,
	  `params` text NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `key` (`key`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}users` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `role_id` int(11) unsigned NOT NULL,
	  `account_id` int(11) unsigned DEFAULT '0',
	  `created_by` int(11) unsigned DEFAULT '0',
	  `updated_by` int(11) unsigned DEFAULT '0',
	  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `obsolete` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `password_change` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `created` datetime DEFAULT NULL,
	  `updated` datetime DEFAULT NULL,
	  `last_login` datetime DEFAULT NULL,
	  `username` varchar(100) NOT NULL,
	  `password` varchar(255) NOT NULL,
	  `password_key` varchar(255) DEFAULT NULL,
	  `email` varchar(255) NOT NULL,
	  `name` varchar(255) DEFAULT NULL,
	  `company` varchar(255) DEFAULT NULL,
	  `photo_type` varchar(255) DEFAULT 'gravatar',
	  `website` varchar(255) DEFAULT NULL,
	  `api_key` varchar(255) NOT NULL,
	  `profile` text,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `username` (`username`),
	  UNIQUE KEY `email` (`email`),
	  KEY `account_id` (`account_id`),
	  KEY `role_id` (`role_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}user_tokens` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `user_id` int(11) unsigned NOT NULL,
	  `expires` int(10) unsigned NOT NULL,
	  `created` datetime DEFAULT NULL,
	  `token` varchar(255) NOT NULL,
	  `user_agent` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `token` (`token`),
	  KEY `user_id` (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	"CREATE TABLE `{TABLE_PREFIX}permissions` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `user_id` int(11) unsigned NOT NULL,
	  `group` varchar(255) NOT NULL DEFAULT '',
	  `key` varchar(255) NOT NULL,
	  `type` varchar(255) NOT NULL DEFAULT '',
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`)
	) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8;",
);
