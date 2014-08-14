<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Permissions {

	/**
	 * @var  Permissions  object
	 */
	public static $instance;

	/**
	 * Creates an instance of the permissions class.
	 *
	 * @static
	 * @return OPermissions
	 */
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Builds the default roles drop menu.
	 *
	 * @param  array       $roles  custom-defined roles
	 * @param  Model_User  $user   user object
	 * @return array
	 */
	public function roles_fields(array $roles, $user) {
		$config = Oxygen::config('roles');

		$fields = array();
		foreach ($config as $key => $display) {
			$options = array(
				'-' => __('(Custom Permissions)'),
			);
			foreach ($roles as $role) {
				if ($role->type == $key) {
					$options[$role->id] = $role->name;
				}
			}

			$name = 'role_id';
			if ($key != 'system') {
				$name = $key.'_'.$name;
			}

			$field = OField::factory('select')
				->model($user)
				->name($name)
				->label(__(':type Role:', array(':type' => $display)))
				->options($options)
				->help('right', HTML::anchor('roles', __('Edit')));
			if (!is_null($user->$name)) {
				$field->value($user->$name);
			}
			else if ($key == 'system' && $name == 'role_id') {
				$default_role_id = Oxygen::config('oxygen')->get('default_role');
				if (!empty($default_role_id)) {
					$field->value($default_role_id);
				}
			}
			$fields[] = $field;
		}

		return $fields;
	}

	/**
	 * Flatten the permissions.
	 *
	 * @param  array  $permissions
	 * @return array
	 */
	public function flatten($permissions = array()) {
		$flat = array();
		if (is_array($permissions) || is_object($permissions)) {
			foreach ($permissions as $group => $keys) {
				foreach ($keys as $key) {
					$flat[] = $group.':'.$key;
				}
			}
		}
		return $flat;
	}

	/**
	 * Loads all of the permission checkboxes.
	 *
	 * @param  array   $permissions  permissions to be checked by default
	 * @param  string  $type         permissions type
	 * @param  bool    $enabled      checkboxes enabled?
	 * @param  bool    $show_label   show the fieldset label?
	 * @return array
	 */
	public function checkboxes($permissions = array(), $type = null, $enabled = true, $show_label = true) {
		$groups = array();
		$checked = $this->flatten($permissions);
		$data = OHooks::instance()->filter(
			'permissions_checkboxes_data',
			array(
				'permissions' => Oxygen::config('oxygen')->get('permissions')
			)
		);

		foreach ($data['permissions'] as $model => $_groups) {
			$model = str_replace('Model_', '', $model);
			$roles_config = Oxygen::config('roles');
			foreach ($_groups as $key => $perms) {
				if ($type !== null && $key !== $type) {
					continue;
				}
				
				if (!isset($groups[$key])) {
					$groups[$key] = array(
						'display' => $roles_config[$key],
						'fieldsets' => array()
					);
				}

				if (count($perms)) {
					if ($model == 'application') {
						$title = __('General');
					}
					else {
						$title = ucwords(Inflector::humanize($model));
					}

					// Options
					$options = array();
					foreach ($perms as $perm) {
						$options[$model.':'.$perm] = ucwords(Inflector::humanize($perm));
					}

					$attributes['id'] = 'permissions_'.$model;
					if (!$enabled) {
						$attributes['disabled'] = 'disabled';
					}

					$permissions_field = OField::factory('checkbox')
						->name('permissions')
						->label('')
						->options($options)
						->value($checked)
						->attributes($attributes)
						->select_all($show_label);

					// Fieldset
					$model = OModel::factory($model);
					$groups[$key]['fieldsets'][] = OFieldset::factory()
						->model($model)
						->legend($title)
						->field('permissions', $permissions_field)
						->add_css_class('permissions', true);
				}
			}
		}

		$data = OHooks::instance()->filter('permissions_checkboxes_fieldsets', compact('groups', 'checked', 'enabled'));
		extract($data);
		if ($type === null) {
			return $groups;
		}

		return $groups[$type];
	}

	/**
	 * Tiers the permissions.
	 *
	 * @param  array  $permissions  permissions
	 * @return array
	 */
	public function tier(array $permissions) {
		$tiered = array();
		if (is_array($permissions) && count($permissions)) {
			foreach ($permissions as $permission) {
				$data = explode(':', $permission);
				if (!isset($tiered[$data[0]])) {
					$tiered[$data[0]] = array();
				}
				$tiered[$data[0]][] = $data[1];
			}
		}

		return $tiered;
	}

} // End Oxygen_Permissions
