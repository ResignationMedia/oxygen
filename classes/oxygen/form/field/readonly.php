<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Readonly extends Oxygen_Form_Field {

	/**
	 * @var  string  type
	 */
	protected $_type = 'input';

	/**
	 * @var  array  default attributes
	 */
	protected $_attributes = array(
		'type' => 'readonly',
		'readonly' => 'readonly'
	);

	/**
	 * Sets/gets the type of field.
	 *
	 * @param  string  $type  the type of the field
	 * @return mixed
	 */
	public function type($type = null) {
		if ($type == null) {
			return $this->_type;
		}

		return $this;
	}

} // End Oxygen_Form_Field_ReadOnly
