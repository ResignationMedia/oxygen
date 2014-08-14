<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Model_Oxygen_Permission extends OModel {

	/**
	 * @var  bool  log global search
	 */
	protected $_include_in_global_search = false;

	/**
	 * @var  bool  log global item
	 */
	protected $_create_global_item = false;

	/**
	 * @var  bool  disable audits
	 */
	protected $_audit_status = OAudit::OFF;

	/**
	 * @var  int  disable activity
	 */
	protected $_activity_status = OActivity::OFF;

	/**
	 * @var array belongs to user
	 */
	protected $_belongs_to = array('user' => array());

	/**
	 * @var  string  created by column
	 */
	protected $_created_by_column = null;

	/**
	 * @var  array  created column
	 */
	protected $_created_column = null;

	/**
	 * @var  string  updated by column
	 */
	protected $_updated_by_column = null;

	/**
	 * @var  array  updated column
	 */
	protected $_updated_column = null;

	/**
	 * @var string obsolete column
	 */
	protected $_obsolete_column = null;

} // End Model_Oxygen_Permission
