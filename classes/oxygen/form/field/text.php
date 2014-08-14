<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Text extends Oxygen_Form_Field {

	/**
	 * @var  string  field type
	 */
	protected $_type = 'input';

	/**
	 * Sets/gets the type of field.
	 *
	 * @param  string  $type  the type of the field
	 * @return OField|Oxygen_Form_Field_Text
	 */
	public function type($type = null) {
		if ($type == null) {
			return $this->_type;
		}

		$this->_type = $type;
		return $this;
	}

	/**
	 * Overrides the default view path with the specific field type's view, if it exists.
	 *
	 * @param  string  $view  view key
	 * @param  string  $type  field type
	 */
	protected function set_view($view, $type = 'text') {
		if ($type == 'input') {
			$type = 'text';
		}
		parent::set_view($view, $type);
	}

} // End Oxygen_Form_Field_Text
