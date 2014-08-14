<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Inflector extends Kohana_Inflector {

	/**
	 * Makes an underscored or dashed phrase human-readable.
	 *
	 *	 $str = Inflector::humanize('kittens-are-cats');	// "kittens are cats"
	 *	 $str = Inflector::humanize('dogs_as_well');		// "dogs as well"
	 *   $str = Inflector::humanize('cows_are_cool', true); // "Cows Are Cool"
	 *
	 * @param  string  $str      phrase to make human-readable
	 * @param  boolean $ucwords  use ucwords()?
	 * @return string
	 */
	public static function humanize($str, $ucwords = false) {
		$str = parent::humanize($str);

		if ($ucwords) {
			$str = ucwords($str);
		}

		return $str;
	}

} // End Oxygen_Inflector
