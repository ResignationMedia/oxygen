<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright   (c) Crowd Favorite. All Rights Reserved.
 * @package     Oxygen
 * @subpackage  Controllers
 *
 * @property Request $request
 * @property View $template
 */
class Controller_Oxygen_Popover extends Controller_CRUD {

	/**
	 * Actions to automatically initialize the model.
	 *
	 * @return array
	 */
	protected function init_model_actions() {
		return Arr::merge(parent::init_model_actions(), array(
			'popover_add',
		));
	}

	/**
	 * Sets the model name.
	 *
	 * @return void
	 */
	public function before() {
		$this->_model_name = $this->request->param('model');
		parent::before();
	}

	/**
	 * Popover add functionality.
	 *
	 * @return void
	 */
	public function action_add() {
		$this->_fieldgroup = 'popover_add';

		if (Arr::get($_POST, 'save')) {
			parent::action_add();
			return;
		}

		$this->_init_model();
		$this->_create_nonce();

		$this->_model->fields_init();
		$this->_model->set_field_values();
		$fields = $this->_model->fieldgroup('popover_add');
		$fields['nonce'] = Nonce::field($this->_nonce_action);
		$form = OForm::factory()
			->model($this->_model)
			->name($this->_model->meta('one').'_add')
			->fields($fields)
			->button('save',
				OField::factory('submit')
					->model($this->_model)
					->name('save')
					->default_value('Save')
			)
			->attributes(array('class' => 'edit'));

		// Set template content
		$data = OHooks::instance()->filter(
			get_class($this).'.popover_add.view_data',
			array(
				'model' => $this->_model,
				'form' => $form
			)
		);

		$this->template->set(array(
			'response' => array(
				'result' => 'success',
				'html' => View::factory($this->_model->view('popover_add'), $data)->render()
			)
		));
	}

} // End Controller_Oxygen_Popover
