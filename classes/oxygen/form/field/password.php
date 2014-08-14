<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Password extends Oxygen_Form_Field {

	/**
	 * @var  string  type
	 */
	protected $_type = 'password';

	/**
	 * @var  array  default element attributes
	 */
	protected $_attributes = array(
		'autocomplete' => 'off'
	);

} // End Oxygen_Form_Field_Password
