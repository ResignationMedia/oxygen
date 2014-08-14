<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
return array(
	'name' => array(
		'not_empty' => 'Please enter a name for this role.',
		'unique_value' => 'A role already exists with this name. :param1'
	),
	'permissions' => array(
		'not_empty' => 'Please check at least one checkbox.'
	)
);
