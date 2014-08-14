<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Shortkey {

	/**
	 * @var  int  count
	 */
	protected $count = 0;

	/**
	 * @var  array  legend
	 */
	protected $legend = array(
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
	);

	/**
	 * Initializes an OShortkey object.
	 *
	 * @static
	 * @return OShortkey
	 */
	public static function factory() {
		return new OShortkey;
	}

	/**
	 * Counts the legend.
	 */
	public function __construct() {
		$this->count = count($this->legend);
	}

	/**
	 * Generates a number from a key.
	 *
	 * @param  string  $key  key
	 * @return int
	 */
	public function number($key) {

		$length = strlen($key);
		$number = 0;
		for ($i = 0; $i < $length; ++$i) {
			$digit = substr($key, $i, 1);
			$value = $this->_digit_val($digit);
			if ($i+1 != $length) {
				++$value;
				$number += $value * pow($this->count, ($length-$i-1));
			}
			else {
				$number += $value;
			}
		}

		return intval($number);
	}

	/**
	 * Generates a key from a number.
	 *
	 * @param  int  $number  number
	 * @return string
	 */
	public function key($number) {

		$number = intval($number);
		if (isset($this->legend[$number])) {
			return $this->legend[$number];
		}

		$div = floor($number / $this->count)-1;
		$remainder = $number % $this->count;
		return $this->key($div).$this->key($remainder);
	}

	/**
	 * Gets the index of the digit in the legend.
	 *
	 * @param  string  $digit  digit
	 * @return int
	 */
	private function _digit_val($digit) {

		$digit = strtolower($digit);
		for ($i = 0; $i < $this->count; ++$i) {
			if ($this->legend[$i] === $digit) {
				return $i;
			}
		}
	}

} // End Oxygen_Shortkey
