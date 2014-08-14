<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Text extends Kohana_Text {

	/**
	 * Remove all characters besides alpha, numeric characters, and any extra regex args passed in.
	 *
	 * @static
	 * @param  string  $str
	 * @param  string  $extra
	 * @return string
	 */
	public static function alphanum($str, $extra = '') {
		$regex = '/[^a-z0-9'.$extra.']/i';
		return preg_replace($regex, '', $str);
	}

	/**
	 * Removes all but 0-9 from a string.
	 *
	 * @static
	 * @param  string  $str
	 * @return mixed
	 */
	public static function numeric($str) {
		$regex = '/[^0-9]/';
		return preg_replace($regex, '', $str);
	}

	/**
	 * Removes all characters besides alpha, and any extra regex args passed in.
	 *
	 * @static
	 * @param  string  $str
	 * @param  string  $extra
	 * @return mixed
	 */
	public static function alpha($str, $extra = '') {
		$regex = '/[^a-z'.$extra.']/i';
		return preg_replace($regex, '', $str);
	}

	/**
	 * Turns a boolean to human-readable format.
	 *
	 * @static
	 * @param  string  $str  1|0
	 * @return string
	 */
	public static function bool_display($str) {
		return (int)$str ? 'Yes' : 'No';
	}

	/**
	 * @static
	 * @param  string  $str
	 * @return int
	 */
	public static function reverse_bool_display($str) {
		if (strtolower($str) == 'yes') {
			return '1';
		}
		else if (strtolower($str) == 'no') {
			return '0';
		}

		return $str;
	}

} // End Oxygen_Text
