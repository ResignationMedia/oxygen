<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 *
 * This class exists to set the configuration
 */
class Oxygen_Cookie extends Kohana_Cookie {

	/**
	 * @var  bool  configuration set?
	 */
	private static $config_set = false;

	/**
	 * Gets the value of a signed cookie. Cookies without signatures will not
	 * be returned. If the cookie signature is present, but invalid, the cookie
	 * will be deleted.
	 *
	 *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
	 *     $theme = Cookie::get('theme', 'blue');
	 *
	 * @param   string  $key      cookie name
	 * @param   mixed   $default  default value to return
	 * @return  string
	 */
	public static function get($key, $default = null) {
		if (!self::$config_set) {
			self::config();
		}

		return parent::get($key, $default);
	}

	/**
	 * Sets a signed cookie. Note that all cookie values must be strings and no
	 * automatic serialization will be performed!
	 *
	 *     // Set the "theme" cookie
	 *     Cookie::set('theme', 'red');
	 *
	 * @param   string   $name        name of cookie
	 * @param   string   $value       value of cookie
	 * @param   integer  $expiration  lifetime in seconds
	 * @return  boolean
	 * @uses    Cookie::salt
	 */
	public static function set($name, $value, $expiration = null) {
		if (!self::$config_set) {
			self::config();
		}

		return parent::set($name, $value, $expiration);
	}

	/**
	 * Deletes a cookie by making the value null and expiring it.
	 *
	 *     Cookie::delete('theme');
	 *
	 * @param   string   $name  cookie name
	 * @return  boolean
	 * @uses    Cookie::set
	 */
	public static function delete($name) {
		if (!self::$config_set) {
			self::config();
		}

		return parent::delete($name);
	}

	/**
	 * Generates a salt string for a cookie based on the name and value.
	 *
	 *     $salt = Cookie::salt('theme', 'red');
	 *
	 * @param  string  $name   name of cookie
	 * @param  string  $value  value of cookie
	 * @return string
	 */
	public static function salt($name, $value) {
		if (!self::$config_set) {
			self::config();
		}

		return parent::salt($name, $value);
	}

	/**
	 * Loads the configuration for Cookie
	 *
	 * @static
	 */
	private static function config() {
		$vars = get_class_vars(__CLASS__);
		foreach ($vars as $var => $value) {
			$value = Oxygen::config('oxygen')->get('cookie_'.$var);
			if ($value !== null) {
				Cookie::$$var = $value;
			}
		}

		self::$config_set = true;
	}

} // End Oxygen_Cookie
