<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 *
 * These are the role types included with Oxygen. To add a new type, add to the following to this array:
 *
 *     'type_key' => 'Type Display'
 *
 * Then in your models, to add permissions to this new type add the following to permissions():
 *
 *     'type_key' => array(
 *         'add',
 *         'edit',
 *         'delete',
 *     )
 */
return array(
	'system' => 'System',
);
