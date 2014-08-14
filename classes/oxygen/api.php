<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_API {

	/**
	 * @var API instance
	 */
	public static $instance;

	/**
	 * Creates an instance of API
	 *
	 * @return API
	 */
	public static function instance() {

		if (self::$instance === null) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Generates an API key.
	 *
	 * @return string
	 */
	public function generate_key() {
		return sha1(mt_rand().time().mt_rand());
	}

} // End Oxygen_API
