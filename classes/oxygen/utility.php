<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Utility {

	/**
	 * Cleans up the passed in string for JSON objects.
	 *
	 * @static
	 * @param  string|array  $str     string to alter
	 * @param  bool          $breaks  convert \n to <br />?
	 * @return string
	 */
	public static function html($str, $breaks = false) {
		if (is_array($str)) {
			extract($str);
		}

		$str = Utility::htmlspecialchars_or($str);

		// Convert \n to <br />?
		if ($breaks) {
			$str = nl2br($str);
		}

		return $str;
	}

	/**
	 * Recursively apply htmlspecialchars to arrays or strings.
	 *
	 * @static
	 * @param  string|array  $str
	 * @param  int           $quote_style
	 * @return array|string
	 */
	public static function htmlspecialchars_or($str, $quote_style = ENT_COMPAT) {
		if (is_array($str)) {
			return array_map('Utility::htmlspecialchars_or', $str, array_fill(0, count($str), $quote_style));
		}
		else {
			return htmlspecialchars(htmlspecialchars_decode($str, $quote_style), $quote_style);
		}
	}

	/**
	 * Sorts an array by it's natural sort order on keys.
	 *
	 * @static
	 * @param  array  $to_be_sorted
	 * @return bool
	 */
	public static function natksort(array &$to_be_sorted) {
		$result = array();
		$keys = array_keys($to_be_sorted);
		natcasesort($keys);

		foreach ($keys as $key) {
			$result[$key] = $to_be_sorted[$key];
		}

		$to_be_sorted = $result;
		return true;
	}

	/**
	 * Converts \r\n and \r to \n.
	 *
	 * @static
	 * @param  string  $str
	 * @return string
	 */
	public static function eol($str) {
		return str_replace(array("\r\n", "\r"), "\n", $str);
	}
	
	/**
	 * Return a unique string.
	 *
	 * @static
	 * @param  int  $len
	 * @return string
	 */
	public static function unique($len = 8) {
		return substr(sha1(microtime()), 0, $len);
	}

} // End Oxygen_Utility
