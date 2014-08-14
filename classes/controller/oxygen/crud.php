<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright  (c) Crowd Favorite. All Rights Reserved.
 * @package    Oxygen
 * @subpackage Controllers
 *
 * @property Request $request
 * @property Session $session
 * @property Breadcrumbs $breadcrumbs
 */
class Controller_Oxygen_CRUD extends Controller_Protected {

	/**
	 * @var  string  model name
	 */
	protected $_model_name = '';

	/**
	 * @var  OModel  object
	 */
	protected $_model = null;

	/**
	 * @var  string  nonce action
	 */
	protected $_nonce_action = '';

	/**
	 * @var  string  fieldgroup
	 */
	protected $_fieldgroup = null;

	/**
	 * Actions to automatically initialize the model.
	 *
	 * @return array
	 */
	protected function init_model_actions() {
		return array(
			'index',
			'add',
			'edit',
			'delete',
			'clone',
			'grid',
			'reinstate',
			'search',
			'view',
		);
	}

	/**
	 * Actions to automatically initiate 404 if not found.
	 *
	 * @return array
	 */
	protected function retrieve_model_actions() {
		return array(
			'view',
			'edit',
			'delete',
			'clone',
			'reinstate',
		);
	}

	/**
	 * Basic CRUD elements.
	 *
	 * @return void
	 */
	public function before() {
		parent::before();

		if ($this->request->action() == 'protected') {
			// Stop processing.
			return;
		}

	}

	/**
	 * Load model for controller.
	 */
	protected function _init_model() {
		// Load the model
		if ($this->_model === null) {
			if (empty($this->_model_name)) {
				$this->_model_name = Inflector::singular($this->request->controller());
			}
			$this->_model = OModel::factory($this->_model_name);
		}

		$data = OHooks::instance()->filter(
			get_class($this).'.init_model.post',
			array(
				'controller' => $this,
				'model' => $this->_model
			)
		);
		$this->_model = $data['model'];
	}

	/**
	 * Load record for CRUD operations.
	 */
	protected function _load_model() {
		$id = $this->request->param('id', 0);
		$model = OModel::factory($this->_model_name);

		if (in_array($this->request->action(), array('reinstate', 'delete', 'view'))) {
			$model->include_obsolete(true);
		}

		$this->_model = $model->where($model->primary_key(), '=', $id)->find();

		if (!$this->_model->loaded()) {
			$this->_unable_to_load_record();
		}
	}

	/**
	 * Create nonce key for action.
	 */
	protected function _create_nonce() {
		if ($this->_model !== null) {
			// Set the nonce
			$this->_nonce_action = $this->_model->meta('one').'_'.$this->request->action();
		}
		else {
			$this->_nonce_action = $this->request->action();
		}
	}

	/**
	 * Default CRUD index.
	 *
	 * @param  object  $related  using a related object of the model?
	 */
	public function action_index($related = null) {
		$permission = OHooks::instance()->filter(
			get_class($this).'.action_index.permission',
			array(
				'key' => 'view',
				'group' => $this->_model_name
			)
		);
		if (!Auth::instance()->has_permission($permission['key'], $permission['group'])) {
			throw new Oxygen_Access_Exception;
		}

		$this->_init_model();

		$list = $this->action_grid($related);

		$this->favorites->title(__(':model_name List', array(
			':model_name' => $this->_model->meta('one_text')
		)));

		// Filter template content
		$data = OHooks::instance()->filter(
			get_class($this).'.index.view_data',
			array(
				'model' => $this->_model,
				'list' => $list
			)
		);

		// Set template content
		$this->template->set(array(
			'title' => $this->_model->meta('mult_text'),
			'content' => View::factory($this->_model->view('list'), $data)
		));
	}

	/**
	 * Builds the OList object.
	 *
	 * @param  object  $related  using a related object of the model?
	 *
	 * @return OList
	 */
	public function action_grid($related = null) {
		$permission = OHooks::instance()->filter(
			get_class($this).'.action_grid.permission',
			array(
				'key' => 'view',
				'group' => $this->_model_name
			)
		);
		if (!Auth::instance()->has_permission($permission['key'], $permission['group'])) {
			throw new Oxygen_Access_Exception;
		}

		$this->_init_model();

		$model = $this->_model;
		if ($related !== null && isset($model->$related)) {
			$model = $model->$related;
		}
		$this->template->bind_global('model', $model);
		$fields = $model->fieldgroup('list');
		if (!$fields) {
			$model->fields_init();
			$model->set_field_values();
			$model->friendly_values();
		}

		// Load the grid
		if (Arr::get($_POST, 'filter-results') != null) {
			// TODO $this->request->post($key) for these?
			$data = array(
				'where' => Arr::get($_POST, 'where', array()),
				'created' => Arr::get($_POST, 'created'),
				'updated' => Arr::get($_POST, 'updated'),
				'range_start' => Arr::get($_POST, 'range_start'),
				'range_end' => Arr::get($_POST, 'range_end')
			);
		}
		else {
			$data = null;
		}
		$grid = $model->grid($this->request->param('sort'), $this->request->param('order'), $data);

		$list = OList::factory()
			->title($model->meta('mult_text'))
			->items($grid['items'])
			->model($model)
			->view($model->list_views())
			->pagination($grid['pagination']);
		$this->_model->cache_related_for_list($list);

		$list = OHooks::instance()->filter(get_class($this).'.grid.list', $list);
		if ($this->request->is_ajax()) {
			$this->template->response = array(
				'result' => 'success',
				'html' => (string) $list
			);
		}

		return $list;
	}

	/**
	 * View an object.
	 *
	 * @return void
	 */
	public function action_view() {
		$permission = OHooks::instance()->filter(
			get_class($this).'.action_view.permission',
			array(
				'key' => 'view',
				'group' => $this->_model_name
			)
		);
		if (!Auth::instance()->has_permission($permission['key'], $permission['group'])) {
			throw new Oxygen_Access_Exception;
		}

		$this->_init_model();
		$this->_load_model();
		$this->_model->fields_init();
		$form = OForm::factory()
			->model($this->_model)
			->name($this->_model->meta('one').'_view')
			->title($this->_model->meta('one_text'))
			->fields($this->_model->fieldgroup('view'), 'view')
			->actions(array('delete'))
			->view('shell', 'form/shell/view')
			->add_css_class('view');

		if ($this->_model->is_obsolete()) {
			Msg::add('info',
				__('This :model has been deleted. :reinstate', array(
					':model' => $this->_model->meta('one_text'),
					':reinstate' => $this->_model->link('reinstate')
				)),
				'trash'
			);
		}
		else {
			$form->action($this->_model->url('view'));
		}

		$this->_model->set_field_values();
		$this->_model->friendly_values();

		// Favorite
		$this->favorites->title($this->_model->name());

		// Breadcrumbs
		$this->breadcrumbs->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		));

		// Filter the model and form
		$data = OHooks::instance()->filter(get_class($this).'.view.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form
		));

		// Set template content
		$this->template->set(array(
			'title' => $this->_model->name(),
			'content' => View::factory($this->_model->view('view'), $data)
		));
	}

	/**
	 * Underlying controller code to create an object.
	 *
	 * Any errors here will throw an exception which should be caught
	 * by the calling method.
	 *
	 * @return void
	 */
	protected function _add($data = array()) {
		$this->_model->fields_init();
		if (empty($data)) {
			$data = $_POST;
		}
		$this->_model->values_via_fieldgroup($data, 'add');

		// Hooks
		$this->_model = OHooks::instance()->filter(
			get_class($this).'.add.model_pre_save',
			$this->_model
		);

		// Save
		$this->_model->create();
		Msg::add('confirm', __(':model successfully created.', array(
			':model' => $this->_model->meta('one_text')
		)));

		// Hooks
		$this->_model = OHooks::instance()->filter(
			get_class($this).'.add.model_post_save',
			$this->_model
		);
		OHooks::instance()->event(get_class($this).'.add.model_saved', $this->_model);
	}

	/**
	 * Prep the form.
	 *
	 * @return void
	 */
	protected function _add_form() {
		// Create the form
		$this->_model->fields_init();
		$this->_model->set_field_values();
		$fields = $this->_model->fieldgroup('add');
		$fields += array(
			'nonce' => Nonce::field($this->_nonce_action)
		);
		$fields = OHooks::instance()->filter(
			get_class($this).'_add_form.fields',
			$fields
		);
		return OForm::factory()
			->model($this->_model)
			->title('New '.$this->_model->meta('one_text'))
			->name($this->_model->meta('one').'_add')
			->fields($fields)
			->button('save',
				OField::factory('submit')
					->model($this->_model)
					->name('save')
					->default_value(__('Save'))
			)
			->attributes(array('class' => 'edit'));
	}

	/**
	 * Create an object and redirect (full page load).
	 *
	 * @return void
	 */
	public function action_add() {
		$permission = OHooks::instance()->filter(
			get_class($this).'.action_add.permission',
			array(
				'key' => 'add',
				'group' => $this->_model_name
			)
		);
		if (!Auth::instance()->has_permission($permission['key'], $permission['group'])) {
			throw new Oxygen_Access_Exception;
		}

		$this->_init_model();
		$this->_create_nonce();

		if (Arr::get($_POST, 'save')) {
			Nonce::check_fatal($this->_nonce_action);
			try {
				$this->_add();
				$this->request->redirect($this->_model->destination('add'));
			}
			catch (ORM_Validation_Exception $e) {
				Msg::add('error', $e->errors('validation'));
			}
			catch (Exception $e) {
				Msg::add('error', $e->getMessage());
			}
		}

		$form = $this->_add_form();
		$form->add_css_class('base', true);

		// Favorite
		$this->favorites->title('New '.$this->_model->meta('one_text'));

		// Breadcrumbs
		$this->breadcrumbs->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		));

		// Set template content
		$data = OHooks::instance()->filter(get_class($this).'.add.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form,
			'title' => __('New :name', array(
				':name' => $this->_model->meta('one_text')
			))
		));

		$this->template->set(array(
			'title' => $data['title'],
			'content' => View::factory($this->_model->view('add'), $data)
		));
	}

	/**
	 * Underlying controller code to update an object.
	 *
	 * Any errors here will throw an exception which should be caught
	 * by the calling method.
	 *
	 * @return void
	 */
	protected function _edit($data = array()) {
		$this->_model->fields_init();
		if (empty($data)) {
			$data = $_POST;
		}
		$this->_model->values_via_fieldgroup($data, 'edit');

		// Hooks
		$this->_model = OHooks::instance()->filter(
			get_class($this).'.edit.model_pre_save',
			$this->_model
		);

		// Save
		$this->_model->update();
		Msg::add('confirm', __(':model successfully edited.', array(
			':model' => $this->_model->meta('one_text')
		)));

		// Hooks
		$this->_model = OHooks::instance()->filter(
			get_class($this).'.edit.model_post_save',
			$this->_model
		);
		OHooks::instance()->event(get_class($this).'.edit.model_saved', $this->_model);
	}

	/**
	 * Prep the form.
	 *
	 * @return void
	 */
	protected function _edit_form() {
		// Initialize the form
		$fields = $this->_model->fieldgroup('edit');
		$fields += array(
			'nonce' => Nonce::field($this->_nonce_action, $this->_model->id)
		);
		if (!empty($this->_model->_updated_column)) {
			$fields += array(
				'updated' => OField::factory('hidden')
					->model($this->_model)
					->name('updated')
					->value($this->_model->updated)
			);;
		}
		return OForm::factory()
			->model($this->_model)
			->title('Edit '.$this->_model->meta('one_text'))
			->name($this->_model->meta('one').'_edit')
			->fields($fields)
			->button('save',
				OField::factory('submit')
					->model($this->_model)
					->name('save')
					->default_value('Save')
			)
			->attributes(array('class' => 'edit'));
	}

	/**
	 * Update an object.
	 *
	 * @return void
	 */
	public function action_edit() {
		$permission = OHooks::instance()->filter(
			get_class($this).'.action_edit.permission',
			array(
				'key' => 'edit',
				'group' => $this->_model_name
			)
		);
		if (!Auth::instance()->has_permission($permission['key'], $permission['group'])) {
			throw new Oxygen_Access_Exception;
		}

		$this->_init_model();
		$this->_load_model();
		$this->_create_nonce();

		if ($this->_model->is_obsolete()) {
			$this->request->redirect($this->_model->url('view'));
		}

		// User submit the form?
		if (Arr::get($_POST, 'save')) {
			Nonce::check_fatal($this->_nonce_action, $this->_model->id);
			try {
				// Collision detection
				if (($data = $this->_model->no_collision($this->_model->id, $this->_nonce_action)) !== true) {
					$this->template->set(array(
						'title' => 'Please Review',
						'content' => View::factory('models/collision', $data)
					));

					return;
				}

				$this->_edit();
				$this->request->redirect($this->_model->destination('edit'));
			}
			catch (ORM_Validation_Exception $e) {
				Msg::add('error', $e->errors('validation'));
			}
			// All errors should be caught and appropriate JSON object return if the request is AJAX
			catch (Exception $e) {
				Msg::add('error', $e->getMessage());
			}
		}

		$this->_model->fields_init();
		$this->_model->set_field_values();

		$form = $this->_edit_form();
		$form->actions(array('delete'))
			->add_css_class('base', true);

		// Favorite
		$this->favorites->title($this->_model->name().' (edit)');

		// Breadcrumbs
		$this->breadcrumbs->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		));
		$this->breadcrumbs->title_encoded = true;

		// Set template content based on the request
		$data = OHooks::instance()->filter(get_class($this).'.edit.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form,
			'title' => __('Edit: :model_link', array(
				':model_link' => $this->_model->link('view')
			))
		));

		// Set template content
		$this->template->set(array(
			'title' => $data['title'],
			'content' => View::factory($this->_model->view('edit'), $data)
		));
	}

	/**
	 * Clones an object.
	 *
	 * @return void
	 */
	public function action_clone() {
		$permission = OHooks::instance()->filter(
			get_class($this).'.action_clone.permission',
			array(
				'key' => 'add',
				'group' => $this->_model_name
			)
		);
		if (!Auth::instance()->has_permission($permission['key'], $permission['group'])) {
			throw new Oxygen_Access_Exception;
		}

		if (!$this->_model->cloneable()) {
			if ($this->request->is_ajax()) {
				$this->template->response = array(
					'result' => 'error',
					'html' => 'This object is not cloneable.'
				);
			}
			else {
				Msg::add('error', 'Object is not cloneable.');
				$this->request->redirect($this->_model->url('list'));
			}
		}

		$this->_model->fields_init();
		$this->_model->set_field_values();

		// User submit the form?
		if (Arr::get($_POST, 'save')) {
			try {
				// Validation
				Nonce::check_fatal($this->_nonce_action, $this->_model->id);

				// Reset the object
				$related = array();
				foreach (Arr::merge($this->_model->belongs_to(), $this->_model->has_one()) as $key => $data) {
					$related[$key] = $data['foreign_key'];
				}
				$this->_model->clear()->values($_POST, $this->_model->fieldgroup('edit', true));

				// Hooks
				$this->_model = OHooks::instance()->filter(get_class($this).'.clone.model_pre_save', $this->_model);

				// Save the data
				$this->_model->create();

				// Hooks
				$this->_model = OHooks::instance()->filter(get_class($this).'.clone.model_post_save', $this->_model);
				OHooks::instance()->event(get_class($this).'.clone.model_saved', $this->_model);

				// AJAX?
				$success = $this->_model->meta('one_text').' successfully cloned.';
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

				// All done!
				Msg::add('confirm', $success);
				$this->request->redirect($this->_model->destination('clone'));
			}
			catch (ORM_Validation_Exception $e) {
				Msg::add('error', $e->errors('validation'));
			}
			// All errors should be caught and appropriate JSON object return if the request is AJAX
			catch (Exception $e) {
				Msg::add('error', $e->getMessage());
			}
			// All errors should be caught and appropriate JSON object return if the request is AJAX
			catch (Exception $e) {
				if ($this->request->is_ajax()) {
					$this->template->response = array(
						'result' => 'error',
						'msgs' => array(
							array(
								'type' => 'error',
								'text' => $e->getMessage(),
							)
						)
					);
					return;
				}
			}
		}

		// Initialize the form
		$fields = $this->_model->fieldgroup('edit');
		$fields += array(
			'update' => OField::factory('hidden')
				->model($this->_model)
				->name('updated')
				->value($this->_model->updated),

			'nonce' => Nonce::field($this->_nonce_action, $this->_model->id)
		);
		$form = OForm::factory()
			->model($this->_model)
			->title('Clone '.$this->_model->meta('one_text'))
			->name($this->_model->meta('one').'_clone')
			->fields($fields)
			->button('save',
				OField::factory('submit')
					->model($this->_model)
					->name('save')
					->default_value('Save')
			)
			->attributes(array('class' => 'edit'));

		// Favorite
		$this->favorites->title($this->_model->name().' (clone)');

		// Breadcrumbs
		$this->breadcrumbs->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		));

		// Set template content based on the request
		$data = OHooks::instance()->filter(get_class($this).'.clone.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form
		));

		// Set template content
		$this->template->set(array(
			'title' => 'Clone: '.$this->_model->name(),
			'content' => View::factory($this->_model->view('clone'), $data)
		));
	}

	/**
	 * Obsolete an object.
	 *
	 * @return void
	 */
	public function action_delete() {
		$permission = OHooks::instance()->filter(
			get_class($this).'.action_delete.permission',
			array(
				'key' => 'delete',
				'group' => $this->_model_name
			)
		);
		if (!Auth::instance()->has_permission($permission['key'], $permission['group'])) {
			throw new Oxygen_Access_Exception;
		}

		$this->_init_model();
		$this->_load_model();
		$this->_create_nonce();

		if ($this->_model->is_obsolete()) {
			$this->request->redirect($this->_model->url('list'));
		}

		if (Arr::get($_POST, 'delete')) {
			try {
				// Validation
				Nonce::check_fatal($this->_nonce_action, $this->_model->id);

				// Obsolete the record
				$this->_model->fields_init();
				$this->_model->values($_POST, $this->_model->fieldgroup('delete', true));

				// Hooks
				$this->_model = OHooks::instance()->filter(
					get_class($this).'.delete.model_pre_save',
					$this->_model
				);

				OHooks::instance()->event(
					get_class($this).'.delete.pre_save',
					$this->_model
				);

				// model data won't be around after save, grab this now
				$destination = $this->_model->destination('delete');

				$this->_model->obsolete();

				// Hooks
				$this->_model = OHooks::instance()->filter(
					get_class($this).'.delete.model_post_save',
					$this->_model
				);
				OHooks::instance()->event(
					get_class($this).'.delete.model_saved',
					$this->_model
				);

				// Set confirmation message
				Msg::add('confirm', __(':model successfully deleted. :reinstate', array(
					':model' => $this->_model->meta('one_text'),
					':reinstate' => $this->_model->link('reinstate')
				)));

				// All done!
				$this->request->redirect($destination);
			}
			catch (ORM_Validation_Exception $e) {
				Msg::add('error', $e->errors('validation'));
			}
			// All errors should be caught and appropriate JSON object return if the request is AJAX
			catch (Exception $e) {
				Msg::add('error', $e->getMessage());
			}
		}

		// Fieldset
		$fields = array(
			'nonce' => Nonce::field($this->_nonce_action, $this->_model->id),
		);
		$legend = __('Are you sure you want to delete :model: :name?', array(
			':model' => $this->_model->meta('one_text'),
			':name' => $this->_model->name(),
		));

		// Setup the form
		$delete = OFieldset::factory()
			->model($this->_model)
			->legend($legend)
			->fields($fields);

		$cancel = OField::factory('submit')
			->model($this->_model)
			->name('cancel')
			->default_value(__('Cancel'))
			->attributes(array(
				'onclick' => 'history.go(-1); return false;'
			));

		$delete_button = OField::factory('submit')
			->model($this->_model)
			->name('delete')
			->default_value(__('Yes, Delete'));
		$form = OForm::factory()
			->name('delete_'.$this->_model->meta('one'))
			->fieldset('delete', $delete)
			->button('delete', $delete_button)
			->button('cancel', $cancel)
			->attributes(array(
				'class' => 'edit delete'
			));

		// Breadcrumbs
		$this->breadcrumbs->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		));
		$this->breadcrumbs->add(Bookmark::factory(
			$this->_model->url('view'),
			$this->_model->name()
		));

		// Set template data
		$data = OHooks::instance()->filter(get_class($this).'.delete.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form
		));
		OHooks::instance()->event(
			get_class($this).'.delete.pre_view',
			$this->_model
		);
		$this->template->set(array(
			'title' => __('Delete :model?', array(':model' => $this->_model->meta('one_text'))),
			'content' => View::factory($this->_model->view('delete'), $data)
		));
	}

	/**
	 * Reinstate an object.
	 *
	 * @return void
	 */
	public function action_reinstate() {
		$permission = OHooks::instance()->filter(
			get_class($this).'.action_reinstate.permission',
			array(
				'key' => 'delete',
				'group' => $this->_model_name
			)
		);
		if (!Auth::instance()->has_permission($permission['key'], $permission['group'])) {
			throw new Oxygen_Access_Exception;
		}

		$this->_init_model();
		$this->_load_model();
		$this->_create_nonce();

		if (Arr::get($_POST, 'reinstate')) {
			if (Nonce::check_fatal($this->_nonce_action, $this->_model->id)) {
				// Reinstate
				$this->_model->fields_init();
				$this->_model->values($_POST, $this->_model->fieldgroup('reinstate', true));
				$this->_model->reinstate();

				// Hooks
				$this->_model = OHooks::instance()->filter(get_class($this).'.reinstate.model_post_save', $this->_model);
				OHooks::instance()->event(get_class($this).'.reinstate.model_saved', $this->_model);

				// Set confirmation
				Msg::add('confirm', $this->_model->meta('one_text').' '.$this->_model->name().' successfully reinstated.');

				// All done!
				$this->request->redirect($this->_model->destination('reinstate'));
			}
		}

		// Fieldset
		$fields = array(
			'nonce' => Nonce::field($this->_nonce_action, $this->_model->id),
		);
		$legend = 'Are you sure you want to reinstate '.$this->_model->meta('one_text').': '.$this->_model->name().'?';

		// Setup the form
		$reinstate = OFieldset::factory()
			->model($this->_model)
			->legend($legend)
			->fields($fields);

		$reinstate_button = OField::factory('submit')
			->model($this->_model)
			->name('reinstate')
			->default_value('Yes, Reinstate');

		$cancel = OField::factory('submit')
			->model($this->_model)
			->name('cancel')
			->default_value('Cancel')
			->attributes(array(
				'onclick' => 'history.go(-1); return false;'
			));

		$form = OForm::factory()
			->name('reinstate_'.$this->_model->meta('one'))
			->fields($fields)
			->fieldset('reinstate', $reinstate)
			->button('reinstate', $reinstate_button)
			->button('cancel', $cancel);

		// Breadcrumbs
		$this->breadcrumbs->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		));
		$this->breadcrumbs->add(Bookmark::factory(
			$this->_model->url('view'),
			$this->_model->name()
		));

		// Set template data
		$data = OHooks::instance()->filter(get_class($this).'.reinstate.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form,
		));
		$this->template->set(array(
			'title' => 'Reinstate '.$this->_model->meta('one_text').'?',
			'content' => View::factory($this->_model->view('reinstate'), $data)
		));
	}

	/**
	 * Searches the current model.
	 *
	 * @return void
	 */
	public function action_search() {
		$permission = OHooks::instance()->filter(
			get_class($this).'.action_search.permission',
			array(
				'key' => 'view',
				'group' => $this->_model_name
			)
		);
		if (!Auth::instance()->has_permission($permission['key'], $permission['group'])) {
			throw new Oxygen_Access_Exception;
		}

		$key = $this->request->param('key');

		$this->_init_model();

		// Setup the fields
		$this->_model->fields_init();
		$fields = $this->_model->fieldgroup('search');
		$fields += array(
			'terms' => OField::factory()
				->model($this->_model)
				->name('terms')
				->label('')
				->default_value(Arr::get($_POST, 'terms', ''))
				->attributes(array('placeholder' => __('Search'))),

			'items_per_page' => OField::factory()
				->model($this->_model)
				->name('items_per_page')
				->default_value(Arr::get($_POST, 'items_per_page', Oxygen::config('oxygen')->preference('search_items_per_page'))),

			'sort' => OField::factory('select')
				->model($this->_model)
				->name('sort')
				->options($this->_model->sortable_columns()),

			'sort_order' => OField::factory('radio')
				->model($this->_model)
				->name('sort_order')
				->label('')->options(array('asc' => 'A-Z', 'desc' => 'Z-A'))
				->default_value('asc'),
		);

		if ($this->_model->enabled_column() !== null && isset($fields['enabled'])) {
			$fields['enabled'] = $this->_model->field('enabled')
				->options(array('no' => 'No', 'yes' => 'Yes'))
				->default_value('yes');
		}

		if ($this->_model->created_column() !== null && isset($fields['created'])) {
			$fields['created'] = $this->_model->field('created')
				->default_value('')
				->display_type('range')
				->date_format('Y-m-d')
				->time_format('h:i:s');
		}

		if ($this->_model->updated_column() !== null && isset($fields['updated'])) {
			$fields['updated'] = $this->_model->field('updated')
				->default_value('')
				->display_type('range')
				->date_format('Y-m-d')
				->time_format('h:i:s');
		}

		// Set the values
		$this->_model->set_field_values(false, $fields);

		// Process the search
		$list = '';
		if (Arr::get($_POST, 'search')) {
			$key = $this->_model->search_fields($fields);
			$this->request->redirect($this->_model->url('search').'/'.$key);
		}
		else if ($key !== null) {
			$fields = $this->_model->search_fields($fields, $key);
			if ($fields === false) {
				$this->request->redirect($this->_model->url('search'));
			}
			$search = $this->_model->search($fields);
			$list = OList::factory('search/list')
				->view('row', 'search/list/row')
				->view('row_empty', 'search/list/empty')
				->model($this->_model)
				->items($search['results'])
				->pagination($search['pagination']);

			if ($this->request->is_ajax()) {
				$this->template->response = array(
					'result' => 'success',
					'html' => (string) $list
				);
			}
		}

		// Favorite
		$title = __('Search :model', array(
			':model' => $this->_model->meta('mult_text')
		));
		$this->favorites->title($title);

		// Breadcrumbs
		$this->breadcrumbs->add(Bookmark::factory(
			$this->_model->url('list'),
			$this->_model->meta('mult_text')
		));

		// Setup the form
		$form = OForm::factory()
			->name('search_'.$this->_model->meta('one'))
			->title($title)
			->model($this->_model)
			->fields($fields, 'search')
			->button('search',
				OField::factory('submit')
					->model($this->_model)
					->name('search')
					->default_value('Search')
			);

		// Set template data
		$data = OHooks::instance()->filter(get_class($this).'.search.view_data', array(
			'controller' => $this,
			'model' => $this->_model,
			'form' => $form,
			'list' => $list
		));

		$this->template->set(array(
			'title' => $title,
			'content' => View::factory('models/search', $data)
		));
	}

	/**
	 * API Access for CRUD operations.
	 *
	 * @return void
	 */
	public function action_api() {
		// API Key set?
		if (!$this->session->get('api')) {
			$this->template->response = array(
				'result' => 'error'
			);
			return;
		}

		$get = Arr::get($_GET, 'action');
		if ($get !== null) {
			if (!Auth::instance()->has_permission('view', get_class($this->_model))) {
				$this->template->response = array(
					'result' => 'error'
				);
				return;
			}

			if ($get == 'search') {
				// TODO Implement API Search
			}
			else {
				$this->_model->find(Arr::get($_GET, 'id', 0));
				if ($this->_model->loaded()) {
					$this->template->response = array(
						'result' => 'success',
						get_class($this->_model) => $this->_model->api_output()
					);
				}
				else {
					$this->template->response = array(
						'result' => 'error'
					);
				}
			}

			// All done, c'ya!
			return;
		}

		$post = Arr::get($_POST, 'action');
		if ($post !== null) {
			if (!Auth::instance()->has_permission($post, get_class($this->_model))) {
				$this->template->response = array(
					'result' => 'error'
				);
				return;
			}

			if ($post == 'add' || $post == 'edit') {
				$this->_model->values($_POST);
				if (($post == 'add' && $this->_model->create()) || ($post == 'edit' && $this->_model->update())) {
					$this->template->response = array(
						'result' => 'success',
						get_class($this->_model) => $this->_model->api_output()
					);
				}
				else {
					$this->template->response = array(
						'result' => 'error'
					);
				}
			}
			else if ($post == 'delete') {
				$this->_model->values($_POST);
				if ($this->_model->obsolete()) {
					$this->template->response = array(
						'result' => 'success',
						'id' => $this->_model->id
					);
				}
				else {
					$this->template->response = array(
						'result' => 'error'
					);
				}
			}
		}
	}

	/**
	 * Sets the favorite group to the model's mult_text meta if a custom
	 * group has not been defined by this point.
	 *
	 * @return void
	 */
	public function after() {
		if ($this->_model !== null) {
			if ($this->favorites_enabled && $this->favorites->group() == '*') {
				$this->favorites->group($this->_model->meta('mult_text'));
			}
		}

		parent::after();
	}

	/**
	 * Call this when we're unable to load a record.
	 *
	 * @return void
	 */
	protected function _unable_to_load_record() {
		$this->error_404();
	}

	/**
	 * Returns the fieldgroup to be used for CRUD operations.
	 *
	 * @return string
	 */
	protected function fieldgroup() {
		if ($this->_fieldgroup === null) {
			return $this->request->action();
		}

		return $this->_fieldgroup;
	}

} // End Controller_Oxygen_CRUD
