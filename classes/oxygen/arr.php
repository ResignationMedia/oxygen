<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   Oxygen
 */
class Oxygen_Arr extends Kohana_Arr {

	/**
	 * Converts an object to an array.
	 *
	 * @static
	 * @param  mixed  $data
	 * @return array
	 */
	public static function object_to_array($data) {
		if (is_array($data) || is_object($data)) {
			$result = array();
			foreach ($data as $key => $value) {
				$result[$key] = self::object_to_array($value);
			}

			return $result;
		}
		return $data;
	}

} // End Oxygen_Arr
