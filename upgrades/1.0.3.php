<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package application
 * @subpackage upgrade
 *
 * Upgrades Oxygen to Version 1.0.3.
 */

// Grab the database instance.
$db = Database::instance();

/**
 * Change the column type to MEDIUMTEXT.
 */
$columns = $db->list_columns('preferences');
$table_name = $db->quote_table('preferences');
if (isset($columns['value']) && $columns['value']['data_type'] == 'text') {
	$sql = "ALTER TABLE :table_name CHANGE `value` `value` MEDIUMTEXT  NOT NULL";
	DB::query(Database::UPDATE, $sql)
		->param(':table_name', DB::expr($table_name))
		->execute($db);
}
