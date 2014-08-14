<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Hidden extends Oxygen_Form_Field {

	/**
	 * @var  string  type
	 */
	protected $_type = 'hidden';

	/**
	 * Sets the field view variables, then calls [Oxygen_HTML_Element::render].
	 *
	 * @param  string  $file        shell view file name
	 * @param  bool	   $find_shell  set to false to skip finding the shell
	 * @return string
	 */
	public function render($file = null, $find_shell = true) {
		if ($this->display() == 'view') {
			$find_shell = false;
			$this->label($this->name());
		}
		return parent::render($file, $find_shell);
	}

} // End Oxygen_Form_Field_Hidden
