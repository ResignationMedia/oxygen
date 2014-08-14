<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 *
 * @method Oxygen_HTML_Element get()
 * @method Model_Audit record()
 */
abstract class Oxygen_Model extends ORM {

	/**
	 * @var  bool  run init()?
	 */
	protected $_auto_init = true;

	/**
	 * @var  bool  reload model from DB on unserialize
	 */
	protected $_reload_on_wakeup = false;

	/**
	 * @var  bool initialized?
	 */
	protected $_initialized = false;

	/**
	 * @var  array  default model views
	 */
	protected $_views = array(
		'list' => 'models/list',
		'add' => 'models/add',
		'edit' => 'models/edit',
		'view' => 'models/view',
		'delete' => 'models/delete',
		'clone' => 'models/clone',
		'reinstate' => 'models/reinstate',
		'search' => 'models/search',
	);

	/**
	 * @var  array  destinations for successful form posts
	 */
	protected $_destinations = array();

	/**
	 * @var  array  meta
	 */
	protected $_meta = array();

	/**
	 * @var  array  field objects
	 */
	protected $_fields = array();

	/**
	 * @var  array  fieldgroups
	 */
	protected $_fieldgroups = array();

	/**
	 * @var  bool  log global search
	 */
	protected $_include_in_global_search = true;

	/**
	 * @var  bool  log global item
	 */
	protected $_create_global_item = true;

	/**
	 * @var  int  store audits?
	 */
	protected $_audit_status = OAudit::AUTO;

	/**
	 * @var  array  audit data
	 */
	protected $_audit_data = array();

	/**
	 * @var  int  log activity?
	 */
	protected $_activity_status = OActivity::AUTO;

	/**
	 * @var  string  activity type
	 */
	protected $_activity_type = null;

	/**
	 * @var  string  obsolete flag column
	 */
	protected $_obsolete_column = 'obsolete';

	/**
	 * @var  bool  whether or not to include obsoleted records by default
	 */
	protected $_include_obsolete = false;

	/**
	 * @var  string  enabled column
	 */
	protected $_enabled_column = 'enabled';

	/**
	 * @var  string  updated by column
	 */
	protected $_updated_by_column = 'updated_by';

	/**
	 * @var  array  updated column
	 */
	protected $_updated_column = array(
		'column' => 'updated',
		'format' => 'Y-m-d H:i:s'
	);

	/**
	 * @var  string  created by column
	 */
	protected $_created_by_column = 'created_by';

	/**
	 * @var  array  created column
	 */
	protected $_created_column = array(
		'column' => 'created',
		'format' => 'Y-m-d H:i:s'
	);

	/**
	 * @var  string  kv_data column, set to null to disable for model
	 */
	protected $_kv_data_column = null;

	/**
	 * @var  string  CRUD via 'standard' or 'ajax'
	 */
	protected $_crud_request = 'standard';

	/**
	 * @var  bool  object cloneable?
	 */
	protected $_cloneable = false;

	/**
	 * Prepares the model database connection and loads the object.
	 *
	 * @param   mixed  $id  Parameter for find or object to load
	 */
	public function __construct($id = null) {
		parent::__construct($id);

		// Reset ID becuase Kohana::ORM clear()'s all column values on constructor
		// Used as cache key
		if (is_int($id)) {
			$this->id = $id;
		}

		if ($this->_auto_init) {
			$this->init();
		}
	}

	/**
	 * Handles retrieval of all model values, relationships, and metadata.
	 *
	 * @param   string $column Column name
	 * @return  mixed
	 */
	public function __get($column) {
		if ($column == 'global' && isset($this->_has_one['global'])) {
			if (isset($this->_related[$column]) && $this->_related[$column]->loaded()) {
				return $this->_related[$column];
			}

			$model = $this->_related($column);

			// Use this model's primary key value and foreign model's column
			$col = Inflector::singular($model->table_name()).'.'.$this->_has_one[$column]['foreign_key'];
			$val = get_class($this).'_'.$this->pk();

			$model->where($col, '=', $val)->find();

			return $this->_related[$column] = $model;
		}

		return parent::__get($column);
	}

	/**
	 * Initializes the model.
	 */
	public function _initialize() {
		if (!$this->_initialized) {
			parent::_initialize();
			$this->_initialized = true;
		}
	}

	/**
	 * Returns an array of model permissions.
	 *
	 * @return array
	 */
	public function permissions() {
		return array();
	}

	/**
	 * Returns default model nav menu.
	 *
	 * @return array
	 */
	public function nav_menu() {
		return array();
	}

	/**
	 * Initialize the model on load?
	 */
	public function init() {
		if (count($this->_fields)) {
			// Set the field values
			$this->set_field_values();
		}

		return $this;
	}

	/**
	 * Initializes the form fields.
	 */
	public function fields_init() {
		$this->_fields += array(
			'obsolete' => OField::factory('flag')
				->model($this)
				->name('obsolete'),
		);

		return $this;
	}

	/**
	 * Adds the model's meta to the object array.
	 *
	 * @return array
	 */
	public function as_array() {
		$object = parent::as_array();
		$object = Arr::merge(array(
			'meta' => $this->_meta
		), $object);

		return $object;
	}

	/**
	 * Returns the value of the primary key
	 *
	 * @return string
	 */
	public function pk() {
		$pk = parent::pk();

		if ($pk === null) {
			$pk = '0';
		}

		return $pk;
	}

	/**
	 * Returns the guid for the item, or passed ID
	 *
	 * @return string
	 */
	public function guid($id = null) {
		if (is_null($id)) {
			$id = $this->pk();
		}

		$data = OHooks::instance()->filter(
			get_called_class().'.guid',
			array(
				'guid' => get_called_class().'_'.$id,
				'id' => $id,
				'class' => get_called_class(),
			)
		);

		return $data['guid'];
	}

	/**
	 * Sets the obsolete column.
	 *
	 * @param  string  $column
	 * @return OModel|string
	 */
	public function obsolete_column($column = null) {
		if ($column === null) {
			return $this->_obsolete_column;
		}

		$this->_obsolete_column = $column;
		return $this;
	}

	/**
	 * Sets the enabled column.
	 *
	 * @param  string  $column
	 * @return OModel|string
	 */
	public function enabled_column($column = null) {
		if ($column === null) {
			return $this->_enabled_column;
		}

		$this->_enabled_column = $column;
		return $this;
	}

	/**
	 * Sets the created column.
	 *
	 * @param  string  $column
	 * @return OModel|string
	 */
	public function created_column($column = null) {
		if ($column === null) {
			return $this->_created_column['column'];
		}

		$this->_created_column['column'] = $column;
		return $this;
	}

	/**
	 * Sets the updated column.
	 *
	 * @param  string  $column
	 * @return OModel|string
	 */
	public function updated_column($column = null) {
		if ($column === null) {
			return $this->_updated_column['column'];
		}

		$this->_updated_column['column'] = $column;
		return $this;
	}

	/**
	 * Checks to see if the column is a related object.
	 *
	 * @param  string  $column
	 * @return bool|string
	 */
	public function relation($column) {
		$relation = false;
		$relations = Arr::merge($this->has_many(), $this->has_one(), $this->belongs_to());
		foreach ($relations as $key => $data) {
			if ($data['foreign_key'] == $column) {
				$relation = $key;
				break;
			}
		}
		return $relation;
	}

	/**
	 * Loads the items grid.
	 *
	 * @param  string|array  $sort  column to sort by, or an array of column => order
	 * @param  string   $order  asc|desc (defaults to asc if null)
	 * @param  array  $filter_data  data to be applied to filter the results
	 * @param  array  $pagination_config  data to be applied to the pagination constructor
	 * @return array
	 */
	public function grid($sort = null, $order = null, array $filter_data = null, $pagination_config = array()) {
		$object = $this;
		if ($this->loaded()) {
			$object = OModel::factory($this->object_name());
		}

		// Filter Results
		if ($filter_data !== null) {
			// WHERE items
			foreach (Arr::get($filter_data, 'where', array()) as $column => $op) {
				$value = Arr::get($filter_data, $column);
				if (!empty($value)) {

					$value = str_replace('*', '%', $value);
					if ($op == 'LIKE' || $op == 'NOT LIKE') {
						$value = '%'.$value.'%';
					}

					$object->where($column, $op, $value);
				}
			}

			// Created/Updated
			$columns = array(
				'created',
				'updated'
			);
			foreach ($columns as $column) {
				$type = Arr::get($filter_data, $column);
				$start = time();
				$end = time();
				if ($type == 'current') {
					$start = mktime(0, 0, 0, (int)date('m'), 1, (int)date('Y'));
				}
				else if ($type == 'last') {
					$m = (int)date('m')-1;
					$y = (int)date('Y');

					if ($m <= 0) {
						$m = 12;
						--$y;
					}
					$start = mktime(0, 0, 0, $m, 1, $y);
					$end = mktime(0, 0, 0, (int)date('m'), 1, (int)date('Y'));
				}
				else if ($type == 'custom') {
					$_start = Arr::get($filter_data, $column.'_start');
					if ($_start !== null) {
						$_start = explode('/', $_start);
						$start = mktime(0, 0, 0, (int)$_start[1], (int)$_start[2], (int)$_start[0]);
					}

					$_end = Arr::get($filter_data, $column.'_end');
					if ($_end !== null) {
						$_end = explode('/', $_end);
						$end = mktime(0, 0, 0, (int)$_end[1], (int)$_end[2], (int)$_end[0]);
					}
				}

				$object->where_open();
				if ($start > $end) {
					$object->where($column, '<=', $start)
						->or_where($column, '>=', $end);
				}
				else {
					$object->where($column, '>=', $start)
						->or_where($column, '<=', $end);
				}
				$object->where_close();
			}

			// Range
			$range_start = Arr::get($filter_data, 'range_start', 0);
			$range_end = Arr::get($filter_data, 'range_end', 0);
			if ($range_start || $range_end) {
				$object->where_open();
				if ($range_start > $range_end) {
					$object->where('id', '<=', $range_start)
						->or_where('id', '>=', $range_end);
				}
				else {
					$object->where('id', '>=', $range_start)
						->or_where('id', '<=', $range_end);
				}
				$object->where_close();
			}
		}

		// Sort column(s)
		if (is_array($sort)) {
			foreach ($sort as $column => $order) {
				$object->order_by($column, $order);
			}
		}
		else {
			if ($sort !== null && $sort !== '-') {
				if ($order === null) {
					$order = 'asc';
				}

				$object->order_by($sort, $order);
			}
			else {
				$sort_column = $this->sort_column();
				foreach ($sort_column as $column => $order) {
					if (array_key_exists($column, $object->_object)) {
						$object->order_by($column, $order);
					}
				}
			}
		}

		// Pagination
		$total = clone $object;
		$total = $total->count_all();
		$pagination = Pagination::factory(Arr::merge(array(
			'total_items' => $total
		), $pagination_config));

		$items = $object->find_all($pagination);
		if (!is_array($items)) {
			$items = $items->as_array();
		}

		return array(
			'items' => $items,
			'pagination' => $pagination
		);
	}

	/**
	 * Cache field related objects for list display.
	 *
	 * @param  OList  $list
	 */
	public function cache_related_for_list(OList $list) {
		if (!count($this->_fieldgroups)) {
			$this->fields_init();
		}
		$fields = $this->fieldgroup('list');
		$items = $list->items();
		if (count($fields) && count($items)) {
			foreach ($fields as $field) {
				$alias = $field->name();
				if ($field->use_related() && $this->_related($alias)) {
					$ids = array();
					foreach ($items as $item) {
						if (is_object($item)) {
							$ids[] = $item->id;
						}
					}

					if (count($ids)) {
						OModel::factory($this->_object_name)->where($this->_object_name.'.id', 'IN', $ids)->with($alias)->find_all();
					}
				}
			}
		}
	}

	/**
	 * Adds the object name to the order by if it doesn't exist.
	 *
	 * @param  string  $column
	 * @param  string  $direction
	 * @return Kohana_ORM
	 */
	public function order_by($column, $direction = null) {
		if (!strpos($column, $this->_object_name)) {
			$column = $this->_object_name.'.'.$column;
		}
		return parent::order_by($column, $direction);
	}

	/**
	 * Finds a record. Also sets the values/friendly values to the OField objects.
	 *
	 * @return OModel
	 */
	public function find() {
		OHooks::instance()->modify(get_class($this).'.model.find.pre', array($this));

		if ($this->_obsolete_column && !$this->_include_obsolete) {
			$this->_where_not_obsolete();
		}

		$id = $this->id;
		$result = OCache::instance()->get($id, get_class($this));
		if (empty($id) || $result === null) {
			// Result comes back as an object of this same class, not a DB result object
			$result = parent::find();
			OCache::instance()->set($result->id, $result, get_class($this));
		}

		// Set the values
		if (count($this->_fields)) {
			$this->set_field_values();
		}

		OHooks::instance()->modify(get_class($this).'.model.find.post', array($result));

		return $result;
	}

	/**
	 * Finds all of the records for this model, includes pagination.
	 *
	 * @param  Pagination  $pagination
	 * @return OModel
	 */
	public function find_all(Pagination $pagination = null) {
		OHooks::instance()->modify(get_class($this).'.model.find_all.pre', array($this));

		if ($this->_obsolete_column && !$this->_include_obsolete) {
			$this->_where_not_obsolete();
		}

		if (!is_null($pagination)) {
			$this->limit($pagination->items_per_page())
				->offset($pagination->offset());
		}

		foreach ($this->sort_column() as $column => $order) {
			$this->order_by($column, $order);
		}

		$results = parent::find_all();
		if ($results->count()) {
			foreach ($results as $result) {
				OCache::instance()->set($result->id, $result, get_class($this));
			}
		}

		return OHooks::instance()->filter(get_class($this).'.model.find_all.post', $results);
	}

	/**
	 * Counts all of the records.
	 *
	 * @return int
	 */
	public function count_all() {
		if ($this->_obsolete_column && !$this->_include_obsolete) {
			$this->_where_not_obsolete();
		}
		return parent::count_all();
	}

	/**
	 * Loads an array of values into into the current object.
	 *
	 * @chainable
	 * @param  array $values Values to load
	 * @return OModel
	 */
	protected function _load_values(array $values) {
		parent::_load_values($values);

		// Unserialize data
		foreach ($this->_object as $key => $value) {
			if (is_string($value)) {
				if (($v = json_decode($value)) !== null) {
					$value = $v;
				}
				else if (($v = @unserialize($value)) !== false) {
					$value = $v;
				}
			}

			$this->$key = $value;

			// Remove the changed flag...
			unset($this->_changed[$key]);
		}

		OHooks::instance()->modify(get_class($this).'.model._load_values', $this);
		OCache::instance()->set($this->pk(), $this, get_class($this));

		return $this;
	}

	/**
	 * Handles setting of column
	 *
	 * @param  string $column Column name
	 * @param  mixed  $value  Column value
	 * @return OModel
	 */
	public function set($column, $value) {
		if (count($this->_fields)) {
			$field = $this->field($column);
			if ($field !== false) {
				switch ($field->data_type()) {
					case 'int':
					case 'bool':
						$value = intval($value);
					break;
					case 'float':
						$value = floatval($value);
					break;
					case 'date':
						if ($field->timeshift()) {
							$value = Date::utc($value);
						}
					break;
				}

				// Store the new value
				$field->value($value);

				// Related?
				if ($field->use_related()) {
					$value = $this->{$field->name()}->where(
						$this->_object_name.'.'.$this->_primary_key,
						'=',
						$value
					)->find();
				}
			}
		}

		return parent::set($column, $value);
	}

	/**
	 * Serializes any array values before storage.
	 *
	 * @chainable
	 * @param  array  $values    Array of column => val
	 * @param  array  $expected  Array of keys to take from $values
	 * @return OModel
	 */
	public function values(array $values, array $expected = null) {
		if ($expected !== null) {
			foreach ($expected as $key => $column) {
				if (!isset($values[$column])) {
					$values[$column] = null;
				}
			}
		}

		foreach ($values as $key => $value) {
			if ($expected !== null && !empty($expected) && !in_array($key, $expected)) {
				continue;
			}

			if (($related = $this->_related($key)) !== false) {
				$fk = $key.$related->foreign_key_suffix();
				$values[$fk] = $values[$key];
				$expected[] = $fk;

				unset($values[$key]);
				if (($key = array_search($key, $expected)) !== false) {
					unset($expected[$key]);
				}
			}
		}

		return parent::values($values, $expected);
	}

	/**
	 * Sets values from passed data, informed by field data types.
	 *
	 * @param  array   $data   associative array of keys, values
	 * @param  string  $key    key for fieldgroup to use
	 * @return OModel
	 */
	public function values_via_fieldgroup($data, $fieldgroup_key) {
		$fieldgroup = $this->fieldgroup($fieldgroup_key);
		if (!empty($fieldgroup)) {
			foreach ($fieldgroup as $field) {
				if (isset($data[$field->name()])) {
					$val = $data[$field->name()];
					switch ($field->data_type()) {
						case 'int':
						case 'bool':
							$val = intval($val);
							break;
						case 'float':
							$val = floatval($val);
							break;
						case 'string':
							$val = $val;
							break;
					}
				}
				else if ($field->type() == 'flag') {
					$val = 0;
				}
				else {
					$val = null;
				}
				$this->set($field->name(), $val);
			}
		}
		return $this;
	}

	/**
	 * Loops through the current action's fieldgroup and verifies that the option on the $_POST is a valid option. This
	 * will help prevent XSS.
	 *
	 * @param  Validation  $extra_validation  Validation object
	 * @return OModel
	 */
	public function check(Validation $extra_validation = null) {
		// Determine if any external validation failed
		$extra_errors = ($extra_validation && ! $extra_validation->check());
		if ($extra_errors) {
			$exception = new ORM_Validation_Exception($this->_object_name, $extra_validation);
			throw $exception;
		}

		// Add more validation
		// TODO: Don't inspect request from model
		$fields = $this->fieldgroup(Request::current()->action());
		if (!empty($fields)) {
			$extra_validation = Validation::factory($_POST);
			foreach ($fields as $key => $field) {
				if (!isset($_POST[$key])) {
					continue;
				}
				if (in_array($field->type(), array('select', 'checkbox')) && is_array($_POST[$key])) {
// TODO
				}
				else if (in_array($field->type(), array('select', 'checkbox', 'radio'))) {
					$extra_validation->rule(
						$field->name(),
						'in_array',
						array(':value', array_keys($field->options()))
					);
				}
			}
		}

		return parent::check($extra_validation);
	}

	/**
	 * Checks to see if the current field is unique.
	 *
	 * @param  Validation  $post
	 * @param  string	   $field
	 */
	public function unique_value(Validation $post, $field) {
		if (isset($this->_changed[$field])) {
			$result = DB::select('id')
				->from($this->table_name())
				->where($field, '=', $post[$field])
				->execute($this->_db)
				->current();

			if ($result) {
				$this->id = $result['id'];
				$this->validation()->error($field, 'unique_value', array(HTML::anchor($this->url('view'), 'View Item')));
			}
		}
	}

	/**
	 * Overrides the activity type.
	 *
	 * @param  string  $type  activity type
	 */
	public function activity_type($type) {
		$this->_activity_type = $type;
	}

	/**
	 * Runs the Oxygen hooks and then updates the object. Also sets the global_search and global_item objects.
	 *
	 * @param  Validation $validation  Validation object
	 * @return OModel
	 */
	public function update(Validation $validation = null) {
		if (empty($this->_changed)) {
			// Nothing to do....
			return $this;
		}

		// Run Pre-Hooks
		$this->_pre_hooks('update');

		// Set updated and updated_by columns
		if (($user = Auth::instance()->get_user()) !== false) {
			if ($this->_updated_column !== null && $this->_updated_by_column !== null) {
				$this->{$this->_updated_by_column} = $user->id;
			}
		}

		// Serialize arrays and objects
		$serialized = array();
		foreach ($this->_changed as $key => $value) {
			if (isset($this->_object[$key]) && !is_scalar($this->_object[$key])) {
				$this->_object[$key] = json_encode($this->_object[$key]);
				$serialized[] = $key;
			}
		}

		if ($validation === null) {
			$validation = $this->validation('update');
		}
		$results = parent::update($validation);

		// Set cache here because _load_values takes care of json_decode when retrieved
		OCache::instance()->set($this->id, $this, get_class($this));

		// JSON Decode on the way out
		foreach ($serialized as $key) {
			$this->_object[$key] = json_decode($this->_object[$key]);
		}

		// Set global content
		$this->set_global();

		// Run Post-Hooks
		$this->_post_hooks('update', 'update');

		return $results;
	}

	/**
	 * Runs the Oxygen hooks and then creates the object. Also sets the global_search and global_item objects.
	 *
	 * @param  Validation $validation  Validation object
	 * @return OModel
	 */
	public function create(Validation $validation = null) {
		// Run Pre-Hooks
		$this->_pre_hooks('create');

		// Set updated and updated_by columns
		if (($user = Auth::instance()->get_user()) !== false) {
			if ($this->_created_column !== null && $this->_created_by_column !== null &&
			   (!$this->loaded() || isset($this->_changed[$this->_primary_key]))) {
				$this->{$this->_created_by_column} = $user->id;
			}
			if ($this->_updated_column !== null && $this->_updated_by_column !== null) {
				$this->{$this->_updated_by_column} = $user->id;
			}
		}

		// Serialize arrays and objects
		$serialized = array();
		foreach ($this->_changed as $key => $value) {
			if (isset($this->_object[$key]) && !is_scalar($this->_object[$key])) {
				$this->_object[$key] = json_encode($this->_object[$key]);
				$serialized[] = $key;
			}
		}

		// Run the ORM create
		if ($validation === null) {
			$validation = $this->validation('create');
		}
		parent::create($validation);

		// Set cache here because _load_values takes care of json_decode when retrieved
		OCache::instance()->set($this->id, $this, get_class($this));

		// JSON Decode on the way out
		foreach ($serialized as $key) {
			$this->_object[$key] = json_decode($this->_object[$key]);
		}

		// Set global content
		$this->set_global();

		// Run Post-Hooks
		$this->_post_hooks('create', 'post');

		return $this;
	}

	/**
	 * Deletes the record, global data and cache.
	 *
	 * @chainable
	 * @return OModel
	 */
	public function delete() {
		// Run Pre-Hooks
		$this->_pre_hooks('delete');

		// [ORM::delete]
		$results = parent::delete();

		// Clear cache entry
		OCache::instance()->delete($this->id, get_class($this));

		// Delete global content
		$this->delete_global();

		// Run Post-Hooks
		$this->_post_hooks('delete');

		return $results;
	}

	/**
	 * Delete all objects in the associated table. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function delete_all() {
		$this->_build(Database::DELETE);

		$query = DB::delete($this->_table_name);
		foreach ($this->_db_pending as $pending) {
			$query = call_user_func_array(array($query, $pending['name']), $pending['args']);
		}
		$query->execute($this->_db);

		// Clear cache entries
		OCache::instance()->delete_all(get_class($this));

		return $this->clear();
	}

	/**
	 * Obsoletes an object.
	 *
	 * @chainable
	 * @return OModel
	 */
	public function obsolete() {
		// Run Pre-Hooks
		$this->_pre_hooks('obsolete');

		// Toggle the column
		$this->{$this->_obsolete_column} = 1;

		// [ORM::save]
		$results = $this->save();

		// Delete global search
		$this->delete_global(true);

		// Run Post-Hooks
		$this->_post_hooks('obsolete');

		return $results;
	}

	/**
	 * Set validation for the model, should be implemented via switch as shown in example.
	 *
	 * @return  mixed  null|Validation
	 */
	public function validation($type = 'create') {
		switch ($type) {
			case 'create':
				return null;
				break;
			case 'update':
				return null;
				break;
		}
		return null;
	}

	/**
	 * Checks to see if the object is obsolete.
	 *
	 * @return bool
	 */
	public function is_obsolete() {
		if ($this->_obsolete_column !== null) {
			return $this->{$this->_obsolete_column};
		}
	}

	/**
	 * Is this object enabled?
	 *
	 * @return bool
	 */
	public function enabled() {
		if ($this->_enabled_column !== null) {
			return (bool) $this->{$this->_enabled_column};
		}

		return true;
	}

	/**
	 * Is this object cloneable?
	 *
	 * @return bool
	 */
	public function cloneable() {
		return $this->_cloneable;
	}

	/**
	 * Reinstates an object.
	 *
	 * @chainable
	 * @return OModel
	 */
	public function reinstate() {
		// Run Pre-Hooks
		$this->_pre_hooks('reinstate');

		// Toggle the column
		$this->{$this->_obsolete_column} = 0;

		// [ORM::save]
		$results = $this->save();

		// Set global item
		$this->set_global();

		// Run Post-Hooks
		$this->_post_hooks('reinstate');

		return $results;
	}

	/**
	 * Gets the view for the specified key
	 *
	 * @param  mixed  $key   view key, or an array of $key => $view
	 * @param  string $view  path to view
	 * @return mixed
	 */
	public function view($key, $view = null) {
		if (is_array($key)) {
			foreach ($key as $key2 => $view) {
				$this->view($key2, $view);
			}
		}
		else {
			if ($view === null) {
				return isset($this->_views[$key]) ? $this->_views[$key] : null;
			}
			$this->_views[$key] = $view;
		}

		return $this;
	}

	/**
	 * Gets the meta for the specified key.
	 *
	 * @param  string  $key  meta key
	 * @return string|bool
	 */
	public function meta($key) {
		return isset($this->_meta[$key]) ? $this->_meta[$key] : false;
	}

	/**
	 * Gets the sortable columns.
	 *
	 * @return array
	 */
	public function sortable_columns() {
		$cols = array();
		$fields = array_keys($this->list_columns());
		natcasesort($fields);
		foreach ($fields as $field) {
			if ((isset($this->_fields[$field]) && !in_array($this->_fields[$field]->type(), array('hidden', 'textarea')))
				&& !in_array($field, array('created_by', 'updated_by', 'obsolete'))) {

				$label = $this->_fields[$field]->list_label();
				if (empty($label)) {
					$label = ucwords(Inflector::humanize($field));
				}

				$cols[$field] = $label;
			}
		}

		return $cols;
	}

	/**
	 * Define views to override on lists.
	 *
	 * @return array
	 */
	public function list_views() {
		return array();
	}

	/**
	 * List actions.
	 *
	 * @return array
	 */
	public function list_actions() {
		$actions = array(
			'edit',
			'delete'
		);

		if ($this->_cloneable) {
			$actions[] = 'clone';
		}
		return $actions;
	}

	/**
	 * List header actions.
	 *
	 * @return array
	 */
	public function list_header_actions() {
		return array(
			'search'
		);
	}

	/**
	 * Show the "New" button on lists?
	 *
	 * @return bool
	 */
	public function show_add() {
		if (Auth::instance()->has_permission('add', $this)) {
			return true;
		}
	}

	/**
	 * Returns an array of columns for the requested fieldgroup.
	 *
	 * @param  string  $key  fieldgroup key
	 * @return array
	 */
	public function columns($key) {
		// Build the column names
		$columns = array();
		foreach ($this->fieldgroup($key) as $field) {
			$columns[$field->name()] = $field->list_label();
		}

		return $columns;
	}

	/**
	 * Clears the fields.
	 */
	public function clear_fields() {
		$this->_fields = array();
		$this->_fieldgroups = array(
			'add' => '',
			'edit' => '',
			'view' => '',
			'search' => '',
			'list' => '',
		);
	}

	/**
	 * Assigns fields to a fieldgroup.
	 *
	 * @param  string  $key     fieldgroup key
	 * @param  string  $fields  collection of OFields
	 */
	public function set_fieldgroup($key, $fields) {
		if (!is_array($fields)) {
			$fields = array($fields);
		}

		$this->_fieldgroups[$key] = $fields;
	}

	/**
	 * Returns an array of field objects for the requested fieldgroup.
	 *
	 * @param  string  $key        fieldgroup key
	 * @param  bool    $keys_only  return just the keys
	 * @return array
	 */
	public function fieldgroup($key, $keys_only = false) {
		$fields = array();
		if (isset($this->_fieldgroups[$key])) {
			foreach ($this->_fieldgroups[$key] as $field) {
				if ($keys_only) {
					$fields[] = $field;
				}
				else if (isset($this->_fields[$field])) {
					if ($key == 'list') {
						$key = 'view';
					}
					else if (!in_array($key, array('search', 'view'))) {
						$key = 'edit';
					}
					$fields[$field] = $this->field($field)->display($key);
				}
			}
		}

		return $fields;
	}

	/**
	 * Sets or gets a field that is assigned to the model.
	 *
	 * @param  string  $key    access key
	 * @param  OField  $field  OField object
	 * @return OField|OForm|bool
	 */
	public function field($key, $field = null) {
		if ($field === null) {
			if (empty($this->_fields)) {
				$this->fields_init();
			}

			return isset($this->_fields[$key]) ? $this->_fields[$key] : false;
		}

		$this->_fields[$key] = $field;
		return $this;
	}

	/**
	 * Sets the friendly values for data loaded from the database.
	 */
	public function friendly_values() {
		foreach ($this->_fields as $key => $field) {
			if ($field->type() == 'flag') {
				$value = $field->value();
				if ((int) $value) {
					$this->field($key)->value('Yes');
				}
				else {
					$this->field($key)->value('No');
				}
			}
			else {
				if ($field->use_related() === true) {
					if ($field->value() == '0') {
						$value = '-';
					}
					else {
						$id = $field->value();
						if ($id instanceof OModel) {
							$id = $id->pk();
						}

						$related = $this->{$field->name()}
							->values(array('id' => $id), array('id'))
							->find();
						if ($related->loaded()) {
							$value = $related->name();
							if ($field->link_related() === true) {
								$value = HTML::anchor($related->url('view'), HTML::chars($value));
							}
						}
						else {
							$value = $field->value();
						}
					}
					$this->field($key)->value($value);
				}
			}
		}

		return $this;
	}

	/**
	 * Sets the field values.
	 *
	 * @param  bool   $ignore_post  ignore $_POST values?
	 * @param  array  $fields       fields to set values for
	 */
	public function set_field_values($ignore_post = false, $fields = null) {
		if ($fields === null) {
			$fields = $this->_fields;
		}

		foreach ($fields as $key => $data) {
			if (Arr::get($_POST, $key) && !$ignore_post) {
				$fields[$key]->value(Arr::get($_POST, $key));
			}
			else {
				$value = null;
				$field = $fields[$key];
				if ($field->default_value() === null) {
					if ($field->display() != 'search') {
						$related = $this->_related($key);
						if ($related && isset($this->{$key.$related->_foreign_key_suffix})) {
							$value =  $this->{$key.$related->_foreign_key_suffix};
						}
						else if (isset($this->$key)) {
							$value = $this->$key;
						}
					}

					if ($value !== null) {
						$field->default_value($value);
						$field->value($value);
					}
				}
				else if (isset($this->$key)) {
					$field->value($this->$key);
				}
			}

			if (isset($this->_fields[$key])) {
				$this->_fields[$key] = $fields[$key];
			}
		}

		return $this;
	}

	/**
	 * Getter/setter for $_audit_status.
	 *
	 * @return mixed
	 */
	public function audit_status($status = null) {
		if (is_null($status)) {
			return $this->_audit_status;
		}
		$this->_audit_status = $status;
		return $this;
	}

	/**
	 * Gets or sets the audit data to be logged to the audit table.
	 *
	 * @return array
	 */
	public function audit_data($key = null, $value = null) {
		// getter
		if (is_null($key)) {
			$data = array();
			foreach ($this->list_columns() as $key => $field) {
				$data[$key] = $this->$key;
			}

			// Additional Audit Data (stomping
			$this->_set_audit_data();
			$data = Arr::merge($data, $this->_audit_data);
			return $data;
		}
		// setter
		$this->_set_audit_data($key, $value);
		return $this;
	}

	/**
	 * Sets Audit data. This method does nothing by default, but if you want it to do something
	 * then overload this method in your extending model. This method is not designed to be
	 * called directly. To set audit data, use the audit_data() method.
	 *
	 * @param  string  $key    key
	 * @param  mixed   $value  value
	 */
	protected function _set_audit_data($key = null, $value = null) {
		if (!is_null($key)) {
			$this->_audit_data[$key] = $value;
		}
	}

	/**
	 * Returns the view column for lists.
	 *
	 * @return string
	 */
	public function view_column() {
		return 'name';
	}

	/**
	 * Returns the name of the current ORM object.
	 *
	 * @return string
	 */
	public function name() {
		if (isset($this->name)) {
			return $this->name;
		}

		return '';
	}

	/**
	 * Returns the ID of the current ORM object.
	 *
	 * @return string
	 */
	public function id() {
		return (string) $this->pk();
	}

	/**
	 * Returns the default sort column for lists.
	 *
	 * @return array
	 */
	public function sort_column() {
		if ($this->_created_column !== null) {
			if (is_array($this->_created_column)) {
				$column = $this->_created_column['column'];
			}
			else {
				$column = $this->_created_column;
			}

			return array(
				$column => 'desc'
			);
		}

		return array(
			$this->_primary_key => 'asc'
		);
	}

	/**
	 * Detects a collision in changed data.
	 *
	 * @param  int     $id     item ID
	 * @param  string  $nonce  nonce key
	 * @return array|bool
	 */
	public function no_collision($id = null, $nonce = null) {
		$updated = Arr::get($_POST, 'updated');
		if ($updated) {
			$class = get_class($this);
			$previous = OModel::factory($class, $id);

			if ($updated != $previous->updated) {
				$diff = array();

				foreach ($this->fieldgroup('edit') as $field => $data) {
					if (Arr::get($_POST, $field)) {
						if (is_array($this->$field) || is_array($previous->$field)) {
							if (serialize($this->$field) != serialize($previous->$field)) {
								$diff[] = $field;
							}
						}
						else if ($this->$field != $previous->$field) {
							$diff[] = $field;
						}
					}
				}

				if (count($diff)) {
					$this->set_field_values();
					$previous->set_field_values(true);

					return array(
						'model' => $this,
						'previous' => $previous,
						'diff' => $diff,
						'nonce' => $nonce
					);
				}
			}
		}

		return true;
	}

	/**
	 * Figures out where to send a form after a successful post.
	 *
	 * @param  string  $key  URL type
	 * @return string
	 */
	public function destination($key, $value = null) {
		if (!is_null($value)) {
			$this->_destinations[$key] = $value;
			return $this;
		}
		if (isset($this->_destinations[$key])) {
			$target = $this->_destinations[$key];
			switch ($target) {
				case '_view':
				case '_edit':
				case '_list':
					return $this->url(str_replace('_', '', $target));
					break;
			}
			return $target;
		}

		switch ($key) {
			case 'add':
			case 'edit':
			case 'delete':
			case 'clone':
				$url = $this->url('list');
				break;
			case 'reinstate':
				$url = $this->url('view');
				break;
			default:
				$url = $this->url($key);
				break;
		}

		$data = OHooks::instance()->filter(
			get_class($this).'.destination',
			compact('url', 'key')
		);

		return $data['url'];
	}

	/**
	 * Builds a link for the current model.
	 *
	 * @param  string  $type        type of link
	 * @param  string  $title       anchor text
	 * @param  array   $attributes  anchor attributes
	 * @return string
	 */
	public function link($type = 'edit', $title = null, array $attributes = array()) {
		if ($title === null) {
			switch ($type) {
				case 'edit':
				case 'edit_ajax':
				case 'view':
				case 'view_ajax':
					$title = HTML::chars($this->name());
					break;
				case 'delete':
				case 'delete_ajax':
					$title = __('Delete');
					break;
				case 'clone':
					$title = __('Clone');
					break;
				case 'reinstate':
					$title = __('Recover from trash?');
					break;
				case 'add':
				case 'add_ajax':
					$title = __('New :model_type', array(
						':model_type' => $this->meta('one_text')
					));
					break;
				case 'list':
					$title = $this->meta('mult_text');
					break;
				case 'search':
					$title = __('Search');
					break;
				case 'filter':
					$title = __('Filter');
					break;
			}
		}

		if (in_array($type, array('delete', 'clone', 'reinstate', 'edit', 'view', 'add', 'list', 'search', 'filter'))) {
			return HTML::anchor(URL::site_reverse($this->url($type)), $title, $attributes);
		}

		return '';
	}

	/**
	 * Returns a URL for the model.
	 *
	 * @param  string  $type  action
	 * @param  array   $url   URI
	 * @return string
	 */
	public function url($type = 'edit', array $url = array()) {
		if (!count($url)) {
			$path_model = str_replace('_', '/', $this->meta('one'));
			$path_model = OHooks::instance()->filter(
				get_class($this).'.url.path_model',
				$path_model
			);
			$path_models = str_replace('_', '/', $this->meta('mult'));
			$path_models = OHooks::instance()->filter(
				get_class($this).'.url.path_models',
				$path_models
			);
			switch ($type) {
				case 'edit':
				case 'edit_ajax':
				case 'view':
				case 'view_ajax':
				case 'delete':
				case 'delete_ajax':
				case 'clone':
				case 'reinstate':
					$url = array($path_models, $type, $this->id);
					break;
				case 'add':
				case 'add_ajax':
					$url = array($path_models, $type);
					break;
				case 'list':
					$url = array($path_models);
					break;
				case 'grid':
					$url[] = $path_models;
					if (Request::current()->action() == 'search') {
						$url[] = 'search';
						$url[] = Request::current()->param('key');
					}
					else {
						$url[] = 'grid';
					}
					break;
				case 'search':
					$url = array($path_models, 'search');
					break;
				case 'filter':
					$url = array('search', 'form', $path_model);
					break;
			}
		}

		$data = OHooks::instance()->filter(
			get_class($this).'.url',
			array(
				'url' => URL::site(implode('/', $url)),
				'type' => $type,
				'model' => $this,
			)
		);

		return $data['url'];
	}

	/**
	 * Builds the sortable link.
	 *
	 * @param  string  $sort   key
	 * @param  string  $label  label
	 * @return string
	 */
	public function sort_link($sort, $label) {
		$request = Request::current();
		$params = $request->param();
		$order = Arr::get($params, 'order', 'asc');
		if ($sort == Arr::get($params, 'sort')) {
			if ($order == 'asc') {
				$order = 'desc';
				$label .= ' &uarr;';
			}
			else {
				$order = 'asc';
				$label .= ' &darr;';
			}

			$params['order'] = $order;
		}

		$params['sort'] = $sort;
		$params['controller'] = Request::current()->controller();

		return HTML::anchor($request->route()->uri($params), $label, array(
			'data-page' => Arr::get($params, 'page', '1'),
			'data-sort' => $sort,
			'data-order' => $order,
		));
	}

	/**
	 * Loads the audit history for the current model.
	 *
	 * @return OModel
	 */
	public function history() {
		$limit = Oxygen::config('oxygen')->preference('audit_limit');
		if ($limit == null) {
			$limit = 25;
		}
		return OModel::factory('Audit')
			->where('table', '=', $this->table_name())
			->and_where('item', '=', $this->id)
			->limit($limit);
	}

	/**
	 * Runs the pre-hooks.
	 *
	 * @param  string  $method  the method name
	 */
	protected function _pre_hooks($method) {
		if ($method == 'create' || $method == 'update') {
			if (!empty($this->_changed)) {
				OHooks::instance()->modify(get_class($this).'.model.update.pre', $this);
			}
			else {
				OHooks::instance()->modify(get_class($this).'.model.create.pre', $this);
			}
		}
		else {
			OHooks::instance()->modify(get_class($this).'.model.'.$method.'.pre', $this);
		}
	}

	/**
	 * Runs the post-hooks, audit insertion, and activity insertion.
	 *
	 * @param  string  $method  the method
	 * @param  string  $action  action type
	 */
	protected function _post_hooks($method, $action = 'edit') {
		// Audit
		$audit_id = 0;
		if ($this->_audit_status !== OAudit::OFF && ($method == 'create' || $method == 'update') && Request::current()->action() != 'protected') {
			$audit = OModel::factory('Audit')->record($this, $action, ($this->_audit_status === OAudit::FORCE ? true : false));

			if ($audit !== false) {
				$audit_id = $audit->id;
			}
		}

		// Post-Hooks
		if ($method == 'update') {
			// UPDATE
			OHooks::instance()->modify(get_class($this).'.model.update.post');

			// Activity?
			if ($this->_activity_status !== OActivity::OFF) {
				OActivity::factory($this, $this->_activity_type)->save('update', $audit_id);
			}
		}
		else if ($method == 'create') {
			// INSERT
			OHooks::instance()->modify(get_class($this).'.model.create.post');

			// Activity?
			if ($this->_activity_status !== OActivity::OFF) {
				OActivity::factory($this, $this->_activity_type)->save('post', $audit_id);
			}
		}
		else {
			OHooks::instance()->modify(get_class($this).'.model.'.$method.'.post');

			// Activity
			if ($this->_activity_status !== OActivity::OFF) {
				$verb = 'delete';
				if ($method == 'reinstate') {
					$verb = $method;
				}
				OActivity::factory($this, $this->_activity_type)->save($verb);
			}
		}
	}

	/**
	 * Sets the global search and item content.
	 *
	 * @return bool|ORM
	 */
	protected function set_global() {
		if ($this->loaded()) {
			// Initialize the global item=
			if ($this->_create_global_item) {
				$global_item = ORM::factory('global_item')
					->where('guid', '=', Oxygen::guid($this))
					->find();
				$data = OHooks::instance()->filter(
					get_class($this).'.set_global.global_item_data',
					array(
						'guid' => Oxygen::guid($this),
						'title' => $this->name(),
						'view_url' => URL::site_reverse($this->url('view')),
						'edit_url' => URL::site_reverse($this->url('edit')),
						'model' => $this
					)
				);
				$global_item->values($data, array(
					'guid', 'title', 'view_url', 'edit_url'
				))->save();
			}

			// Initialize the global search
			if ($this->_include_in_global_search) {
				OModel::factory('global_search')->delete_all_by_guid(Oxygen::guid($this));

				foreach ($this->global_search_data() as $data) {
					$values = Arr::merge(array(
						'guid' => Oxygen::guid($this),
						'type' => get_class($this),
						'title_sort' => '',
						'summary' => '',
						'content' => ''
					), $data);

					OModel::factory('global_search')->values($values)->create();
				}
			}

			return $this;
		}

		return false;
	}

	/**
	 * Sets the global search data for this model.
	 *
	 * Available options:
	 * 	- title_sort
	 * 	- summary
	 * 	- content
	 *
	 * @return array
	 */
	protected function global_search_data() {
		return array(
			array(
				'title_sort' => strtolower(Text::alpha($this->name())),
				'summary' => $this->meta('one_text'),
				'content' => $this->name()
			),
		);
	}

	/**
	 * Delete global search and item content.
	 *
	 * @param  bool  $ignore_item  set to true to skip global item
	 */
	protected function delete_global($ignore_item = false) {
		if ($this->loaded()) {
			// global search
			if ($this->_include_in_global_search) {
				$search = OModel::factory('global_search')
					->where('guid', '=', get_class($this).'_'.$this->id)
					->find();
				if ($search->loaded()) {
					$search->delete();
				}
			}

			// global item
			if ($this->_create_global_item && !$ignore_item) {
				$item = OModel::factory('global_item', array('guid' => get_class($this).'_'.$this->id));
				if ($item->loaded()) {
					$item->delete();
				}
			}
		}
	}

	/**
	 * Stub for any future API output logic we want to implement.
	 *
	 * @return array
	 */
	public function api_output() {
		return $this->as_array();
	}

	/**
	 * Generates a UUID.
	 *
	 * @link http://www.php.net/manual/en/function.uniqid.php#69164
	 *
	 * @static
	 * @param  string $table database table to check
	 * @param  string $column the column to check for UUID
	 * @param  string $db database config to use
	 */
	public static function uuid($table = null, $column = 'id', $db = 'default') {
		$uuid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );

		if ($table != null) {
			$query = DB::select($column)
				->from($table)
				->where($column, '=', $uuid)
				->execute($db);

			if ($query->count()) {
				$uuid = OModel::uuid($table);
			}
		}

		return $uuid;
	}

	/**
	 * Returns a JSON-ready array of the object.
	 *
	 * @param  array  $extra   extra data to be added to the JSON response.
	 * @param  array  $ignore  values to ignore
	 * @return array
	 */
	public function json_response(array $extra = array(), array $ignore = array()) {
		foreach ($ignore as $key) {
			if (array_key_exists($key, $extra)) {
				unset($extra[$key]);
			}
		}
		return $extra;
	}

	/**
	 * @param  array   $fields  collection of searched fields
	 * @param  string  $key
	 * @return string|array
	 */
	public function search_fields(array $fields, $key = null) {
		if ($key === null) {
			// This is a new search, do work!
			$params = array();
			foreach ($fields as $field) {
				$value = $field->value();
				if (!empty($value)) {
					if ($field->type() == 'flag') {
						if ($value == 'yes') {
							$value = '1';
						}
						else if ($value == 'no') {
							$value = '0';
						}
					}
					$params[$field->name()] = $value;
				}
			}

			$params = json_encode($params);
			$search = OModel::factory('search', array('params' => $params));
			if (!$search->loaded()) {
				$search->model = $this->_object_name;
				$search->params = $params;
				$search->create();
			}
			return OShortkey::factory()->key($search->id);
		}
		else {
			$search = OModel::factory('Search', OShortkey::factory()->number($key));
			if ($search->loaded()) {
				foreach ($search->params as $field => $value) {
					if (isset($fields[$field])) {
						$fields[$field]->default_value($value);
					}
				}

				return $fields;
			}

			return false;
		}
	}

	/**
	 * Performs a search based on the fields passed in.
	 *
	 * @param  array  $fields
	 * @return array
	 */
	public function search(array $fields) {
		foreach ($fields as $field) {
			if (!in_array($field->name(), array('sort', 'sort_order', 'items_per_page'))) {
				$value = $field->value();
				if (!empty($value)) {
					if ($field->name() == 'created' || $field->name() == 'updated') {
						// Range
						if ($field->display_type() == 'range') {
							if ($field->name() == 'updated' && !empty($value[0])) {
								$this->where($this->_object_name.'.'.$field->name(), '>=', (!empty($value[0]) ? Date::utc($value[0]) : Date::utc(0)));
							}
							$this->where($this->_object_name.'.'.$field->name(), '<=', (!empty($value[1]) ? Date::utc($value[1]) : Date::utc(time())));
						}
						else {
							// TODO Fixed datetime logic
						}
					}
					else if ($field->name() == 'enabled') {
						if ($field->value() != 'both') {
							$this->where($this->_object_name.'.'.$field->name(), '=', $value);
						}
					}
					else {
						$this->where($this->_object_name.'.'.$field->name(), 'LIKE', '%'.$value.'%');
					}
				}
			}
		}

		$items_per_page = Oxygen::config('oxygen')->preference('search_items_per_page');
		if (isset($fields['items_per_page']) && $fields['items_per_page']->value() <= $items_per_page) {
			$value = $fields['items_per_page']->value();
			if (!empty($value)) {
				$items_per_page = $value;
			}
		}
		$total = clone $this;
		$total = $total->count_all();
		$pagination = Pagination::factory(array(
			'items_per_page' => $items_per_page,
			'total_items' => $total
		));
		$total = null;

		// Order by
		$results = $this->order_by($fields['sort']->value(), $fields['sort_order']->value())
			->find_all($pagination)
			->as_array();

		return array(
			'results' => $results,
			'pagination' => $pagination
		);
	}

	/**
	 * Checks for the obsolete check before automatically adding it.
	 *
	 * @return void
	 */
	private function _where_not_obsolete() {
		$found = false;
		foreach ($this->_db_pending as $pending) {
			if ($pending['name'] == 'where' && strpos($pending['args'][0], $this->_obsolete_column) !== false) {
				$found = true;
				break;
			}
		}

		if (!$found) {
			$this->where($this->_object_name.'.'.$this->_obsolete_column, '=', '0');
		}
	}

	/**
	 * Getter/Setter to tell the model to include/exclude obsoleted records
	 *
	 * @return bool|null
	 */
	public function include_obsolete($value = null) {
		if ($value === null) {
			return $this->_include_obsolete;
		}

		$this->_include_obsolete = (bool) $value;

		return $this;
	}

	/**
	 * Get or set the _crud_request type for a model.
	 *
	 * @chainable
     * @param  string  $type  standard|ajax
	 * @return OModel
	 */
	public function crud_request($type = null) {
		if ($type === null) {
			return $this->_crud_request;
		}

		$this->_crud_request = $type;

		return $this;
	}

	/**
	 * Ignore complex display?
	 *
	 * @param  string  $key   key to look for
	 * @param  array   $keys  array of ignored keys
	 * @return bool
	 */
	public function ignore_complex_display($key, array $keys = array()) {
		$keys = Arr::merge($keys, array('created', 'updated'));
		return !in_array($key, $keys);
	}

	/**
	 * Turns activity logging on and off.
	 *
	 * @param  bool  $toggle
	 * @return OModel
	 */
	public function log_activity($toggle) {
		if ($toggle) {
			$this->_activity_status = OActivity::AUTO;
		}
		else {
			$this->_activity_status = OActivity::OFF;
		}

		return $this;
	}

	/**
	 * Turns audit loggin on and off.
	 *
	 * @param  bool  $toggle
	 * @return void
	 */
	public function log_audit($toggle) {
		if ($toggle) {
			$this->_audit_status = OAudit::AUTO;
		}
		else {
			$this->_audit_status = OAudit::OFF;
		}
	}

	/**
	 * Returns the foreign key suffix.
	 *
	 * @return string
	 */
	public function foreign_key_suffix() {
		return $this->_foreign_key_suffix;
	}

	public function kv_data_enabled() {
		return !is_null($this->_kv_data_column);
	}

	public function kv_data_get($key = null) {
		// not enabled? get out
		if (!$this->kv_data_enabled()) {
			return false;
		}
		// no data? get out.
		if (empty($this->kv_data)) {
			return null;
		}
		// make sure we handle JSON decoded as object or array
		if (is_array($this->kv_data)) {
			$kv_data = $this->kv_data;
		}
		else {
			$kv_data = array();
			foreach ($this->kv_data as $k => $v) {
				$kv_data[$k] = $v;
			}
		}
		// return everything
		if (is_null($key)) {
			$data = $kv_data;
		}
		// multiple keys
		else if (is_array($key)) {
			$data = array();
			foreach ($key as $k) {
				if (isset($kv_data[$k])) {
					$data[$k] = $kv_data[$k];
				}
			}
		}
		// single key
		else {
			if (isset($kv_data[$key])) {
				$data = $kv_data[$key];
			}
		}
		$filtered = OHooks::instance()->filter(
			get_class($this).'.kv_data',
			compact('data', 'key')
		);
		return $filtered['data'];
	}

	public function kv_data_set($key, $value = null) {
		// not enabled? get out
		if (!$this->kv_data_enabled()) {
			return false;
		}
		// get a new model just in case other properties are set on this one
		// we only want to update the KV data here
		$class = get_class($this);
		$model = new $class;
		$model->where($model->primary_key(), '=', $this->pk())
			->find();

		// whoops, no model
		if (!$model->loaded()) {
			return false;
		}

		if (is_array($key)) {
			// set multiple values
			$kv_data = array_merge($model->kv_data, $key);
		}
		else {
			// set a single value
			$kv_data = $model->kv_data;
			$kv_data[$key] = $value;
		}
		$model->set('kv_data', $kv_data);
		$model->update();

		return $this;
	}

} // End Oxygen_Model
