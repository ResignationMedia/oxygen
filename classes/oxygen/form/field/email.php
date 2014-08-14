<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Email extends Oxygen_Form_Field_Text {

	/**
	 * @var  bool  make the email a mailto link?
	 */
	protected $_link = false;

	/**
	 * Make the value a mailto link?
	 *
	 * @param  bool  $link  true|false
	 * @return bool|OField
	 */
	public function link($link = null) {
		if ($link === null) {
			return $this->_link;
		}
		$this->_link = $link;
		return $this;
	}

	/**
	 * Checks to see if the value should be a mailto link.
	 *
	 * @param  string  $value
	 * @return OField|string
	 */
	public function value($value = null) {
		if ($value === null && $this->_link === true) {
			return HTML::mailto(parent::value($value), parent::value($value));
		}
		return parent::value($value);
	}

} // End Oxygen_Form_Field_Email
