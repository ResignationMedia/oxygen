<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Model_Oxygen_Global_Item extends ORM {

	/**
	 * @var  array  has one relationships
	 */
	protected $_has_one = array(
		'search' => array(
			'model' => 'global_search',
			'foreign_key' => 'guid'
		)
	);

	/**
	 * @var  string  primary key value
	 */
	protected $_primary_key = 'id';

	/**
	 * @var  array  created column
	 */
	protected $_created_column = null;

	/**
	 * @var  array  updated column
	 */
	protected $_updated_column = null;

	/**
	 * Handles retrieval of all model values, relationships, and metadata.
	 *
	 * @param   string $column Column name
	 * @return  mixed
	 */
	public function __get($column) {
		if ($column == 'search') {
			if (isset($this->_related[$column])) {
				return $this->_related[$column];
			}

			$model = $this->_related($column);

			// Use this model's primary key value and foreign model's column
			$col = $model->_object_name.'.'.$this->_has_one[$column]['foreign_key'];
			$val = $this->guid;

			$model->where($col, '=', $val)->find();
		}

		return parent::__get($column);
	}

	/**
	 * Saves the global search item.
	 *
	 * @chainable
	 * @param  Validation  $validation  Validation object
	 * @return ORM
	 */
	public function save(Validation $validation = NULL) {
		// TODO: Remove Request reference
		if (Request::current()->action() == 'login' || Request::current()->action() == 'protected') {
			// No need to record a login...
			return;
		}

		return parent::save();
	}

} // End Model_Oxygen_Global_Item
