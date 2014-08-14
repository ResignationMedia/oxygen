<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package    Oxygen
 * @subpackage Upgrades
 *
 * Upgrades Oxygen to Version 1.0.4.
 */

// Grab the database instance.
$db = Database::instance();

/**
 * Change the column type to MEDIUMTEXT.
 */
$columns = $db->list_columns('audit');
$table_name = $db->quote_table('audit');
if (isset($columns['id']) && $columns['id']['data_type'] == 'int') {
	$sql = "ALTER TABLE :table_name CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT";
	DB::query(Database::UPDATE, $sql)
		->param(':table_name', DB::expr($table_name))
		->execute($db);
}
