<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
return array(
	'domain' => array(
		'not_empty' => 'Please enter a domain.'
	),
	'hostname' => array(
		'not_empty' => 'Please enter the database host.'
	),
	'username' => array(
		'not_empty' => 'Please enter the database username.'
	),
	'password' => array(
		'not_empty' => 'Please enter the database password.'
	),
	'database' => array(
		'not_empty' => 'Please enter the database name.',
		'verify_db' => 'Unable to connect to the database using the provided settings.'
	),
);
