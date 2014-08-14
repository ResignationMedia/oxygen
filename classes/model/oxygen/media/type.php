<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Model_Oxygen_Media_Type extends ORM {

	/**
	 * @var  bool  disable audits
	 */
	protected $_audit_status = OAudit::OFF;

	/**
	 * @var  string  created by column
	 */
	protected $_created_by_column = null;

	/**
	 * @var  string  updated by column
	 */
	protected $_updated_by_column = null;

	/**
	 * @var  array  updated column
	 */
	protected $_updated_column = null;

} // End Model_Oxygen_Media_Type
