<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Phone extends Oxygen_Form_Field_Text {

	public function render($file = null, $find_shell = true) {
		if ($this->display() == 'view') {
			$this->value(Format::phone($this->value(), '(3) 3-4'));
		}
		return parent::render($file, $find_shell);
	}

} // End Oxygen_Form_Field_Phone
