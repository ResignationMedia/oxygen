<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 *
 * @property-read int $id
 * @property-read int $account_id
 * @property int $role_id
 * @property boolean $enabled
 * @property boolean $obsolete
 * @property boolean $password_change
 * @property int $created_by
 * @property int $updated_by
 * @property string $api_key
 * @property string $username
 * @property string $password
 * @property string $password_key
 * @property string $email
 * @property string $name
 * @property string $company
 * @property string $photo_type
 * @property string $web
 * @property string $last_login
 * @property string $created
 * @property string $updated
 * @property string $profile
 * @property Model_Preference $preferences
 *
 * @property Model_Permission $permission
 */
abstract class Model_Oxygen_User extends OModel {

	/**
	 * @var  string  activity type
	 */
	protected $_activity_type = 'user';

	/**
	 * @var  array  meta keys
	 */
	protected $_meta = array(
		'one' => 'user',
		'mult' => 'users',
		'one_text' => 'User',
		'mult_text' => 'Users'
	);

	/**
	 * @var  string  table name
	 */
	protected $_table_name = 'users';

	/**
	 * @var  array  has many relationships
	 */
	protected $_has_many = array(
		'user_tokens' => array('model' => 'User_Token'),
		'preferences' => array('model' => 'Preference'),
		'permissions' => array('model' => 'Permission')
	);

	/**
	 * @var  array  has one relationships
	 */
	protected $_has_one = array(
		'global' => array('model' => 'global_item', 'foreign_key' => 'guid'),
	);

	/**
	 * @var  array  belongs to relationships
	 */
	protected $_belongs_to = array(
		'role' => array(
			'model' => 'role'
		)
	);

	/**
	 * Prepares the model database connection and loads the object.
	 *
	 * @param   mixed  $id  Parameter for find or object to load
	 */
	public function __construct($id = null) {
		$this->destination('edit', '_edit');
		$this->view('add', 'user/add');
		parent::__construct($id);
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
				'delete',
				'update_profile',
				'set_permissions',
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
			'text' => 'Users',
			'url' => 'users',
			'permissions' => array(
				array('view', 'User'),
				array('add', 'User'),
				array('edit', 'User'),
				array('delete', 'User')
			)
		);
	}

	/**
	 * Initializes the form fields and fieldgroups.
	 */
	public function fields_init() {
		parent::fields_init();

		// Fields
		$this->_fields += array(
			'username' => OField::factory()
				->model($this)
				->name('username'),

			'name' => OField::factory()
				->model($this)
				->name('name'),

			'email' => OField::factory('email')
				->model($this)
				->name('email'),

			'company' => OField::factory()
				->model($this)
				->name('company'),

			'website' => OField::factory()
				->model($this)
				->name('website')
				->label(__('Website')),

			'profile' => OField::factory('textarea')
				->model($this)
				->name('profile'),

			'enabled' => OField::factory('flag')
				->model($this)
				->name('enabled')
				->default_value('1'),

			'password_change' => OField::factory('flag')
				->model($this)
				->name('password_change')
				->label(__('Force user to change password on next login'))
				->list_label(__('Force Password Change'))
				->default_value('1'),

			'api_key' => OField::factory()
				->model($this)
				->name('api_key')
				->label('API Key'),

			'role' => OField::factory()
				->model($this)
				->name('role')
				->link_related(true),

			'enabled' => OField::factory('flag')
				->model($this)
				->name('enabled')
				->default_value('1'),

			'timezone' => OField::factory('select')
				->model($this)
				->name('timezone')
				->label(__('Timezone'))
				->options(Date::timezones())
				->default_value(Oxygen::config('oxygen')->get('timezone', 'America/Denver')),

			'created' => OField::factory('date')
				->model($this)
				->name('created')
				->show_time(true),

			'updated' => OField::factory('date')
				->model($this)
				->name('updated')
				->show_time(true),
		);

		// Fieldgroups
		$this->_fieldgroups += array(
			'add' => array(
				'name',
				'username',
				'email',
			),
			'edit' => array(
				'name',
				'username',
				'email',
				'enabled',
				'password_change',
			),
			'view' => array(
				'name',
				'username',
				'email',
				'api_key',
				'enabled',
				'password_change',
			),
			'search' => array(
				'name',
				'username',
				'email',
				'role',
				'enabled',
				'created',
				'updated',
			),
			'list' => array(
				'name',
				'username',
				'email',
				'role',
				'enabled',
				'created',
			),
			'preferences' => array(
				'timezone',
			),
			'profile' => array(
				'name',
				'email',
			),
		);

		return $this;
	}

	/**
	 * Returns the default sort column for lists.
	 *
	 * @return string
	 */
	public function sort_column() {
		return array(
			'name' => 'asc'
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
			),
			'username' => array(
				array('not_empty'),
				array(array($this, 'unique_value'), array(':validation', ':field')),
			),
			'email' => array(
				array('not_empty'),
				array('email', array(':value', true)),
				array(array($this, 'unique_value'), array(':validation', ':field')),
			),
			'password' => array(
				array('not_empty'),
			),
		);
	}

	/**
	 * Define views to override on lists.
	 *
	 * @return array
	 */
	public function list_views() {
		return array(
			'row_header' => 'user/list/row/header',
			'row' => 'user/list/row/item',
			'row_empty' => 'user/list/row/empty',
		);
	}

	/**
	 * Verifies the permissions array contains valid options.
	 *
	 * @param  Validation  $extra_validation  Validation object
	 * @return ORM
	 */
	public function check(Validation $extra_validation = null) {
		$roles = null;
		$fields = null;
		$config = Oxygen::config('roles');
		foreach ($config as $key => $display) {
			$key = ($key == 'system' ? '' : $key.'_').'role_id';
			// TODO This should be checking the object, not $_POST
			if (Arr::get($_POST, $key)) {
				if ($roles === null) {
					// "Role" model find_all returns array, not result
					$roles = OModel::factory('Role')->find_all();
					$fields = OPermissions::instance()->roles_fields($roles, $this);
				}

				if ($extra_validation === null) {
					$extra_validation = Validation::factory($_POST);
				}

				foreach ($fields as $field) {
					if ($field->name() == $key) {
						$extra_validation->rule($key, 'in_array', array(':value', array_keys($field->options())));
					}
				}
			}
		}

		return parent::check($extra_validation);
	}

	/**
	 * Sets the generic password data before saving a new user.
	 *
	 * @param  Validation  $validation  Validation object
	 * @return ORM
	 */
	public function create(Validation $validation = null) {
		$this->api_key = API::instance()->generate_key();
		$this->password = ( $this->password ? $this->password : Auth::instance()->generate_password() );
		$this->username = Text::alphanum($this->username);

		if (!$this->enabled()) {
			$this->{$this->_enabled_column} = 1;
		}

		return parent::create($validation);
	}

	/**
	 * Creates the administrator account during install.
	 *
	 * @param  Validation  $validation  Validation object
	 * @return ORM
	 */
	public function create_admin(Validation $validation = null) {
		$this->role_id = 1;
		$this->api_key = API::instance()->generate_key();
		$this->username = Text::alphanum($this->username);
		$this->password = Auth::instance()->hash_password($this->password);

		// don't create an audit record
		$this->_audit_status = OAudit::OFF;

		return parent::create($validation);
	}

	/**
	 * Allows a model use both email and username as unique identifiers for login
	 *
	 * @param   string  $value  value being checked
	 * @return  string
	 */
	public function unique_key($value) {
		return Valid::email($value) ? 'email' : 'username';
	}

	/**
	 * Gets the user's name.
	 *
	 * @return string
	 */
	public function name() {
		if ($this->name !== null && !empty($this->name)) {
			return $this->name;
		}

		return $this->username;
	}

	/**
	 * Loads the permissions for the current user.
	 *
	 * @return array
	 */
	public function get_permissions() {
		$permissions = OCache::instance()->get($this->id.'_permissions', 'permissions');
		if ($permissions === null) {
			// check for role
			if ($this->role_id == 1) { // superuser
				$permissions = array();
				$_permissions = Oxygen::config('oxygen')->get('permissions');
				if (is_array($_permissions) && !empty($_permissions)) {
					foreach ($_permissions as $model => $_perms) {
						$model_name = str_replace('Model_', '', $model);
						foreach ($_perms as $group => $keys) {
							if (!isset($permissions[$group])) {
								$permissions[$group] = array();
							}
							$permissions[$group][$model_name] = $keys;
						}
					}
				}
			}
			else if (!empty($this->role_id)) {
				$role = OModel::factory('Role', $this->role_id);
				$permissions = array(
					$role->type => array()
				);
				foreach ($role->permissions as $_group => $keys) {
					$permissions[$role->type][$_group] = array();
					foreach ($keys as $key) {
						$permissions[$role->type][$_group][] = $key;
					}
				}
			}
			// no role, use granular permissions
			else {
				$results = $this->permissions->find_all();
				$permissions = array();
				foreach ($results as $result) {
					if (!isset($permissions[$result->type])) {
						$permissions[$result->type] = array();
					}
					if (!isset($permissions[$result->type][$result->group])) {
						$permissions[$result->type][$result->group] = array();
					}
					$permissions[$result->type][$result->group][] = $result->key;
				}
			}
			OCache::instance()->set($this->id.'_permissions', $permissions, 'permissions');
		}
		return (array) $permissions;
	}

	/**
	 * Builds the upload directory for the current user.
	 *
	 * @return string
	 */
	public function upload_directory() {
		$username = strtolower(Auth::instance()->get_user()->username);
		$directory = DOCROOT.'content/users';

		if (!is_dir($directory)) {
			@mkdir($directory, 0777, true);
		}

		$directory .= '/'.substr($username, 0, 1);
		if (!is_dir($directory)) {
			@mkdir($directory, 0777, true);
		}

		$directory .= '/'.substr($username, 1, 1);
		if (!is_dir($directory)) {
			@mkdir($directory, 0777, true);
		}

		return $directory.'/';
	}

	/**
	 * Builds the profile photo image tag.
	 *
	 * @param  string  $size    profile photo size
	 * @param  int     $width   width override
	 * @param  int     $height  height override
	 * @return string
	 */
	public function profile_photo($size = 'thumbnail', $width = 0, $height = 0) {
		$configs = Oxygen::config('oxygen.user_photo.sizes');
		$path = OTheme::find_file('img', 'no-profile-photo', 'jpg');
		if (isset($configs[$size])) {
			$path = $this->profile_photo_url($size, false);
		}
		return HTML::image(
			$path,
			array(
				'alt' => $this->name(),
				'width' => ($width ? $width : $configs[$size]['width']),
				'height' => ($height ? $height : $configs[$size]['height']),
				'class' => 'photo usr-photo size'.Utility::html($size)
			)
		);
	}

	/**
	 * Returns the path to the uploaded photo, if it exists.
	 *
	 * @param  string  $size photo size
	 * @return string
	 */
	public function uploaded_photo_url($size) {
		$file = Media::path(array($this->id, $this->username, $size), 'user/profile');
		if (file_exists($file)) {
			return URL::site(str_replace(DOCROOT, '', $file));
		}

		return '';
	}

	/**
	 * Checks to see if the user has an uploaded photo.
	 *
	 * @return bool
	 */
	public function has_uploaded_photo() {
		$path = $this->uploaded_photo_url('thumbnail');
		return !empty($path);
	}

	/**
	 * Builds the profile photo URL.
	 *
	 * @param  string  $size         photo size
	 * @param  bool    $encode_html  encode the response?
	 * @return string
	 */
	public function profile_photo_url($size = 'thumbnail', $encode_html = true) {
		$configs = Oxygen::config('oxygen.user_photo.sizes');

		if (!isset($configs[$size])) {
			return '#';
		}

		switch ($this->photo_type) {
			case 'upload':
				$url = $this->uploaded_photo_url($size);
				break;
			case 'gravatar':
			default:
				$url = Gravatar::url($this->email, $configs[$size]['width'], $encode_html);
				break;
		}

		return $url;
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
		if ($type == 'profile') {
			if (empty($title)) {
				$title = __('Profile');
			}

			return HTML::anchor($this->url($type), $title, $attributes);
		}

		return parent::link($type, $title, $attributes);
	}

	/**
	 * Builds a URL.
	 *
	 * @param  string  $type  type of URL
    * @param  array   $uri
	 * @return string
	 */
	public function url($type = 'edit', array $uri = array()) {
		$url = '';
		switch ($type) {
			case 'profile':
				$url = 'profile';
				break;
			case 'profile_photo':
				$url = 'profile/photo';
				break;
			case 'profile_password':
				$url = 'profile/password';
				break;
			case 'permissions':
				$url = 'users/permissions/'.$this->id;
				break;
		}

		if (!empty($url)) {
			return $url;
		}

		return parent::url($type, $uri);
	}

	/**
	 * Adds a favorite.
	 *
	 * @param  Bookmark  $bookmark  Bookmark object
	 * @param  string	 $key       group name
	 * @return bool
	 */
	public function favorite_add(Bookmark $bookmark, $key = '*') {
		$favorites = $this->preference('favorites');
		if ($favorites === null) {
			$favorites = (object) array();
		}

		if (!isset($favorites->$key)) {
			$favorites->$key = (object) array();
		}

		$favorites->$key->{md5($bookmark->url())} = $bookmark->as_object();

		$this->favorites_sort($favorites);
		$this->preference('favorites', $favorites);
		return true;
	}

	/**
	 * Deletes a favorite.
	 *
	 * @param  string  $url  favorite URL
	 * @return bool
	 */
	public function favorite_delete($url) {
		$favorites = $this->preference('favorites');
		foreach ($favorites as $group => $bookmarks) {
			foreach ($bookmarks as $hash => $bookmark) {
				$key = md5($url);
				if ($hash === $key) {
					unset($favorites->$group->$key);

					if (!count((array) $favorites->$group)) {
						// No longer have any favorites in this group.
						unset($favorites->$group);
					}

					// Sort
					$this->favorites_sort($favorites);
					$this->preference('favorites', $favorites);

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Sorts the list of favorites.
	 *
	 * @param  object  $favorites  favorites to be sorted
	 * @return bool
	 */
	private function favorites_sort($favorites) {
		if (is_object($favorites)) {
			$favorites = (array) $favorites;
		}

		foreach ($favorites as $group => $keys) {
			$favorites[$group] = (array) $favorites[$group];
			usort($favorites[$group], 'Model_User::favorites_natsort');
			$favorites[$group] = (object) $favorites[$group];
		}

		ksort($favorites);
		return (object) $favorites;
	}

	/**
	 * Sorts the values based on title.
	 *
	 * @static
	 * @param  Favorite  $a  first object
	 * @param  Favorite  $b  second object
	 * @return int
	 */
	public static function favorites_natsort($a, $b) {
		$a = strtolower($a->title);
		$b = strtolower($b->title);

		if ($a == $b) {
			return 0;
		}

		return ($a < $b) ? -1 : +1;
	}

	/**
	 * Loads a preference object.
	 *
	 * @param  string  $key      preference key
	 * @param  mixed   $value    preference value
	 * @return mixed
	 */
	public function preference($key, $value = null) {
		$preferences = $this->preferences();
		if ($value === null) {
			if (isset($preferences[$key])) {
				if (is_string($preferences[$key]->value)) {
					if (($value = json_decode($preferences[$key]->value)) !== null) {
						$preferences[$key]->value = $value;
					}
					else if (($value = @unserialize($preferences[$key]->value)) !== false) {
						$preferences[$key]->value = $value;
					}
				}

				return $preferences[$key]->value;
			}

			// Try to load it from the config
			return null;
		}
		else {
			if (is_array($value) || is_object($value)) {
				$value = json_encode($value);
			}

			if (!isset($preferences[$key])) {
				$preferences[$key] = OModel::factory('preference');
				$preferences[$key]->key = $key;
				$preferences[$key]->user_id = $this->id;
			}

			$preferences[$key]->value = $value;
			$preferences[$key]->save();

			// Set the new cache
			OCache::instance()->set('user_'.$this->id, $preferences, 'preferences');
		}
	}

	/**
	 * Loads the user's preferences.
	 *
	 * @return array
	 */
	public function preferences() {
		$preferences = OCache::instance()->get('user_'.$this->id, 'preferences', array());
		if (empty($preferences)) {
			$prefs = OModel::factory('preference')->where('user_id', '=', $this->id)->find_all();
			foreach ($prefs as $pref) {
				$preferences[$pref->key] = $pref;
			}

			OCache::instance()->set('user_'.$this->id, $preferences, 'preferences');
		}

		return $preferences;
	}

	/**
	 * Set the new password for the user.
	 *
	 * @return bool
	 */
	public function set_password($new_password, $confirm_password) {
		try {
			$validation = Validation::factory(array(
				'password' => $new_password,
				'confirm_password' => $confirm_password
			))
				->rule('password', 'not_empty')
				->rule('password_confirm', 'matches', array(':validation', 'confirm_password', 'password'));

			$this->password = Auth::instance()->hash_password($new_password);
			$this->password_key = '';
			$this->password_change = 0;
			$this->update($validation);
		}
		catch (ORM_Validation_Exception $e) {
			foreach ($e->errors('validation') as $error) {
				Msg::add('error', $error);
			}
			return false;
		}

		return true;
	}

	/**
	 * Sets the Roles field object for searches.
	 *
	 * @param  string  $key
	 * @param  bool    $keys_only
	 * @return array
	 */
	public function fieldgroup($key, $keys_only = false) {
		$fields = parent::fieldgroup($key, $keys_only);

		if ($key == 'search') {
			$roles = OModel::factory('role')->find_all();
			$options = array();
			foreach ($roles as $role) {
				$options[$role->id] = $role->name;
			}

			if (!empty($options)) {
				$field = OField::factory('checkbox')->name('role')
					->options($options)
					->display('search');
				if ($fields['role']->default_value() !== null) {
					$field->default_value($fields['role']->default_value());
				}
				if ($fields['role']->value() !== null) {
					$field->value($fields['role']->value());
				}

				$fields['role'] = $field;
			}
			else if (isset($fields['role'])) {
				unset($fields['role']);
			}
		}

		return $fields;
	}

	/**
	 * Adds the role constraints to the search.
	 *
	 * @param  array  $fields
	 * @return array
	 */
	public function search(array $fields) {
		if (isset($fields['role'])) {
			$value = $fields['role']->value();
			if (!empty($value)) {
				$this->with('role');
				$this->where($this->role->object_name().'.'.$this->role->primary_key(), 'IN', $value);
			}
			unset($fields['role']);
		}
		return parent::search($fields);
	}

	/**
	 * Returns the user(s) that has the defined role.
	 *
	 * @param  int  $role_id
	 * @return array
	 */
	public function find_by_role($role_id) {
		$results = DB::select($this->_primary_key)
			->from($this->_table_name)
			->where('role_id', '=', $role_id)
			->execute($this->_db);

		$ids = array();
		foreach ($results as $result) {
			$ids[] = $result[$this->_primary_key];
		}

		return $ids;
	}

} // End Model_Oxygen_User
