<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
return array(
	/**
	 * Routes that will skip authentication.
	 */
	'public_routes' => array(
		'user/login',
		'user/forgot_password'
	),

	/**
	 * Default date and time formats
	 */
	'date_format' => 'M j, Y',
	'time_format' => 'g:ia',

);
