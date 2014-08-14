<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Model_Oxygen_Setting extends OModel implements IteratorAggregate {

	/**
	 * @var  int  "primary key" fix for ORM and Audits
	 */
	public $id = 1;

	/**
	 * @var  bool  don't auto init
	 */
	protected $_auto_init = false;

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
	 * @var string obsolete column
	 */
	protected $_obsolete_column = null;

	/**
	 * @var array meta keys
	 */
	protected $_meta = array(
		'one' => 'setting',
		'mult' => 'settings',
		'one_text' => 'Setting',
		'mult_text' => 'Settings'
	);

	/**
	 * @var  string  primary key
	 */
	protected $_primary_key = 'id';

	/**
	 * @var  string  created by column
	 */
	protected $_created_by_column = null;

	/**
	 * @var  array  updated column
	 */
	protected $_created_column = null;

	/**
	 * @var  string  updated by column
	 */
	protected $_updated_by_column = null;

	/**
	 * @var  array Settings data
	 */
	protected $_data = array();

	/**
	 * @var bool Don't attempt to reload object on unserialize
	 */
	protected $_reload_on_wakeup = false;

	/**
	 * Make sure to include settings data on serialize
	 *
	 * @access public
	 * @return string
	 */
	public function serialize() {
		$serialized_data = parent::serialize();
		$data = unserialize($serialized_data);
		$data['_data'] = $this->_data;
		return serialize($data);
	}

	/**
	 * Required by IteratorAggregate interface
	 *
	 * @param  mixed  $id
	 */
	public function getIterator() {
		return new ArrayIterator($this->_data);
	}

	public function __get($key) {
		return Arr::get($this->_data, $key, null);
	}

	public function __set($key, $value) {
		$this->_data[$key] = $value;
	}

	/**
	 * Sets the name for the model, since one doesn't actually exist.
	 *
	 * @param  mixed  $id
	 */
	public function __construct($id = null) {
		$this->name = __('System Settings');
		$this->view('edit', 'settings/edit');
		parent::__construct($id);
	}

	/**
	 * Return hard-coded name.
	 *
	 * @return array
	 */
	public function name() {
		return 'Settings';
	}

	/**
	 * Returns an array of model permissions.
	 *
	 * @return array
	 */
	public function permissions() {
		return array(
			'system' => array(
				'manage_system_settings'
			),
		);
	}

	/**
	 * Returns nav menu settings.
	 *
	 * @return array
	 */
	public function nav_menu() {
		return array(
			'text' => 'Settings',
			'url' => 'settings',
			'permissions' => array(
				array('manage_system_settings', 'Setting')
			)
		);
	}

	/**
	 * Initialize the fields. This method will only be called once by
	 * Oxygen_Model::init().
	 */
	public function fields_init() {
		// Load Roles
		$roles = array();
		$_roles = OModel::factory('Role')->type('system')->find_all();
		foreach ($_roles as $role) {
			$roles[$role->id] = $role->name;
		}

		// Fields
		$this->_fields += array(
			'theme' => OField::factory('select')
				->model($this)
				->name('theme')
				->label(__('Default Theme'))
				->options(OTheme::instance()->options())
				->default_value('default'),

			'email_from' => OField::factory()
				->model($this)
				->name('email_from')
				->label(__('System Email Address'))
				->default_value(Oxygen::config('oxygen')->get('email_from'))
				->help('right', __('Used for Lost Password emails, etc.')),

			'remember_me_days' => OField::factory()
				->model($this)
				->name('remember_me_days')
				->label(__('Remember Me Duration'))
				->default_value(Oxygen::config('oxygen')->get('remember_me_days'))
				->help('right', __('Days to keep a user logged in when they check the Remember Me box')),

			'items_per_page' => OField::factory()
				->model($this)
				->name('items_per_page')
				->label(__('Default List Pagination'))
				->default_value(Oxygen::config('oxygen')->get('items_per_page'))
				->help('right', __('Items to show per page in lists')),

			'search_items_per_page' => OField::factory()
				->model($this)
				->name('search_items_per_page')
				->label(__('Default Search Pagination'))
				->default_value(Oxygen::config('oxygen')->get('search_items_per_page'))
				->help('right', __('Items to show per page in search results')),

			'audit_limit' => OField::factory()
				->model($this)
				->name('audit_limit')
				->label(__('Audit History Display Limit'))
				->default_value(Oxygen::config('oxygen')->get('audit_limit'))
				->help('right', __('Maximum audit records to retrieve at once')),

			'salt' => OField::factory()
				->model($this)
				->name('salt')
				->label(__('Randomization Key'))
				->default_value(Oxygen::config('oxygen')->get('salt'))
				->help('right', __('When this is changed, users may have to log in again')),

			'heartbeat_key' => OField::factory()
				->model($this)
				->name('heartbeat_key')
				->label(__('Heartbeat Access Key'))
				->default_value(Oxygen::config('oxygen')->get('heartbeat_key'))
				->help('right', __('Required to access the health status of the application')),

			'default_role' => OField::factory('select')
				->model($this)
				->name('default_role')
				->label(__('Default User Role'))
				->options($roles)
				->value(Oxygen::config('oxygen')->get('default_role', 0))
				->help('right', __('Used when creating new users')),

			'timezone' => OField::factory('select')
				->model($this)
				->name('timezone')
				->label(__('Default System Timezone'))
				->options(Date::timezones())
				->default_value(Oxygen::config('oxygen')->get('timezone', 'America/Denver'))
				->help('right', __('Default timezone that times will be displayed in')),

			'date_format' => OField::factory()
				->model($this)
				->name('date_format')
				->label(__('Date Format'))
				->default_value(Oxygen::config('oxygen')->get('date_format'))
				->help('right', __('Accepts any valid <a href="http://www.php.net/date">PHP date format</a>')),

			'time_format' => OField::factory()
				->model($this)
				->name('time_format')
				->label(__('Time Format'))
				->default_value(Oxygen::config('oxygen')->get('time_format'))
				->help('right', __('Accepts any valid <a href="http://www.php.net/date">PHP time format</a>')),

		);

		// Fieldgroups
		$this->_fieldgroups += array(
			'edit' => array(
				'theme',
				'email_from',
				'remember_me_days',
				'items_per_page',
				'search_items_per_page',
				'audit_limit',
				'salt',
				'heartbeat_key',
				'default_role',
				'timezone',
				'date_format',
				'time_format',
			)
		);

		$this->_fieldgroups = OHooks::instance()->filter(
			get_class($this).'.fields_init.fieldgroups',
			$this->_fieldgroups
		);

		// Override table columns
		$this->_table_columns = array();
		foreach ($this->fieldgroup('edit') as $key => $field) {
			$this->_table_columns[$key] = array();
		}

		return parent::fields_init();
	}

	/**
	 * Saves the settings
	 *
	 * @param  Validation  $validation  Validation object
	 */
	public function save(Validation $validation = null) {
		$this->_pre_hooks('update');

		$result = false;
		foreach ($this as $key => $field) {
			if (isset($this->_fields[$key]) || in_array($key, array('application_install_version', 'oxygen_install_version'))) {
				$sql = "
					INSERT
					  INTO ".$this->_db->quote_table($this->table_name())."(`key`, `value`, `updated`)
					VALUES(:key, :value, :updated)
					ON DUPLICATE KEY UPDATE
					`value` = :value,
					`updated` = :updated
				";
				$value = OHooks::instance()->filter(
					get_class($this).'.save.'.$key,
					$this->_data[$key]
				);
				$result = DB::query(Database::INSERT, $sql)
					->param(':key', $key)
					->param(':value', $value)
					->param(':updated', DB::expr('NOW()'))
					->execute($this->_db);
			}
		}

		// Audit?
		if ($result) {
			// Global Item
			$this->_loaded = true;
			$this->set_global();
		}

		OCache::instance()->set(get_class($this), $this);

		// Post-Hooks
		$this->_post_hooks('update');
	}

	/**
	 * Returns the URI for the model.
	 *
	 * @param  string  $type  URL type
    * @param  array   $uri
	 * @return string
	 */
	public function url($type = 'edit', array $uri = array()) {
		return URL::site('settings');
	}

	/**
	 * Need to flag the _changed attribute for this class so activity logs correctly.
	 *
	 * @param  string  $method
	 * @param  string  $action
	 */
	protected function _post_hooks($method, $action = 'edit') {
		$this->_changed = true;
		parent::_post_hooks($method, $action);
	}

	/**
	 * Override audit data with all settings
	 * @return array
	 */
	public function audit_data($key = null, $value = null) {
		$data = array();
		$settings = $this->find_all();
		foreach ($settings as $key => $value) {
			$data[$key] = $value;
		}
		return $data;
	}

	/**
	 * Returns all settings as an object, each setting
	 * a property on that object so settings can be accessed
	 * directly such as $settings->theme
	 *
	 * @param Pagination $pagination Unused
	 * @return Model_Setting
	 */
	public function find_all(Pagination $pagination = null) {
		$result = OCache::instance()->get(get_class($this));
		if ($result === null) {
			$sql = "
				SELECT *
				  FROM ".$this->_db->quote_table($this->table_name())."
			";

			$db_result = DB::query(Database::SELECT, $sql)
				->execute($this->_db);

			$result = OModel::factory('Setting');
			foreach ($db_result as $item) {
				$result->_data[$item['key']] = $item['value'];
			}

			OCache::instance()->set(get_class($this), $result);
		}

		return OHooks::instance()->filter(get_class($this).'.model.find_all.post', $result);
	}

	/**
	 * Wrapper for find_all, maintains hooks
	 *
	 * @return OModel
	 */
	public function find() {
		OHooks::instance()->modify(get_class($this).'.model.find.pre', array($this));

		$result = $this->find_all();

		OHooks::instance()->modify(get_class($this).'.model.find.post', array($result));

		return $result;
	}

} // End Model_Oxygen_Setting
