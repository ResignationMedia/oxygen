<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package application
 * @subpackage upgrade
 *
 * Upgrades Oxygen to Version 1.0.1.
 */

// Grab the database instance.
$db = Database::instance();

/**
 * Modify project_users to add the role_id and permissions columns.
 */
$columns = $db->list_columns('activity');
$table_name = $db->quote_table('activity');
if (!isset($columns['destination'])) {
	$query = DB::query(Database::UPDATE, "ALTER TABLE :table_name ADD `destination` VARCHAR(255) DEFAULT NULL :after");

	$after = '';
	if (isset($columns['target'])) {
		$after = 'AFTER `target`';
	}

	$query->param(':table_name', DB::expr($table_name))
		->param(':after', DB::expr($after))
		->execute($db);
}

if (!isset($columns['object'])) {
	$query = DB::query(Database::UPDATE, "ALTER TABLE :table_name ADD `object` VARCHAR(255) DEFAULT NULL :after");

	$after = '';
	if (isset($columns['created'])) {
		$after = 'AFTER `created`';
	}

	$query->param(':table_name', DB::expr($table_name))
		->param(':after', DB::expr($after))
		->execute($db);

	// Update the object columns
	$results = DB::query(Database::SELECT, "
		SELECT id, audit_id, created, actor, target, type, verb
		  FROM :table_name
	")
	->param(':table_name', DB::expr($table_name))
	->as_object()
	->execute($db);

	if ($results->count()) {
		foreach ($results as $result) {
			DB::query(Database::UPDATE, "
				UPDATE :table_name
				   SET object = :object
				 WHERE id = :id
			")
			->param(':table_name', DB::expr($table_name))
			->param(':object', $result->actor)
			->param(':id', $result->id)
			->execute($db);

			DB::query(Database::INSERT, "
				INSERT
				  INTO :table_name(audit_id, created, object, actor, target, type, verb)
				VALUES(:audit_id, :created, :object, :actor, :target, :type, :verb)
			")
			->param(':table_name', DB::expr($table_name))
			->param(':audit_id', $result->audit_id)
			->param(':created', $result->created)
			->param(':object', $result->target)
			->param(':actor', $result->actor)
			->param(':target', $result->target)
			->param(':type', $result->type)
			->param(':verb', $result->verb)
			->execute($db);
		}
	}
}
