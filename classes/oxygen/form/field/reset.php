<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Reset extends Oxygen_Form_Field_Button {

	/**
	 * @var  string  type
	 */
	protected $_type = 'button';

	/**
	 * @var  array  default attributes
	 */
	protected $_attributes = array(
		'type' => 'reset'
	);

} // End Oxygen_Form_Field_Reset
