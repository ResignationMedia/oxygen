<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Flag extends Oxygen_Form_Field {

	/**
	 * @var  string  type
	 */
	protected $_type = 'flag';

	/**
	 * @var  string  data type
	 */
	protected $_data_type = 'bool';

	/**
	 * @var  string  default|select
	 */
	protected $_display_type = 'default';

	/**
	 * Sets the field view type and then calls [View::__construct].
	 *
	 * @param  string  $type  type of field
	 * @param  array   $data  array of values
	 */
	public function __construct($type = 'text', array $data = null) {
		parent::__construct($type, $data);
	}

	/**
	 * Checks to see if we're in a view.
	 *
	 * @param  string  $file        view file name
	 * @param  bool	   $find_shell  set to false to skip finding the shell
	 * @return string
	 */
	public function render($file = null, $find_shell = true) {
		if ($this->_display == 'view') {
			$find_shell = false;

			if ($this->view('shell') == 'form/field/shell') {
				$this->view('shell', 'form/field/flag/shell/view');
			}
		}
		else if ($this->_display == 'search') {
			$this->options(Arr::merge($this->options(), array('both' => 'Both')));
			$find_shell = false;

			if ($this->view('shell') == 'form/field/shell') {
				$this->view('shell', 'form/field/flag/search/shell');
			}

			if ($this->view('search') == 'form/field/search') {
				$this->view('search', 'form/field/flag/search/'.$this->_display_type);
			}
		}

		return parent::render($file, $find_shell);
	}

	/**
	 * Sets the has-select class.
	 */
	protected function set_shell_attributes() {
		parent::set_shell_attributes();

		if ($this->_display_type == 'select') {
			$this->remove_css_class('has-flag', true);
			$this->add_css_class('has-select', true);
		}
	}

	/**
	 * Sets the has-select class.
	 */
	protected function set_attributes() {
		parent::set_attributes();

		if ($this->_display_type == 'select') {
			$this->remove_css_class('elm-flag');
			$this->add_css_class('elm-select');
		}
	}

} // End Oxygen_Form_Field_Flag
