<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Model_Activity extends Model_Oxygen_Activity {

	/**
	 * @var  int  audit status: Off
	 */
	protected $audit_status = OAudit::OFF;

	/**
	 * @var  int  activity status: Off
	 */
	protected $activity_status = OActivity::OFF;

	/**
	 * @var  string  table name
	 */
	protected $_table_name = 'activity';

	/**
	 * @var  string  created by column
	 */
	protected $_created_by_column = null;

	/**
	 * @var  string  updated by column
	 */
	protected $_updated_by_column = null;

} // End Model_Activity
