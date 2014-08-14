<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Checkbox extends Oxygen_Form_Field {

	/**
	 * @var  string  type
	 */
	protected $_type = 'checkbox';

	/**
	 * @var  bool  show select all?
	 */
	protected $_show_select_all = true;

	/**
	 * Show the select all links?
	 *
	 * @param  bool  $show  show select all?
	 * @return OField
	 */
	public function select_all($show = true) {
		$this->_show_select_all = $show;
		return $this;
	}

	/**
	 * Sets the field view type and then calls [View::__construct].
	 *
	 * @param  string  $type  type of field
	 * @param  array   $data  array of values
	 */
	public function __construct($type = 'text', array $data = null) {
		$this->view('search', 'form/field/checkbox/search');
		$this->view('edit', 'form/field/checkbox/edit');
		parent::__construct($type, $data);
	}

	/**
	 * Checks to see if there are options, if so change the shell to "checkboxes".
	 *
	 * @param  string  $file        view file name
	 * @param  bool	   $find_shell  set to true to false to skip finding the shell
	 * @return string
	 */
	public function render($file = null, $find_shell = true) {
		$select_all = '';
		if (count($this->_options)) {
			if ($this->_show_select_all && $this->display() != 'view') {
				foreach ($this->_options as $value => $label) {
					$select_all[] = $this->name().':'.$value;
				}

				if (is_array($select_all)) {
					$select_all = implode(',', $select_all);
				}
			}

			if ($this->view('label') == 'form/field/label') {
				$this->view('label', 'form/field/checkboxes/label');
			}

			if ($this->view('shell') == 'form/field/shell') {
				if ($this->_display == 'search') {
					$this->view('shell', 'form/field/checkboxes/search/shell');
				}
				else {
					$this->view('shell', 'form/field/checkboxes/shell');
				}
			}

			if ($this->view('edit') == 'form/field/checkbox/edit') {
				$this->view('edit', 'form/field/checkboxes/edit');
			}

			if ($this->view('search') == 'form/field/checkbox/search') {
				$this->view('search', 'form/field/checkboxes/search');
			}
		}
		$this->set(array(
			'select_all' => $select_all
		));

		return parent::render($file, false);
	}

	/**
	 * Overrides the default view path with the specific field type's view, if it exists.
	 *
	 * @param  string  $view  view key
	 * @param  string  $type  field type
	 */
	protected function set_view($view, $type = null) {
		$type = $this->_type;
		if (count($this->_options)) {
			$type = 'checkboxes';
		}

		parent::set_view($view, $type);
	}

	/**
	 * Sets the has-checkboxes class.
	 */
	protected function set_shell_attributes() {
		parent::set_shell_attributes();

		if (count($this->_options) > 1) {
			$this->remove_css_class('has-checkbox', true);
			$this->add_css_class('has-checkboxes', true);
		}
	}

	/**
	 * Sets the has-checkboxes class.
	 */
	protected function set_attributes() {
		parent::set_attributes();

		if (count($this->_options) > 1) {
			$this->remove_css_class('has-checkbox');
			$this->add_css_class('has-checkboxes');
		}
	}

} // End Oxygen_Form_Field_Checkbox
