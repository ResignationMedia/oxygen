<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package application
 * @subpackage upgrade
 *
 * Upgrades Oxygen to Version 1.0.2.
 */

// Grab the database instance.
$db = Database::instance();

/**
 * Modify the permissions table.
 */
$columns = $db->list_columns('permissions');
$table_name = $db->quote_table('permissions');
if (!isset($columns['type'])) {
	$query = "ALTER TABLE :table_name ADD `type` VARCHAR(255) NOT NULL :after";
	$after = '';
	if (isset($columns['key'])) {
		$after = 'AFTER `key`';
	}
	DB::query(Database::UPDATE, $query)
		->param(':table_name', DB::expr($table_name))
		->param(':after', DB::expr($after))
		->execute($db);

	$query = "UPDATE :table_name SET `type` = 'system'";
	DB::query(Database::UPDATE, $query)
		->param(':table_name', DB::expr($table_name))
		->execute($db);
}

/**
 * Modify the roles table.
 */
$columns = $db->list_columns('roles');
$table_name = $db->quote_table('roles');
if (!isset($columns['type'])) {
	$query = "ALTER TABLE :table_name ADD `type` VARCHAR(255) NOT NULL :after";
	$after = '';
	if (isset($columns['updated_by'])) {
		$after = 'AFTER `updated_by`';
	}
	DB::query(Database::UPDATE, $query)
		->param(':table_name', DB::expr($table_name))
		->param(':after', DB::expr($after))
		->execute($db);

	$query = "UPDATE :table_name SET `type` = 'system'";
	DB::query(Database::UPDATE, $query)
		->param(':table_name', DB::expr($table_name))
		->execute($db);
}

/**
 * Update activity types from person to user.
 */
$columns = $db->list_columns('activity');
$table_name = $db->quote_table('activity');
if (isset($columns['type'])) {
	$query = "UPDATE :table_name SET `type` = 'user' WHERE `type` = 'person';";
	DB::query(Database::UPDATE, $query)
		->param(':table_name', DB::expr($table_name))
		->execute($db);
}
