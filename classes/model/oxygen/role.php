<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Model_Oxygen_Role extends OModel {

	/**
	 * @var  array  meta keys
	 */
	protected $_meta = array(
		'one' => 'role',
		'mult' => 'roles',
		'one_text' => 'Role',
		'mult_text' => 'Roles'
	);

	/**
	 * @var  string  table name
	 */
	protected $_table_name = 'roles';

	/**
	 * @var  array  has one relationship
	 */
	protected $_has_one = array(
		'global' => array('model' => 'global_item', 'foreign_key' => 'guid'),
	);

	/**
	 * Initialize the fields. This method will only be called once by
	 * Oxygen_Model::init().
	 */
	public function fields_init() {
		// Fields
		$this->_fields += array(
			'name' => OField::factory()
				->model($this)
				->name('name'),

			'type' => OField::factory('select')
				->model($this)
				->name('type')
				->options(Oxygen::config('roles')->as_array()),

			'enabled' => OField::factory('flag')
				->model($this)
				->name('enabled')
				->default_value(1),

// permissions are handled specially in the roles controller
// 			'permissions' => OField::factory('checkbox')
// 				->model($this)
// 				->name('permissions'),

			'created' => OField::factory('date')
				->model($this)
				->name('created')
				->date_format(Oxygen::config('oxygen')->get('date_format'))
				->time_format(Oxygen::config('oxygen')->get('time_format')),

			'updated' => OField::factory('date')
				->model($this)
				->name('updated')
				->date_format(Oxygen::config('oxygen')->get('date_format'))
				->time_format(Oxygen::config('oxygen')->get('time_format')),
		);

		// Fieldsets
		$this->_fieldgroups += array(
			'add' => array(
				'name',
				'type',
				'permissions',
			),
			'edit' => array(
				'name',
				'type',
				'permissions',
			),
			'search' => array(
				'name',
				'type',
				'enabled',
				'created',
				'updated',
			),
			'list' => array(
				'name',
				'type',
				'created',
			)
		);

		return parent::fields_init();
	}

	/**
	 * Returns an array of model permissions.
	 *
	 * @return array
	 */
	public function permissions() {
		return array(
			'system' => array(
				'view',
				'add',
				'edit',
				'delete'
			)
		);
	}

	/**
	 * Returns nav menu settings.
	 *
	 * @return array
	 */
	public function nav_menu() {
		return array(
			'text' => 'Roles',
			'url' => 'roles',
			'permissions' => array(
				array('view', 'Role'),
				array('add', 'Role'),
				array('edit', 'Role'),
				array('delete', 'Role')
			)
		);
	}

	/**
	 * Rule definitions for validation
	 *
	 * @return array
	 */
	public function rules() {
		return array(
			'name' => array(
				array('not_empty'),
				array(array($this, 'unique_value'), array(':validation', ':field'))
			),
			'permissions' => array(
				array('not_empty'),
			)
		);
	}

	/**
	 * Handles setting of column
	 *
	 * @param  string $column Column name
	 * @param  mixed  $value  Column value
	 * @return OModel
	 */
	public function set($column, $value) {
		return parent::set($column, $value);
	}

	/**
	 * Tiers the permissions.
	 *
	 * @chainable
	 * @param  array  $values    Array of column => val
	 * @param  array  $expected  Array of keys to take from $values
	 * @return ORM
	 */
	public function values(array $values, array $expected = NULL) {
		if (isset($values['permissions'])) {
			$values['permissions'] = OPermissions::instance()->tier($values['permissions']);
		}

		return parent::values($values, $expected);
	}

	/**
	 * Finds all of the roles.
	 *
	 * @param  Pagination $pagination  pagination object
	 * @return array
	 */
	public function find_all(Pagination $pagination = null) {
		$roles = OCache::instance()->get('roles', 'user');
		if ($roles === null) {
			$roles = parent::find_all($pagination);
			$roles = $roles->as_array();
			OCache::instance()->set('roles', $roles, 'user');
		}

		return $roles;
	}

	/**
	 * Finds users with this role and converts their roles to custom permissions.
	 *
	 * @return ORM
	 */
	public function obsolete() {
		$ids = OModel::factory('User')->find_by_role($this->pk());
		foreach ($ids as $id) {
			Auth::instance()->set_permissions($id, $this->permissions);
		}
		return parent::obsolete();
	}

	/**
	 * Set the friendly values for the role type.
	 *
	 * @return void
	 */
	public function friendly_values() {
		parent::friendly_values();

		$config = Oxygen::config('roles');
		$type = $this->field('type');

		if ($type->default_value() !== null && isset($config[$type->default_value()])) {
			$type->value($config[$type->default_value()]);
		}
	}

	/**
	 * Sets the type of role to load.
	 *
	 * @param  string  $type
	 * @return Model_Role
	 */
	public function type($type) {
		return $this->where('type', '=', $type);
	}

} // End Model_Oxygen_Role
