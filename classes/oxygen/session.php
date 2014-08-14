<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Oxygen_Session extends Kohana_Session {

	/**
	 * @var  string  default session adapter
	 */
	public static $default = 'database';

} // End Oxygen_Session
