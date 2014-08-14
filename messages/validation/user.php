<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
return array(
	'name' => array(
		'not_empty' => 'Please enter a name.',
	),
	'email' => array(
		'not_empty' => 'Please enter a valid email address.',
		'email' => 'Please enter a valid email address.',
		'unique_value' => 'Email address is in use, please try another.'
	),
	'username' => array(
		'not_empty' => 'Please enter a username.',
		'unique_value' => 'Username is in use, please try another.',
		'valid_login' => 'Invalid username and/or password.',
	),
	'password' => array(
		'not_empty' => 'Please enter a password.'
	),
);
