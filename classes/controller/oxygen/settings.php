<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Settings extends Controller_Tabs {

	/**
	 * @var  bool  Favorites enabled
	 */
	public $favorites_enabled = false;

	/**
	 * @var  Model_Setting  object
	 */
	protected $_model = null;

	/**
	 * Since we're extending the CRUD controller through tabs block these actions.
	 *
	 * TODO Tabs shouldn't extend 404, figure out a better way to do this later.
	 *
	 * @throws HTTP_Exception_404
	 */
	public function before() {
		// ... These actions should not be called.
		if (in_array($this->request->action(), array('add', 'edit', 'delete', 'clone', 'api', 'reinstate', 'search', 'grid', 'view'))) {
			$this->error_404();
		}

		// Does the user have proper permissions?
		if (!Auth::instance()->has_permission('manage_system_settings', 'Setting')) {
			throw new Oxygen_Access_Exception;
		}

		parent::before();
	}

	/**
	 * Displays the system settings.
	 *
	 * @param  null  $related  unused
	 */
	public function action_index($related = null) {
		// Load the model
		$this->_model = OModel::factory('Setting');
		$this->_model->fields_init()
			->find_all();

		// Save?
		if (Arr::get($_POST, 'save')) {
			$this->_model->values($_POST, $this->_model->fieldgroup('edit', true));

			// Save
			$this->_model->save();

			// Confirmation
			Msg::add('confirm', __(':model_name successfully saved.', array(
				':model_name' => $this->_model->meta('mult_text')
			)));

			// Filter
			OHooks::instance()->filter(get_class($this).'.edit.model_post_save', array(
				'model' => $this->_model
			));

			// Redirect
			$this->request->redirect('settings');
		}

		// Build the form
		$form = OForm::factory()
			->model($this->_model)
			->title(__('Configuration'))
			->name($this->_model->meta('one').'_edit')
			->fields($this->_model->fieldgroup('edit'))
			->button('save',
				OField::factory('submit')
					->model($this->_model)
					->name('save')
					->default_value(__('Save'))
			)
			->attributes(array('class' => 'edit'));

		// Filter
		$data = OHooks::instance()->filter(get_class($this).'.edit.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form,
		));

		// Set template content
		$this->template->set(array(
			'title' => 'System Settings',
			'content' => View::factory($this->_model->view('edit'), $data)
		));
	}

} // End Controller_Oxygen_Settings
