<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Fieldset extends Oxygen_HTML_Element {

	/**
	 * @var  array  default views
	 */
	protected $_views = array(
		'shell' => 'form/fieldset'
	);

	/**
	 * @var  string  fieldset legend
	 */
	protected $_legend = '';

	/**
	 * @var  string  content to be added before the fields
	 */
	protected $_content = '';

	/**
	 * @var  array  fields of the fieldset
	 */
	protected $_fields = array();

	/**
	 * @var  OModel  object
	 */
	protected $_model = null;

	/**
	 * @static
	 * @param  string  $file  view filename
	 * @param  array   $data  data to be passed to the view
	 * @return OFieldset
	 */
	public static function factory($file = null, array $data = array()) {
		return new OFieldset($file, $data);
	}

	/**
	 * Sets and gets the model for the fieldset.
	 *
	 * @param  OModel  $model
	 * @return null|OModel|Oxygen_Form_Fieldset
	 */
	public function model(&$model = null) {
		if ($model === null) {
			return $this->_model;
		}

		$this->_model = $model;
		return $this;
	}

	/**
	 * Sets the fieldset's legend.
	 *
	 * @chainable
	 * @param  string  $legend  fieldset legend
	 * @return OFieldset
	 */
	public function legend($legend = null) {
		if ($legend == null) {
			return $this->_legend;
		}

		$this->_legend = $legend;
		$this->add_css_class('has-legend', true);

		return $this;
	}

	/**
	 * Sets the content for the fieldset, which will be displayed before the fields.
	 *
	 * @chainable
	 * @param  string  $content  content
	 * @return OFieldset
	 */
	public function content($content = null) {
		if ($content == null) {
			return $this->_content;
		}

		$this->_content = $content;
		$this->add_css_class('has-content', true);

		return $this;
	}

	/**
	 * Adds a field to the fieldset.
	 *
	 * @chainable
	 * @param  string  $key       access key
	 * @param  OField  $field     OField object
	 * @param  string  $target    target to add the content before/after
     * @param  string  $position  before|after
	 * @return OFieldset
	 */
	public function field($key, $field = null, $target = null, $position = 'after') {
		if ($field === null) {
			return $this->_fields[$key];
		}

		$this->_fields[$key] = $field;
		$this->add_to_stack('_fields', $key, $target, $position);
		return $this;
	}

	/**
	 * Adds fields to the fieldset.
	 *
	 * @chainable
	 * @param  array  $fields  collection of OField objects
	 * @return OFieldset
	 */
	public function fields(array $fields = null) {
		if ($fields == null) {
			return $this->_fields;
		}

		foreach ($fields as $field) {
			$this->_fields[$field->name()] = $field;
			$this->add_to_stack('_fields', $field->name());
		}

		return $this;
	}

	/**
	 * Renders the current fieldset.
	 *
	 * @param  string  $file  shell view file name
	 * @return string
	 */
	public function render($file = null) {
		$this->set_shell_attributes();
		$data = OHooks::instance()->filter($this->id().'.fieldset.render_data', array(
			'fieldset' => $this,
			'attributes' => $this->_shell_attributes,
			'legend' => $this->_legend,
			'content' => $this->_content,
			'model' => $this->_model,
			'fields' => $this->compile()
		));
		$this->set($data);

		return parent::render($file);
	}

	/**
	 * Compiles the fields for the fieldset.
	 *
	 * @return array
	 */
	protected function compile() {
		$fields = array();
		foreach ($this->_stack as $key => $group) {
			if ($group == '_fields') {
				$fields[] = $this->_fields[$key];
			}
		}

		return $fields;
	}

} // End Oxygen_Form_Fieldset
