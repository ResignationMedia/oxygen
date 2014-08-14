<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package    Oxygen
 * @subpackage Upgrades
 *
 * Upgrades Oxygen to Version 1.1.
 */

// Grab the database instance.
$db = Database::instance();

/**
 * Add auto-inc ids.
 */
 
$tables = array(
	'settings' => 'key',
	'sessions' => 'session_id'
);

foreach ($tables as $table => $key) {
	$columns = $db->list_columns($table);
	$table_name = $db->quote_table($table);
	$key_name = $db->quote_column($key);
	if (!isset($columns['id'])) {
		$sql = "
			ALTER TABLE :table_name
			DROP PRIMARY KEY
		";
		DB::query(Database::UPDATE, $sql)
			->param(':table_name', DB::expr($table_name))
			->execute($db);

		$sql = "
			ALTER TABLE :table_name
			ADD `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
		";
		DB::query(Database::UPDATE, $sql)
			->param(':table_name', DB::expr($table_name))
			->execute($db);

		$sql = "
			ALTER TABLE :table_name
			ADD INDEX(:key);
		";
		DB::query(Database::UPDATE, $sql)
			->param(':table_name', DB::expr($table_name))
			->param(':key', DB::expr($key_name))
			->execute($db);
	}
}
