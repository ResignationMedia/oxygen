<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Model_Oxygen_Activity extends ORM {

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
	 * @var  array  updated column
	 */
	protected $_updated_column = null;

	/**
	 * @var  string  updated by column
	 */
	protected $_updated_by_column = null;

	/**
	 * @var  array  created column
	 */
	protected $_created_column = array(
		'column' => 'created',
		'format' => 'Y-m-d H:i:s'
	);

	/**
	 * Loads the GUIDs for the global stream.
	 *
	 * @param  int    $limit
	 * @param  string  $object
	 * @return array
	 */
	public function items($limit = 10, $object = null) {
		if ($object !== null) {
			$this->where('object', '=', $object);
		}
		$activities = $this->group_by('audit_id')
			->order_by('id', 'DESC')
			->limit($limit)
			->find_all();
		return $activities;
	}

} // End Model_Oxygen_Activity
