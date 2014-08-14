<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field extends Oxygen_HTML_Element {

	/**
	 * @var  array  default field views
	 */
	protected $_views = array(
		'shell' => 'form/field/shell',
		'label' => 'form/field/label',
		'view' => 'form/field/view',
		'edit' => 'form/field/edit',
		'search' => 'form/field/search',
		'help' => 'form/field/help',
	);

	/**
	 * @var  string  view to display
	 */
	protected $_display = 'edit';

	/**
	 * @var  array  field help objects
	 */
	protected $_help = array(
		'top' => '',
		'right' => '',
		'bottom' => '',
		'left' => '',
	);

	/**
	 * @var  string  type of field
	 */
	protected $_type = null;

	/**
	 * @var  string  data type
	 */
	protected $_data_type = 'string';

	/**
	 * @var  string  display type
	 */
	protected $_display_type = null;

	/**
	 * @var  string  value of the field
	 */
	protected $_value = null;

	/**
	 * @var  string  default value of the field (used for flags)
	 */
	protected $_default_value = null;

	/**
	 * @var  array  options for the field
	 */
	protected $_options = array();

	/**
	 * @var  string  label of the field
	 */
	protected $_label = null;

	/**
	 * @var  string  label for lists
	 */
	protected $_list_label = null;

	/**
	 * @var  bool  use related model for value?
	 */
	protected $_use_related = null;

	/**
	 * @var  bool  link to related model?
	 */
	protected $_link_related = null;

	/**
	 * @var  array  default operator to use on Query requests
	 */
	protected $_query_options = array();

	/**
	 * @var  OModel  object
	 */
	protected $_model = null;

	/**
	 * @var  string  model name
	 */
	protected $_related_model = null;

	/**
	 * Creates a new OField object.
	 *
	 *	 $field = OField::factory($type);
	 *
	 * @static
	 * @param  string  $type  type of field
	 * @param  array   $data  array of values for the view
	 * @return OField
	 */
	public static function factory($type = 'text', array $data = null) {
		$type = 'Oxygen_Form_Field_'.$type;
		return new $type($type, $data);
	}

	/**
	 * Sets the field view type and then calls [View::__construct].
	 *
	 * @param  string  $type  type of field
	 * @param  array   $data  array of values
	 */
	public function __construct($type = 'text', array $data = null) {
		parent::__construct(null, $data);
	}

	/**
	 * Use the related model?
	 *
	 * @param  bool  $use_related
	 * @return OField|bool
	 */
	public function use_related($use_related = null) {
		if ($use_related === null) {
			return $this->_use_related;
		}

		$this->_use_related = $use_related;
		return $this;
	}

	/**
	 * Link to the related model?
	 *
	 * $field->link_related(true):
	 *     <a href="/related/view/1">Username</a>
	 *
	 * @param  bool  $link_related
	 * @return OField|bool
	 */
	public function link_related($link_related = null) {
		if ($link_related === null) {
			return $this->_link_related;
		}

		$this->_link_related = $link_related;
		if ($link_related) {
			$this->use_related(true);
		}
		return $this;
	}

	/**
	 * Sets and gets the model for the field.
	 *
	 * @param  OModel  $model  instance of a model for the field
	 * @return null|OModel|Oxygen_Form_Field
	 */
	public function model(&$model = null) {
		if ($model === null) {
			return $this->_model;
		}

		$this->_model = $model;
		return $this;
	}

	/**
	 * Sets the field type.
	 *
	 * @param  string  $type  the field type
	 * @return OField|string
	 */
	public function type($type = null) {
		if ($type === null) {
			return $this->_type;
		}

		$this->_type = $type;
		return $this;
	}

	/**
	 * Sets the field data type.
	 *
	 * @param  string  $type  the field type
	 * @return OField|string
	 */
	public function data_type($data_type = null) {
		if ($data_type === null) {
			return $this->_data_type;
		}

		$this->_data_type = $data_type;
		return $this;
	}

	/**
	 * Sets the display type.
	 *
	 * @param  string  $display
	 * @return OField|string
	 */
	public function display_type($display = null) {
		if ($display === null) {
			return $this->_display_type;
		}

		$this->_display_type = $display;
		return $this;
	}

	/**
	 * Sets the field's label and list label as well.
	 *
	 * @chainable
	 * @param  string  $name  field name
	 * @return OField|string
	 */
	public function name($name = null) {
		if ($name !== null && $this->_label === null) {
			// Set the label
			$this->_label = ucwords(Inflector::humanize($name));
			$this->_list_label = $this->_label;
		}

		return parent::name($name);
	}

	/**
	 * Sets the display type, if the display type view does not exist then an
	 * exception is thrown.
	 *
	 * @throws Kohana_Exception
	 * @param  string  $display  display type
	 * @return OField|string
	 */
	public function display($display = null) {
		if ($display === null) {
			return $this->_display;
		}

		if ($this->view($display) === null) {
			throw new Kohana_Exception('Invalid OField display type: :display', array(
				':display' => $display
			));
		}

		$this->_display = $display;

		return $this;
	}

	/**
	 * Sets the field's label.
	 *
	 * @chainable
	 * @param  string  $label  field label
	 * @return OField|string
	 */
	public function label($label = null) {
		if ($label === null) {
			return $this->_label;
		}

		$this->_label = $label;
		return $this;
	}

	/**
	 * Sets the label that will be visible on lists.
	 *
	 * @chainable
	 * @param  string  $label  list label
	 * @return OField|string
	 */
	public function list_label($label = null) {
		if ($label === null) {
			return $this->_list_label;
		}

		$this->_list_label = $label;
		return $this;
	}

	/**
	 * Sets the value for the field.
	 *
	 * @chainable
	 * @param  string  $value  default value for the field
	 * @return OField|string
	 */
	public function value($value = null) {
		if ($value === null) {
			$value = $this->_value;
			if ($value == null && $this->_type != 'flag') {
				$value = $this->default_value();
			}

			return $value;
		}

		$this->_value = $value;
		return $this;
	}

	/**
	 * Sets the default value for the field.
	 *
	 * @chainable
	 * @param  string  $value  default value for the field
	 * @return OField|string
	 */
	public function default_value($value = null) {
		if ($value === null) {
			return $this->_default_value;
		}

		$this->_default_value = $value;
		return $this;
	}

	/**
	 * Adds options to the field.
	 *
	 * @chainable
	 * @param  array  $options  array of options
	 * @return OField|array
	 */
	public function options(array $options = null) {
		if ($options === null) {
			// Show a select menu for related objects?
			if ($this->use_related() && $this->display() == 'edit') {
				$this->type('select');

				// Load the options
				$options = array();

				// Need to call clear() as this was breaking on AJAX edits.
				$related = $this->_model->{$this->name()};
				if ($related->loaded()) {
					$related = $related->clear();
				}

				$related = $related->find_all();
				if ($related->count()) {
					foreach ($related as $item) {
						$options[$item->pk()] = $item->{$item->view_column()};
					}
				}
				$this->options($options);

				if (is_object($this->value())) {
					$this->value($this->value()->pk());
				}
			}

			return OHooks::instance()->filter($this->id().'.field.options', $this->_options);
		}

		$this->_options = $options;
		return $this;
	}

	/**
	 * Adds help content to the field.
	 *
	 * @chainable
	 * @param  string  $position  top|right|left|bottom
	 * @param  string  $content   help content
	 * @return OField|string
	 */
	public function help($position = null, $content = null) {
		if ($content === null) {
			$text = Arr::get($this->_help, $position, $this->_help);
			if (!empty($text)) {
				return View::factory($this->view('help'), compact('text', 'position'));
			}
			return '';
		}

		$this->_help[$position] = $content;
		return $this;
	}

	/**
	 * Use HTML::chars on the value?
	 *
	 * @param  string  $value  the value to be encoded
	 * @return string
	 */
	public function chars($value = null) {
		if ($value === null) {
			$value = $this->value();
		}

		if ($this->_link_related) {
			return $value;
		}

		return parent::chars($value);
	}

	/**
	 * Sets and gets the query options.
	 *
	 * @param  array  $options
	 * @return Oxygen_Form_Field|array
	 */
	public function query_options(array $options = null) {
		if ($options === null) {
			return $this->_query_options;
		}

		$this->_query_options = $options;
		return $this;
	}

	/**
	 * Runs the translation for the current field type for Query requests.
	 *
	 * @param  string  $value
	 * @return mixed
	 */
	public function query_translation($value) {
		switch ($value) {
			case 'current_user_id':
				return Auth::instance()->get_user()->id;
			break;
		}
		return $value;
	}

	/**
	 * Sets the field view variables, then calls [Oxygen_HTML_Element::render].
	 *
	 * @param  string  $file        shell view file name
	 * @param  bool	   $find_shell  set to false to skip finding the shell
	 * @return string
	 */
	public function render($file = null, $find_shell = true) {
		// Trigger the options, or else related object options will not be populated correctly.
		$this->options();

		// Set the shell attributes
		$this->set_shell_attributes();

		// Set the element attributes
		$this->set_attributes();

		// Show a label?
		$label = '';
		if (!in_array($this->_type, array('submit', 'button', 'reset')) && $this->label() !== null) {
			$label = $this->load_view('label', array(
				'field' => $this
			));
		}

		// Set shell variables
		$data = OHooks::instance()->filter($this->id().'.field.render_data', array(
			'model' => $this->_model,
			'field' => $this,
			'label' => $label,
			'element' => $this->load_view($this->_display, array(
				'model' => $this->_model,
				'field' => $this,
			))
		));
		$this->set($data);

		// Set the display type?
		if ($this->_display == 'search' && $this->_display_type != null && $this->view('search') == 'form/field/search') {
			$this->view('search', 'form/field/search/'.$this->_display_type);
		}

		// Does the shell exist for this type?
		if ($find_shell) {
			$this->set_view('shell');
		}

		return parent::render($file);
	}

	/**
	 * Attempts to find the field type's specific view, if it exists then override the default.
	 *
	 * @param  string  $view  view key
	 * @param  array   $data  data to be passed into the view
	 * @return string
	 */
	protected function load_view($view, array $data = array()) {
		if (!isset($this->_modified_views[$view])) {
			$this->set_view($view, $this->_type);
		}
		return parent::load_view($view, $data);
	}

	/**
	 * Sets the shell attributes.
	 */
	protected function set_shell_attributes() {
		parent::set_shell_attributes();

		$this->attributes(array(
			'id' => 'elm-block'.$this->_seg.$this->id(),
			'class' => 'elm-dsp-'.$this->_display
		), true);

		$type = $this->_type;
		if ($type == 'input') {
			$type = 'text';
		}

		$this->add_css_class(array(
			'elm-block',
			'has-'.$type
		), true);
	}

	/**
	 * Sets the element type CSS class.
	 */
	protected function set_attributes() {
		parent::set_attributes();

		$type = $this->_type;
		if ($type == 'input') {
			$type = 'text';
		}

		$this->add_css_class('elm-'.$type);

		// ID
		if (!isset($this->_attributes['id'])) {
			$this->attributes(array('id' => $this->id()));
		}
	}

	/**
	 * Overrides the default view path with the specific field type's view, if it exists.
	 *
	 * @param  string  $view  view key
	 * @param  string  $type  field type
	 */
	protected function set_view($view, $type = null) {
		if ($type == null) {
			$type = $this->_type;
		}

		if (!empty($type)) {
			if (Kohana::find_file('views/form/field/'.$type, $view) !== false) {
				$this->view($view, 'form/field/'.$type.'/'.$view);
			}
		}
	}

	/**
	 * Getter/setter for $_related_model.
	 *
	 * @param  string  $model  name of model
	 * @param  bool  $force_clear  set to true to re-setting $_related_model to null
	 */
	public function related_model($model = null, $force_clear = false) {
		if (empty($model) && !$force_clear) {
			return $this->_related_model;
		}
		$this->_related_model = ($force_clear ? null : $model);

		return $this;
	}

} // End Oxygen_Form_Field
