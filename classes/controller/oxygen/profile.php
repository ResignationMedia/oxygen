<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Profile extends Controller_Users {

	/**
	 * @var  array  profile tabs
	 */
	public $_tabs = array(
		'edit' => array(
			'profile' => 'Profile',
			'profile/photo' => 'Photo',
			'profile/password' => 'Password',
			'profile/api' => 'API Access'
		),
	);

	/**
	 * Does profile-specific things.
	 *
	 * @return void
	 */
	public function _load_model() {
		$user = Auth::instance()->get_user();
		if ($user !== false) {
			$this->_model = OModel::factory($this->_model_name, $user->id);
			if (!$this->_model->loaded()) {
				$this->request->redirect($this->_model->meta('mult'));
			}
		}
	}

// TODO - Breadcrujmb customization
/*
		// Delete the "Users" breadcrumb.
		$this->breadcrumbs->delete($this->_model->meta('mult_text'))
			->add(Bookmark::factory('profile', 'Profile'));
*/


	/**
	 * Sends the form to the correct location.
	 *
	 * @param  int     $id  TODO: This needs to be removed when Kohana deprecates request params in the args.
	 * @param  string  $redirect_target
	 * @return void
	 */
	public function action_password($unused = null) {
		if (!Auth::instance()->has_permission('update_profile', $this->_model_name)) {
			throw new Oxygen_Access_Exception;
		}

		OHooks::instance()
			->add(get_class($this).'.password.model_post_save', array(
				get_class($this),
				'password_model_post_save'
			));

		parent::action_password(true);

		$title = __('Change Password');
		$this->template->title = $title;
		$this->favorites->title($title);
		$this->breadcrumbs->delete('Edit '.$this->_model->meta('one_text'));
	}

	/**
	 * Changes the favorite's title for the API page.
	 *
	 * @return void
	 */
	public function action_api($unused = null) {
		if (!Auth::instance()->has_permission('update_profile', $this->_model_name)) {
			throw new Oxygen_Access_Exception;
		}

		parent::action_api(true);

		$this->favorites->title('API Access');
 		$this->breadcrumbs->delete('Edit '.$this->_model->meta('one_text'));
	}

	/**
	 * Displays the user's profile.
	 */
	public function action_profile() {
		if (!Auth::instance()->has_permission('update_profile', $this->_model_name)) {
			throw new Oxygen_Access_Exception;
		}
		$this->_init_model();
		$this->_load_model();
		$this->_create_nonce();

		// Hooks
		OHooks::instance()
			->add(get_class($this).'.profile.view_data', array(
				get_class($this),
				'profile_view_data'
			))
			->add(get_class($this).'.profile.model_post_save', array(
				get_class($this),
				'profile_model_post_save'
			))
			->add(get_class($this).'.profile.model_saved', array(
				get_class($this),
				'profile_model_saved'
			))
			->add('profile_edit.form.header_data', array(
				get_class($this),
				'user_edit_form_header_data',
			));

		// Form posted?
		if (Arr::get($_POST, 'save')) {
			try {
				// Validation
				Nonce::check_fatal($this->_nonce_action, $this->_model->id);
				$this->_model->check();

				// Check collision
				if (!$this->_model->no_collision($this->_model->id, $this->_nonce_action)) {
					$this->_model->validation()->error('username', 'collision');
					throw new ORM_Validation_Exception($this->_model->object_name, $this->_model->validation());
				}

				// Save the profile
				$this->_model->values_via_fieldgroup($_POST, 'profile');
				$this->_model->update();

				foreach ($this->_model->fieldgroup('preferences') as $key => $field) {
					$value = Arr::get($_POST, $key);
					if (!is_null($value)) {
						$this->_model->preference($key, $value);
					}
				}

				// Set the confirmation message
				Msg::add('confirm', 'Profile successfully updated.');

				// Hooks
				$this->_model = OHooks::instance()->filter(get_class($this).'.profile.model_post_save', $this->_model);
				OHooks::instance()->event(get_class($this).'.profile.model_saved', $this->_model);

				// All done!
				$this->request->redirect('profile');
			}
			catch (ORM_Validation_Exception $e) {
				Msg::add('error', $e->errors('validation'));
			}
		}

		// Add additional fields
		$this->_model->fields_init();
		$this->_model->set_field_values();
		$fields = array(
			OField::factory('hidden')
				->model($this->_model)
				->name('updated')
				->value($this->_model->updated),

			Nonce::field($this->_nonce_action, $this->_model->id),
			$this->_model->field('name'),
			$this->_model->field('email'),
		);

		// Initialize the form
		$form = OForm::factory()
			->title('Edit Profile')
			->name('profile_edit')
			->fields($fields)
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
			->add_css_class('box-tabs', true);
		// add preferences
		foreach ($this->_model->fieldgroup('preferences') as $key => $field) {
			$value = $this->_model->preference($key);
			if (is_null($value)) {
				$value = Oxygen::config('oxygen')->get($key, null);
			}
			$form->field($key, $field->value($value));
		}

		// Favorite
		$title = 'User Profile';
		$this->favorites->title($title);

		// Get the hook to modify data.
		$data = OHooks::instance()->filter(get_class($this).'.profile.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form
		));

		// Set template content
		$this->template->set(array(
			'title' => $title,
			'content' => View::factory('user/profile', array(
				'model' => $data['model'],
				'form' => $data['form']
			))
		));
	}

	/**
	 * Profile Edit
	 */
	public function action_photo() {
		if (!Auth::instance()->has_permission('update_profile', $this->_model_name)) {
			throw new Oxygen_Access_Exception;
		}
		$this->_init_model();
		$this->_load_model();
		$this->_create_nonce();

		// Hooks
		OHooks::instance()
			->add(get_class($this).'.photo.view_data', array(
				get_class($this),
				'header_view_data'
			))
			->add('profile_photo.form.header_data', array(
				get_class($this),
				'user_edit_form_header_data',
			));

		// Form posted?
		if (Arr::get($_POST, 'save') && Nonce::check_fatal($this->_nonce_action, $this->_model->id)) {
			// Uploading files?
			$uploaded = false;
			$photo_type = Arr::get($_POST, 'photo_type');
			if ($_FILES['profile_photo']['size'] && $photo_type == 'upload') {
				try {
					// Validation
					$validation = Validation::factory($_FILES)
						->rules('profile_photo', array(
							array('Upload::valid'),
							array('Upload::not_empty'),
							array('Upload::type', array(':value', Oxygen::config('oxygen.user_photo.types'))),
							array('Upload::size', array(':value', '2M'))
						));
					$this->_model->check($validation);

					// Alter the image
					$configs = OHooks::instance()->filter(
						'user_photo_sizes_config',
						Oxygen::config('oxygen.user_photo.sizes')
					);
					$temp = Upload::save($_FILES['profile_photo'], null, Media::directory());
					foreach ($configs as $size => $config) {
						// Build the file path
						$config = Arr::merge($config, array(
							'directory' => 'user/profile',
							'ext' => 'jpg',
							'args' => array(
								$this->_model->id,
								$this->_model->username,
								strtolower($size)
							)
						));
						Media::factory($temp, $config)->save();
					}
					unlink($temp);

					// Run the hook, if there is one.
					OHooks::instance()->event('process_profile_photo_upload', array());

					// Uploaded!
					$uploaded = true;
				}
				catch (ORM_Validation_Exception $e) {
					Msg::add('error', $e->errors('validation'));
				}
			}

			// Set the photo type
			if ($uploaded || ($photo_type && $this->_model->photo_type != $photo_type)) {
				if ($photo_type == 'upload' && !$this->_model->has_uploaded_photo()) {
					Msg::add('error', array('profile_photo' => 'Please select a file to upload.'));
				}
				else if ($photo_type == 'gravatar' || $this->_model->has_uploaded_photo()) {

					$this->_model->activity_type('photo');
					$this->_model->photo_type = $photo_type;
					$this->_model->update();

					// All done, redirect!
					Msg::add('confirm', 'Your profile photo has been set.');
					$this->request->redirect('profile/photo');
				}
			}
		}

		// Setup the form.
		$nonce = Nonce::field($this->_nonce_action, $this->_model->id);
		$form = OForm::factory()->title('Edit Profile Photo')
			->name('profile_photo')
			->field($nonce->name(), $nonce)
			->content('action_photo_content', View::factory('user/profile/photo_content', array(
				'model' => $this->_model
			)))
			->button('save',
				OField::factory('submit')
					->model($this->_model)
					->name('save')
					->default_value('Save')
			)
			->attributes(array(
				'class' => 'edit frm',
				'enctype' => 'multipart/form-data'
			))
			->view('header', 'form/header/tabs/default')
			->add_css_class('base', true)
			->add_css_class('box-tabs', true);

		// Favorite
		$title = 'Change Photo';
		$this->favorites->title($title);

		// Set template Content
		$data = OHooks::instance()->filter(get_class($this).'.photo.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form,
		));
		$this->template->set(array(
			'title' => $title,
			'content' => View::factory('user/profile/photo', array(
				'model' => $data['model'],
				'form' => $data['form']
			))
		));
	}

	/**
	 * Actions to automatically initialize the model.
	 *
	 * @return array
	 */
	protected function init_model_actions() {
		return Arr::merge(parent::init_model_actions(), array(
			'photo',
		));
	}

	/**
	 * Override the destination on save.
	 *
	 * @param  object User  $model User model
	 * @return array
	 */
	public static function password_model_post_save($model) {
		$model->destination('edit', 'profile');
		return $model;
	}

	/**
	 * A quick change to some data on the $form object before we go to edit.
	 *
	 * @param  array  $data  data to filter
	 * @return array
	 */
	public static function password_view_data(array $data) {
		$data = self::header_view_data($data);
		$data['form']->content('action_password_instructions', 'Enter a new password in both of the fields below to change your password.', 'instructions');
		return $data;
	}

	/**
	 * A quick change to some data on the $form object before we go to edit.
	 *
	 * @param  array  $data  data to filter
	 * @return array
	 */
	public static function profile_view_data(array $data) {
		$data = self::header_view_data($data);
		$timezone = OField::factory('select')
			->model($data['model'])
			->name('timezone')
			->options(Date::timezones())
			->default_value($data['model']->preference('timezone', Oxygen::config('oxygen')->get('timezone', 'America/Denver')));

		$data['form']->field('timezone', $timezone);

		return $data;
	}

	/**
	 * Updates the user's timezone.
	 *
	 * @static
	 * @param  Model_User $model
	 */
	public static function profile_model_saved($model) {
		$model->preference('timezone', Arr::get($_POST, 'timezone'));
	}

	/**
	 * @return array
	 */
	protected function public_uris() {
		return array(
			'profile'
		);
	}

} // End Controller_Oxygen_Profile
