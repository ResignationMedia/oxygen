<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Radio extends Oxygen_Form_Field {

	/**
	 * @var  string  type
	 */
	protected $_type = 'radio';

	/**
	 * Sets the field view type and then calls [View::__construct].
	 *
	 * @param  string  $type  type of field
	 * @param  array   $data  array of values
	 */
	public function __construct($type = 'text', array $data = null) {
		$this->view('search', 'form/field/radio/search');
		parent::__construct($type, $data);
	}

	/**
	 * Checks to see if there are more than one option, if so we need to display multiple radios.
	 *
	 * @param  string  $file        shell filename
	 * @param  bool	   $find_shell  set to false to skip finding the shell
	 * @return string
	 */
	public function render($file = null, $find_shell = true) {
		if (count($this->_options)) {
			$this->_type = 'radios';
			if ($this->view('search') == 'form/field/radio/search') {
				$this->view('search', 'form/field/radios/search');
			}
		}

		return parent::render($file, $find_shell);
	}

} // End Oxygen_Form_Field_Radio
