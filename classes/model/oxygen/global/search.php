<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Model_Oxygen_Global_Search extends OModel {

	/**
	 * @var  bool  log global search
	 */
	protected $_include_in_global_search = false;

	/**
	 * @var  bool  log global item
	 */
	protected $_create_global_item = false;

	/**
	 * @var  int  store audits?
	 */
	protected $_audit_status = OAudit::OFF;

	/**
	 * @var  int  log activity?
	 */
	protected $_activity_status = OActivity::OFF;

	/**
	 * @var  string  obsolete flag column
	 */
	protected $_obsolete_column = null;

	/**
	 * @var  string  created by column
	 */
	protected $_created_by_column = null;

	/**
	 * @var  string  updated by column
	 */
	protected $_updated_by_column = null;

	/**
	 * @var  array  belongs to relationships
	 */
	protected $_belongs_to = array(
		'global' => array(
			'model' => 'global_item',
			'foreign_key' => 'guid'
		)
	);

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
			$col = $model->_object_name.'.'.$this->_belongs_to[$column]['foreign_key'];
			$val = $this->guid;

			$model->where($col, '=', $val)->find();
		}

		return parent::__get($column);
	}

	/**
	 * Saves the global search items
	 *
	 * @chainable
	 * @param  Validation $validation Validation object
	 * @return ORM
	 */
	public function save(Validation $validation = NULL) {
		if (Request::current()->action() == 'login' || Request::current()->action() == 'protected') {
			// No need to record a login...
			return;
		}

		if (empty($this->content)) {
			$this->content = $this->global->title;
		}

		return parent::save($validation);
	}

	/**
	 * Deletes all items by GUID.
	 *
	 * @param  string  $guid
	 */
	public function delete_all_by_guid($guid) {
		DB::delete($this->table_name())->where('guid', '=', $guid)->execute($this->_db);
	}

} // End Model_Oxygen_Global_Search
