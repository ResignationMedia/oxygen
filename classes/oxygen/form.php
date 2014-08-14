<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form extends Oxygen_HTML_Element {

	/**
	 * @var  array  default form views
	 */
	protected $_views = array(
		'shell' => 'form/shell',
		'header' => 'form/header',
		'fieldset' => 'form/fieldset',
		'footer' => 'form/footer',
	);

	/**
	 * @var  string  form action
	 */
	protected $_action = null;

	/**
	 * @var  OModel  model object
	 */
	protected $_model = null;

	/**
	 * @var  string  title
	 */
	protected $_title = '';

	/**
	 * @var  array  form actions
	 */
	protected $_actions = array();

	/**
	 * @var  array  field objects
	 */
	protected $_fields = array();

	/**
	 * @var  array  fieldsets
	 */
	protected $_fieldsets = array();

	/**
	 * @var  array  button objects
	 */
	protected $_buttons = array();

	/**
	 * @var  array  content
	 */
	protected $_content = array();

	/**
	 * Returns a new OForm object. If you do not define the "file" parameter,
	 * you the default shell will be used. (views/form/shell)
	 *
	 *	 $form = OForm::factory($file, $data);
	 *
	 * @static
	 *
	 * @param  string  $file  view filename
	 * @param  array   $data  array of values for the view
	 *
	 * @return OForm
	 */
	public static function factory($file = null, array $data = null) {
		return new OForm($file, $data);
	}

	/**
	 * Sets the form action.
	 *
	 * @param  string  $action  form action
	 *
	 * @return OForm
	 */
	public function action($action = null) {
		if ($action === null) {
			return $this->_action;
		}

		$this->_action = $action;
		return $this;
	}

	/**
	 * Pass the model into the form object.
	 *
	 * @chainable
	 *
	 * @param  object  $model  model object
	 *
	 * @return OModel|$this
	 */
	public function model(&$model = null) {
		if ($model === null) {
			return $this->_model;
		}
		$this->_model = $model;
		return $this;
	}

	/**
	 * Sets the form title.
	 *
	 * @chainable
	 *
	 * @param  string  $title  the form title
	 *
	 * @return OForm
	 */
	public function title($title = null) {
		if ($title === null) {
			return $this->_title;
		}

		$this->_title = $title;
		return $this;
	}

	/**
	 * Adds an action to the form
	 *
	 * @chainable
	 *
	 * @param  array  $actions  form actions
	 *
	 * @return OForm
	 */
	public function actions(array $actions = null) {
		if ($actions === null) {
			return $this->_actions;
		}

		$this->_actions = $actions;
		return $this;
	}

	/**
	 * Getter/Setter. Adds a field to the form or retrieves a field with the provided key.
	 *
	 * @chainable
	 *
	 * @param  string  $key       access key
	 * @param  OField  $field     OField object
	 * @param  string  $target    target to add the content before/after
	 * @param  string  $position  before|after
	 *
	 * @return OField|OForm
	 */
	public function field($key, $field = null, $target = null, $position = 'after') {
		if ($field === null) {
			$field = (isset($this->_fields[$key]) ? $this->_fields[$key] : false);
			if ($field !== false && $this->unique() !== null && $field->unique() === null) {
				$field = $field->unique($this->unique());
			}
			return $field;
		}

		$this->_fields[$key] = $field;
		$this->add_to_stack('_fields', $key, $target, $position);

		return $this;
	}

	/**
	 * Getter/Setter. Adds a collection of OField objects to the form.
	 *
	 * @chainable
	 *
	 * @param  array   $fields   collection of OField objects
	 * @param  string  $display  display view
	 *
	 * @return OForm
	 */
	public function fields(array $fields = null, $display = 'edit') {
		if ($fields === null) {
			if ($this->unique() !== null) {
				foreach ($this->_fields as $key => $field) {
					if ($field->unique() === null) {
						$this->_fields[$key] = $field->unique($this->unique());
					}
				}
			}
			return $this->_fields;
		}

		foreach ($fields as $key => $field) {
			if ($field instanceof OField) {
				$key = $field->name();
			}
			$this->_fields[$key] = $field->display($display);
			$this->add_to_stack('_fields', $key);
		}

		return $this;
	}

	/**
	 * Getter/Setter. Adds a fieldset to the field.
	 *
	 * @chainable
	 *
	 * @param  string     $key       access key
	 * @param  OFieldset  $fieldset  OFieldset object
	 * @param  string     $target    target to add the content before/after
	 * @param  string     $position  before|after
	 *
	 * @return OFieldset|OForm
	 */
	public function fieldset($key, $fieldset = null, $target = null, $position = 'after') {
		if ($fieldset === null) {
			$fieldset = (isset($this->_fieldsets[$key]) ? $this->_fieldsets[$key] : false);
			if ($fieldset !== false && $this->unique() !== null && $fieldset->unique() === null) {
				$fieldset = $fieldset->unique($this->unique());
			}
			return $fieldset;
		}

		// Add to the stack
		$this->add_to_stack('_fieldsets', $key, $target, $position);
		$this->_fieldsets[$key] = $fieldset;

		return $this;
	}

	/**
	 * Getter/Setter. Adds a button to the form.
	 *
	 * @chainable
	 *
	 * @param  string  $key    access key
	 * @param  OField  $field  OField object
	 *
	 * @return OField|OForm
	 */
	public function button($key, $field = null) {
		if ($field === null) {
			$button = (isset($this->_buttons[$key]) ? $this->_buttons[$key] : false);
			if ($button !== false && $this->unique() !== null && $button->unique() === null) {
				$button = $button->unique($this->unique());
			}
			return $button;
		}

		if ($this->unique() !== null && $field->unique() === null) {
			$field = $field->unique($this->unique());
		}
		$this->_buttons[$key] = $field;
		return $this;
	}

	/**
	 * Getter/Setter. Adds content to the form. Ideally, you should use pass a [View] object in
	 * as the content.
	 *
	 * @chainable
	 *
	 * @param  string  $key       access key
	 * @param  string  $content   content
	 * @param  string  $target    target to add the content before/after
	 * @param  string  $position  before|after
	 *
	 * @return string|OForm
	 */
	public function content($key, $content = null, $target = null, $position = 'after') {
		if ($content === null) {
			return $this->_content[$key];
		}

		$this->_content[$key] = $content;
		$this->add_to_stack('_content', $key, $target, $position);
		return $this;
	}

	/**
	 * Loads the header template bit.
	 *
	 * @return string
	 */
	public function header() {
		$data = OHooks::instance()->filter($this->name().'.form.header_data', array(
			'form' => $this,
			'attributes' => $this->attributes(),
			'title' => $this->title(),
			'actions' => $this->actions(),
			'model' => $this->_model
		));
		return $this->load_view('header', $data);
	}

	/**
	 * Loads the footer template bit.
	 *
	 * @return string
	 */
	public function footer() {
		$data = OHooks::instance()->filter($this->name().'.form.footer_data', array(
			'form' => $this,
			'buttons' => $this->_buttons,
			'model' => $this->_model
		));
		return $this->load_view('footer', $data);
	}

	/**
	 * Piggy-backing onto [View::render] to inject some element data.
	 *
	 * @param  string  $file  view filename
	 *
	 * @return string
	 * @uses View::render
	 */
	public function render($file = null) {
		$this->set_shell_attributes();
		$this->set_attributes();

		if ($this->action() === null) {
			$this->action(Request::current()->uri());
		}

		$data = OHooks::instance()->filter($this->id().'.form.render_data', array(
			'form' => $this,
			'action' => $this->action(),
			'shell_attributes' => $this->attributes(null, true),
			'attributes' => $this->attributes(),
			'header' => $this->header(),
			'content' => $this->compile(),
			'footer' => $this->footer()
		));
		$this->set($data);

		return parent::render($file);
	}

	/**
	 * Removes a field from all the fieldsets it's associated with.
	 *
	 * @param  string  $group  group
	 * @param  string  $key    access key
	 *
	 * @return bool
	 * @uses Oxygen_HTML_Element::remove_from_stack
	 */
	protected function remove_from_stack($group, $key) {
		// Remove the field from any fieldset it may be hiding in.
		if ($group == '_field') {
			foreach ($this->_fieldsets as $fieldset) {
				$_fields = array();
				foreach ($fieldset->fields() as $field) {
					if ($field != $key) {
						$_fields[] = $field;
					}
				}

				// Re-save the fieldset
				$this->_fieldsets[$fieldset]->fields($_fields);
			}
		}

		return parent::remove_from_stack($group, $key);
	}

	/**
	 * Adds the form shell attributes.
	 *
	 * @return void
	 */
	protected function set_shell_attributes() {
		parent::set_shell_attributes();
		$this->add_css_class(array('box', 'frm', 'elm-width-300'), true);
	}

	/**
	 * Sets the form attributes.
	 *
	 * @return void
	 */
	protected function set_attributes() {
		parent::set_attributes();

		$attributes = array(
			'name' => $this->name(),
		);

		// Unique?
		if ($this->_unique !== null) {
			$attributes += array(
				'data-unique' => $this->unique()
			);
		}

		$this->attributes($attributes);

		// Label position?
		if (!isset($this->_attributes['class']) || strpos($this->_attributes['class'], 'lbl-pos') === false) {
			$this->add_css_class('lbl-pos-top');
		}
	}

} // End Model_Form
