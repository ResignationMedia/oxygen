<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Select extends Oxygen_Form_Field {

	/**
	 * @var  string  type
	 */
	protected $_type = 'select';

	/**
	 * Sets the field view type and then calls [View::__construct].
	 *
	 * @param  string  $type  type of field
	 * @param  array   $data  array of values
	 */
	public function __construct($type = 'select', array $data = null) {
		$this->view('search', 'form/field/select/search');
		parent::__construct($type, $data);
	}

} // End Oxygen_Form_Field_Select
