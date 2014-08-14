<?php defined('SYSPATH') || die('No direct access allowed.');

return array(
	"LOCK TABLES `{TABLE_PREFIX}roles` WRITE;",

	"INSERT IGNORE
	INTO `{TABLE_PREFIX}roles`
	(`id`,`name`,`permissions`,`created`,`updated`,`created_by`,`updated_by`)
	VALUES
	(1,'Super Admin','',NOW(),NOW(),1,1);",

	"UNLOCK TABLES;",
);
