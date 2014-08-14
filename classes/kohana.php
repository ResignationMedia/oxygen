<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Kohana extends Kohana_Core {

	/**
	 * @var  array   Include paths that are used to find files
	 */
	protected static $_paths = array(APPPATH, OXYPATH, SYSPATH);

} // End Kohana
