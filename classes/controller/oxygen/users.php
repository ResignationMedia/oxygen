<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Users extends Controller_Tabs {

	/**
	 * @var  string  model name
	 */
	protected $_model_name = 'User';

	/**
	 * @var  bool  display profile?
	 */
	protected $_profile = false;

	/**
	 * @var  array  edit tabs
	 */
	public $_tabs = array(
		'edit' => array(
			'users/edit/' => 'Profile',
			'users/password/' => 'Password',
			'users/permissions/' => 'Permissions',
			'users/api/' => 'API Access',
		),
	);

	/**
	 * Actions to automatically initialize the model.
	 *
	 * @return array
	 */
	protected function init_model_actions() {
		return Arr::merge(parent::init_model_actions(), array(
			'password',
			'api'
		));
	}

	/**
	 * Add an object.
	 */
	public function action_add() {
		// Hooks
		OHooks::instance()->add_listener(get_class($this).'.add.view_data', array(
			get_class($this),
			'add_view_data'
		))->add_listener(get_class($this).'.add.model_saved', array(
			get_class($this),
			'add_model_saved'
		));

		parent::action_add();
	}

	/**
	 * Edit an object.
	 */
	public function action_edit() {
		// Hooks
		OHooks::instance()->add_listener(get_class($this).'.edit.view_data', array(
			get_class($this),
			'edit_view_data',
		))->add_listener(get_class($this).'.edit.model_saved', array(
			get_class($this),
			'edit_model_saved',
		))->add_listener('user_edit.form.header_data', array(
			get_class($this),
			'user_edit_form_header_data',
		));

		parent::action_edit();
	}

	/**
	 * View an object.
	 */
	public function action_view() {
		// Hooks
		OHooks::instance()->add_listener(get_class($this).'.view.view_data', array(
			get_class($this),
			'view_view_data'
		));

		parent::action_view();
	}

	/**
	 * Delete an object.
	 */
	public function action_delete() {
		$id = $this->request->param('id', 0);
		if ($id == Auth::instance()->get_user()->id) {
			$this->request->redirect('users');
		}

		parent::action_delete($id);
	}

	/**
	 * Permissions
	 */
	public function action_permissions() {
		// Does the user have proper permissions?
		if (!Auth::instance()->has_permission('set_permissions', $this->_model_name)) {
			throw new Oxygen_Access_Exception;
		}

		$this->_init_model();
		$this->_load_model();
		$this->_create_nonce();

		// Hooks
		OHooks::instance()->add_listener(get_class($this).'.permissions.view_data', array(
			get_class($this),
			'header_view_data'
		))->add_listener('permissions.form.header_data', array(
			get_class($this),
			'user_edit_form_header_data',
		));

		// Form posted?
		if (Arr::get($_POST, 'save')) {
			if (Nonce::check_fatal($this->_nonce_action, $this->_model->id)) {
				$config = Oxygen::config('roles');
				$role_ids = array();
				foreach ($config as $key => $display) {
					$_key = ($key == 'system' ? '' : $key.'_').'role_id';
					$role_id = Arr::get($_POST, $_key, 0);

					$permissions = array();
					if (Arr::get($_POST, 'permissions')) {
						foreach (Arr::get($_POST, 'permissions') as $permission) {
							$permission = explode(':', $permission);
							$_permissions = OModel::factory($permission[0])->permissions();

							if (isset($_permissions[$key]) && in_array($permission[1], $_permissions[$key])) {
								$permissions[$permission[0]][] = $permission[1];
							}
						}
					}

					if (count($permissions)) {
						Auth::instance()->set_permissions($this->_model->id, $permissions, $key);
						$role_ids[$_key] = 0;
					}
					else {
						$role_ids[$_key] = $role_id;
					}
				}

				foreach ($role_ids as $property => $value) {
					$this->_model->$property = $value;
				}
				$this->_model->update();

				$success = $this->_model->meta('one_text').' successfully saved.';
				if ($this->request->is_ajax()) {
					$row = $this->_model->meta('one').'_'.$this->_model->pk();
					$this->action_grid();
					$this->template->response += array(
						'row' => $row,
						'msgs' => array(
							array(
								'type' => 'confirm',
								'text' => $success
							)
						)
					);
					return;
				}

				// Confirmation
				Msg::add('confirm', 'Permissions saved.');

				// Redirect
				$this->request->redirect($this->_model->destination('permissions'));
			}

			// Error...
			Msg::add('error', 'Sorry, something unexpected happened.');
		}

		$permissions = $this->_model->get_permissions();

		// Build the form object
		$form = OForm::factory()
			->model($this->_model)
			->title('Edit User')
			->name('permissions')
			->fields(array(Nonce::field($this->_nonce_action, $this->_model->id)))
			->button('save',
				OField::factory('submit')
					->model($this->_model)
					->name('save')
					->default_value('Save')
			)
			->view('header', 'form/header/tabs/default')
			->attributes(array(
				'class' => 'edit'
			))
			->add_css_class('base', true)
			->add_css_class('box-tabs', true)
			->content('permissions_content', View::factory('user/permissions', array(
				'model' => $this->_model,
				'roles' => OModel::factory('Role')->find_all(),
				'permissions' => $permissions['system']
			)));

		// Add Breadcrumb
		$this->breadcrumbs->clear()->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		))->title_encoded = true;

		// Favorite
		$title = __('Set Permissions for: :model_link', array(
			':model_link' => $this->_model->link('edit')
		));
		$this->favorites->title(strip_tags($title));

		// Get the hook to modify data.
		$data = OHooks::instance()->filter(get_class($this).'.permissions.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form,
		));

		$this->template->set(array(
			'title' => $title,
			'content' => View::factory($this->_model->view('edit'), $data)
		));
	}

	/**
	 * Change the users password.
	 *
	 * @throws Oxygen_Access_Exception
	 */
	public function action_password($bypass_auth = false) {
		if (!$bypass_auth) {
			// Does the user have proper permissions?
			$id = $this->request->param('id', 0);
			if (!in_array($this->request->uri(), $this->public_uris()) &&
				($id && !Auth::instance()->has_permission('edit', $this->_model_name))) {
				throw new Oxygen_Access_Exception;
			}
		}

		$this->_init_model();
		$this->_load_model();
		$this->_create_nonce();

		// Hooks
		OHooks::instance()->add_listener(get_class($this).'.password.view_data', array(
			get_class($this),
			'password_view_data'
		))->add_listener('set_password.form.header_data', array(
			get_class($this),
			'user_edit_form_header_data',
		));

		if ($this->request->post('save')) {
			// Validation
			Nonce::check_fatal($this->_nonce_action, $this->_model->id);
			$new_password = $this->request->post(md5('password'.Security::token()));
			$password_confirm = $this->request->post(md5('password_confirm'.Security::token()));

			if ($this->_model->set_password($new_password, $password_confirm)) {
				$this->_model = OHooks::instance()->filter(get_class($this).'.password.model_post_save', $this->_model);

				$success = $this->_model->meta('one_text').' successfully saved.';
				if ($this->request->is_ajax()) {
					$row = $this->_model->meta('one').'_'.$this->_model->pk();
					$this->action_grid();
					$this->template->response += array(
						'row' => $row,
						'msgs' => array(
							array(
								'type' => 'confirm',
								'text' => $success
							)
						)
					);
					return;
				}

				// Confirmation message
				Msg::add('confirm', 'Password successfully updated.');

				// Redirect
				$this->request->redirect($this->_model->destination('edit'));
			}
		}

		// Set breadcrumbs
		$this->breadcrumbs->clear()->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		))->title_encoded = true;

		// Set template content
		$title = __('Set Password for: :model_link', array(
			':model_link' => $this->_model->link('edit')
		));
		$this->favorites->title(strip_tags($title));
		$this->template->set(array(
			'title' => $title
		));

		// Initialize the form
		$instructions = 'Enter a new password in both of the fields below to change this user\'s password.';
		$token = Security::token(true);
		$form = OForm::factory()
			->model($this->_model)
			->title('Set Password')
			->name('set_password')
			->content('action_password_instructions', $instructions, 'instructions')
			->view('header', 'form/header/tabs/default')
			->fields(array(
				OField::factory('password')
					->model($this->_model)
					->name(md5('password'.$token))
					->label('New Password'),

				OField::factory('password')
					->model($this->_model)
					->name(md5('password_confirm'.$token))
					->label('Type New Password Again'),

				Nonce::field($this->_nonce_action, $this->_model->id)
			))
			->button('save',
				OField::factory('submit')
					->model($this->_model)
					->name('save')
					->default_value('Save')
			)
			->attributes(array(
				'class' => 'edit'
			))
			->add_css_class('base', true)
			->add_css_class('box-tabs', true);

		// Build the view data
		$data = OHooks::instance()->filter(get_class($this).'.password.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form
		));

		// Set template content
		$this->template->set(array(
			'content' => View::factory($this->_model->view('edit'), $data)
		));
	}

	/**
	 * Edit API Key access.
	 */
	public function action_api($bypass_auth = false) {
		if (!$bypass_auth) {
			if (!Auth::instance()->has_permission('edit', $this->_model_name)) {
				throw new Oxygen_Access_Exception;
			}
		}

		// Hooks
		OHooks::instance()->add_listener(get_class($this).'.api.view_data', array(
			get_class($this),
			'api_view_data'
		))->add_listener('api_access.form.header_data', array(
			get_class($this),
			'user_edit_form_header_data',
		));

		$this->_init_model();
		$this->_load_model();
		$this->_create_nonce();

		// Form posted?
		if (Arr::get($_POST, 'save')) {
			try {
				// Check nonce
				Nonce::check_fatal($this->_nonce_action, $this->_model->id);

				// Save the Key
				$this->_model->api_key = API::instance()->generate_key();
				$this->_model->update();

				// Set template content
				$this->template->set(array(
					'response' => array(
						'result' => 'success',
						'html' => $this->_model->api_key
					)
				));
			}
			catch (ORM_Validation_Exception $e) {
				// Set template content
				$this->template->set(array(
					'response' => array(
						'result' => 'error'
					)
				));
			}

			return;
		}


		// Set template content
		$title = __('API Access for: :model_link', array(
			':model_link' => $this->_model->link('edit')
		));
		$this->breadcrumbs->clear()->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		))->title_encoded = true;
		$this->favorites->title(strip_tags($title));

		$this->_model->fields_init();
		$this->_model->set_field_values();
		$field = $this->_model->field('api_key')
			->view('edit', 'user/api_key')
			->value($this->_model->api_key)
			->unique('api_key');

		$form = OForm::factory()
			->title('API Access')
			->name('api_access')
			->model($this->_model)
			->fields(array(
				$field,
				Nonce::field($this->_nonce_action, $this->_model->id),
			))
			->view('header', 'form/header/tabs/default')
			->attributes(array(
				'class' => 'edit'
			))
			->add_css_class('base', true)
			->add_css_class('box-tabs', true);

		// Set template content
		$data = OHooks::instance()->filter(get_class($this).'.api.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form
		));

		if ($this->request->is_ajax()) {
			//@TODO use new Msg system
			$result = 'success';
			$errors = $this->session->get('errors', '');
			if ($errors) {
				$result = 'error';
				$errors = View::factory('chrome/messages', array(
					'class' => 'error',
					'messages' => $errors,
				));
			}
			$data = Arr::merge($data, array('errors' => $errors));
			$this->template->response = array(
				'result' => $result,
				'html' => View::factory($this->_model->view('ajax_edit'), $data)->render()
			);
		}
		else {
			$this->template->set(array(
				'title' => $title,
				'content' => View::factory($this->_model->view('edit'), $data)
			));
		}
	}

	/**
	 * Hook Methods
	 * ------------
	 *
	 * These methods can be used when adding Hooks with OHooks::instance()->add_listener().
	 */

	/**
	 * Hook to modify the CRUD $form and inject permissions data.
	 *
	 * @param  string  $data  data to be modified
	 * @return array
	 */
	public static function add_view_data($data) {
		$email_login_info = OField::factory('flag')
			->model($data['model'])
			->name('email_login_info')
			->label('Email username and password details to this user')
			->default_value('1');

		// add preferences
		foreach ($data['model']->fieldgroup('preferences') as $key => $field) {
			$data['form']->field($key, $field);
		}

		$data['form']->field('email_login_info', $email_login_info);

		$roles = OModel::factory('Role')->find_all();
		$default_role_id = Oxygen::config('oxygen')->get('default_role');

		return Arr::merge($data, compact('roles', 'default_role_id'));
	}

	/**
	 * Sets the user's permissions, and emails their login info.
	 *
	 * @static
	 * @param  Model_User $model
	 */
	public static function add_model_saved($model) {
		// Set Permissions
		$role_id = Arr::get($_POST, 'role_id', 0);
		if (empty($role_id) || $role_id == '-') {
			$permissions = array();
			if (Arr::get($_POST, 'permissions')) {
				foreach (Arr::get($_POST, 'permissions') as $permission) {
					$permission = explode(':', $permission);
					$permissions[$permission[0]][] = $permission[1];
				}
			}
			Auth::instance()->set_permissions($model->id, $permissions);
		}
		else {
			$model->set('role_id', $role_id);
			$model->update();
		}

		// set preferences
		foreach ($model->fieldgroup('preferences') as $key => $field) {
			$value = Arr::get($_POST, $key);
			if (!is_null($value)) {
				$model->preference($key, $value);
			}
		}

		if (Arr::get($_POST, 'email_login_info')) {
			Email::factory(Oxygen::config('oxygen')->get('app_name').': Welcome')
				->from(Oxygen::config('oxygen')->get('email_from'), Oxygen::config('oxygen')->get('app_name'))
				->to($model->email)
				->message(View::factory('auth/email/welcome', array(
					'user' => $model,
					'password' => Auth::instance()->get_generated_password(),
				)))
				->send();
		}
	}

	/**
	 * Updates the user's timezone.
	 *
	 * @static
	 * @param  Model_User $model
	 */
	public static function edit_model_saved($model) {
		foreach ($model->fieldgroup('preferences') as $key => $field) {
			$value = Arr::get($_POST, $key);
			if (!is_null($value)) {
				$model->preference($key, $value);
			}
		}
	}

	/**
	 * A quick change to some data on the $form object before we go to edit.
	 *
	 * @param  array  $data  data to filter
	 * @return array
	 */
	public static function edit_view_data(array $data) {
		$data = self::header_view_data($data);

		// add preferences
		foreach ($data['model']->fieldgroup('preferences') as $key => $field) {
			$value = $data['model']->preference($key);
			if (is_null($value)) {
				$value = Oxygen::config('oxygen')->get($key, null);
			}
			$data['form']->field($key, $field->value($value));
		}

		return $data;
	}

	/**
	 * Filter to move the header tabs to the form.
	 *
	 * @static
	 * @param  array  $data
	 * @return array
	 */
	public static function user_edit_form_header_data(array $data) {
		if (isset($data['form']->header_tabs)) {
			$data['header_tabs'] = $data['form']->header_tabs;
		}
		return $data;
	}

	/**
	 * A quick change to some data on the $form object before we go to edit.
	 *
	 * @param  array  $data  data to filter
	 * @return array
	 */
	public static function api_view_data(array $data) {
		return self::header_view_data($data);
	}

	/**
	 * A quick change to some data on the $form object before we go to edit.
	 *
	 * @param  array  $data  data to filter
	 * @return array
	 */
	public static function password_view_data(array $data) {
		return self::header_view_data($data);
	}

	/**
	 * A quick change to some data on the $form object before we go to edit.
	 *
	 * @param  array  $data  data to filter
	 * @return array
	 */
	public static function view_view_data(array $data) {
		$timezone = $data['model']->preference('timezone');
		if ($timezone === null) {
			$timezone = Oxygen::config('oxygen')->get('timezone', 'America/Denver');
		}

		$timezones = Date::timezones();
		$timezone = OField::factory('select')
			->display('view')
			->model($data['model'])
			->name('timezone')
			->options(Date::timezones())
			->value($timezones[$timezone]);

		$data['form']->field('timezone', $timezone);

		return $data;
	}

	/**
	 * Sets the header view for forms.
	 *
	 * @static
	 * @param  array  $data
	 * @return array
	 */
	public static function header_view_data(array $data) {
		$view = 'form/header/tabs/default';

		$data['form']->view('header', $view)
			->add_css_class('base', true)
			->add_css_class('box-tabs', true);

		$data['form']->header_tabs = $data['controller']->build_tabs('edit');

		return $data;
	}

} // End Controller_Oxygen_Users
