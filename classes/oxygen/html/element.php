<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Oxygen_HTML_Element extends View {

	/**
	 * @var  array  element views
	 */
	protected $_views = array();

	/**
	 * @var  array  views that have been modified
	 */
	protected $_modified_views = array();

	/**
	 * @var  array  shell attributes
	 */
	protected $_shell_attributes = array();

	/**
	 * @var  bool  shell attributes set?
	 */
	protected $_shell_attributes_set = false;

	/**
	 * @var  array  default element attributes
	 */
	protected $_attributes = array();

	/**
	 * @var  bool  attributes set?
	 */
	protected $_attributes_set = false;

	/**
	 * @var  string  ID divider
	 */
	protected $_seg = '--';

	/**
	 * @var  string  unique ID for the element
	 */
	protected $_unique = null;

	/**
	 * @var  string  element's ID
	 */
	protected $_id = '';

	/**
	 * @var  string  element's name
	 */
	protected $_name = '';

	/**
	 * @var  array  stack in which to compile
	 */
	protected $_stack = array();

	/**
	 * @var  array  objects to be passed into views
	 */
	protected $_objects = array();

	/**
	 * @var  bool|string  encode the output?
	 */
	protected $_encode = true;

	/**
	 * Sets the element's ID.
	 *
	 * @chainable
	 *
	 * @param  string  $id  element ID
	 *
	 * @return mixed
	 */
	public function id($id = null) {
		if ($id === null) {
			return $this->_id();
		}

		$this->_id = $id;
		return $this;
	}

	/**
	 * Sets the element's name.
	 *
	 * @chainable
	 *
	 * @param  string  $name  name of the field
	 *
	 * @return OField|OFieldset|OForm|OList
	 */
	public function name($name = null) {
		if ($name === null) {
			return $this->_name;
		}

		$this->_name = $name;
		return $this;
	}

	/**
	 * Defines the attributes for the extending element.
	 *
	 * @chainable
	 *
	 * @param  array  $attributes  element-specific attributes
	 * @param  bool   $shell       add to the shell?
	 *
	 * @return mixed
	 */
	public function attributes(array $attributes = null, $shell = false) {
		$object = ($shell ? '_shell' : '').'_attributes';

		if ($attributes === null) {
			if (!$shell && !$this->_attributes_set) {
				$this->set_attributes();
			}
			else if ($shell && !$this->_shell_attributes_set) {
				$this->set_shell_attributes();
			}

			return $this->$object;
		}

		foreach ($attributes as $attribute => $value) {
			if ($attribute == 'class') {
				unset($attributes[$attribute]);

				$value = explode(' ', $value);
				$this->add_css_class($value, $shell);
			}
		}

		$this->$object = Arr::merge($this->$object, $attributes);

		return $this;
	}

	/**
	 * Sets the view for the defined key.
	 *
	 * @chainable
	 *
	 * @param  mixed   $key   view key
	 * @param  string  $view  view path
	 *
	 * @return mixed
	 */
	public function view($key, $view = null) {
		if (is_array($key)) {
			foreach ($key as $key2 => $view) {
				$this->view($key2, $view);
			}
		}
		else {
			if ($view === null) {
				return isset($this->_views[$key]) ? $this->_views[$key] : null;
			}
			$this->_views[$key] = $view;

			if (!isset($this->_modified_views[$key])) {
				$this->_modified_views[$key] = true;
			}
		}

		return $this;
	}

	/**
	 * Sets an object to be passed into the view.
	 *
	 * @chainable
	 *
	 * @param  mixed  $key   object key
	 * @param  array  $data  object data
	 *
	 * @return mixed
	 */
	public function set_object($key, array $data) {
		if (is_array($key)) {
			foreach ($key as $key2 => $data) {
				$this->_objects[$key2] = $data;
			}
		}
		else {
			$this->_objects[$key] = $data;
		}

		return $this;
	}

	/**
	 * Adds a CSS class name to the element, or shell.
	 *
	 * To add to the element attributes:
	 *
	 *	 $field->add_css_class('className');
	 *
	 * To add to the shell attributes:
	 *
	 *	 $field->add_css_class('className', true);
	 *
	 * @chainable
	 *
	 * @param  mixed     $class  class name(s) to add
	 * @param  bool	  $shell  add to the shell?
	 *
	 * @return mixed
	 */
	public function add_css_class($class, $shell = false) {
		if (is_array($class)) {
			foreach ($class as $_class) {
				$this->add_css_class($_class, $shell);
			}
		}
		else {
			$class = trim($class);
			$object = ($shell ? '_shell' : '').'_attributes';
			if (!isset($this->{$object}['class'])) {
				$this->{$object}['class'] = $class;
			}
			else {
				$current = explode(' ', $this->{$object}['class']);
				if (!in_array($class, $current)) {
					$this->{$object}['class'] .= ' '.$class;
				}
			}
		}

		return $this;
	}

	/**
	 * Removes a CSS class name from the element, or the shell.
	 *
	 * @chainable
	 *
	 * @param  mixed     $class  class name(s) to remove
	 * @param  bool	  $shell  remove from the shell?
	 *
	 * @return mixed
	 */
	public function remove_css_class($class, $shell = false) {
		if (is_array($class)) {
			foreach ($class as $_class) {
				$this->remove_css_class($_class, $shell);
			}
		}
		else {
			$class = trim($class);
			$object = ($shell ? '_shell' : '').'_attributes';
			if (isset($this->{$object}['class'])) {
				$current_classes = explode(' ', $this->{$object}['class']);
				$classes = array();
				foreach ($current_classes as $current) {
					if ($current != $class) {
						$classes[] = $current;
					}
				}
				$this->{$object}['class'] = implode(' ', $classes);
			}
		}

		return $this;
	}

	/**
	 * Flags the current item as unique.
	 *
	 * @chainable
	 *
	 * @param  string  $key  return the unique key?
	 *
	 * @return mixed
	 */
	public function unique($key = null) {
		if ($key === null) {
			return $this->_unique;
		}

		$this->_unique = $key;
		return $this;
	}

	/**
	 * Double encode, or display the raw data?
	 *
	 * @param  bool|string  $key  defaults are true|false|'raw'
	 *
	 * @return bool|string|OField
	 */
	public function encode($key = null) {
		if ($key === null) {
			return $this->_encode;
		}

		$this->_encode = $key;
		return $this;
	}

	/**
	 * Use HTML::chars on the value?
	 *
	 * @param  bool|string  $key    defaults are true|false|'raw'
	 * @param  string       $value  value to encode
	 *
	 * @return string
	 */
	public function chars($value = null) {
		switch ($this->_encode) {
			case true:
				return HTML::chars($value);
				break;
			case false:
				return HTML::chars($value, false);
				break;
			case 'raw':
				return $value;
				break;
		}
	}

	/**
	 * Sets the shell view.
	 *
	 * @param  string  $file  shell view file name
	 *
	 * @return string
	 */
	public function render($file = null) {
		if ($this->_file === null && $file === null) {
			$file = $this->view('shell');
		}

		return parent::render($file);
	}

	/**
	 * Tries to find the view file, if it doesn't exist then returns an empty string.
	 *
	 * @param  string  $view  view key
	 * @param  array   $data  data for the view
	 *
	 * @return string
	 */
	protected function load_view($view, array $data = array()) {
		if ($this->view($view) !== null && $this->view($view) !== false) {
			$data = Arr::merge($this->_objects, $data);

			return View::factory($this->view($view), $data);
		}

		return '';
	}

	/**
	 * Adds an element to the compile stack.
	 *
	 * @param  string  $group     name of the local group
	 * @param  string  $key       key of the element
	 * @param  string  $target    target to place the object before/after
	 * @param  string  $position  before|after
	 */
	protected function add_to_stack($group, $key, $target = null, $position = 'after') {
		if ($target !== null) {
			if ($target == 'top') {
				$this->_stack = Arr::unshift($this->_stack, $key, $group);
			}
			else {
				$_stack = array();
				foreach ($this->_stack as $k => $g) {
					// Is this stack item the same kind of object as the new stack item?
					if ($g == $group) {
						// Before?
						if ($position == 'before' && $key == $target) {
							$_stack[$key] = $group;
						}

						// Re-add the existing object
						$_stack[$k] = $g;

						// After?
						if ($position == 'after' && $key == $target) {
							$_stack[$key] = $group;
						}
					}
					else {
						// Re-add the existing object
						$_stack[$k] = $g;
					}
				}

				// Store the stack
				$this->_stack = $_stack;
			}
		}
		else {
			$this->_stack[$key] = $group;
		}
	}

	/**
	 * Removes an element from the compile stack.
	 *
	 * @param  string  $group  stack group
	 * @param  string  $key    access key
	 *
	 * @return bool
	 */
	protected function remove_from_stack($group, $key) {
		$_stack = array();
		$removed = false;
		foreach ($this->_stack as $k => $g) {
			if ($g == $group && $k == $key) {
				$removed = true;
			}
			else {
				// This one stays...
				$_stack[$k] = $g;
			}
		}

		// Re-save the stack
		$this->_stack = $_stack;

		return $removed;
	}

	/**
	 * Generates a unique stack key.
	 *
	 * @return string
	 */
	protected function generate_stack_key() {
		$key = Text::random('alnum', 6);
		foreach ($this->_stack as $k => $g) {
			if ($key == $k) {
				// Key was found, generate a new one...
				$key = $this->generate_stack_key();
			}
		}

		return $key;
	}

	/**
	 * Compiles the stack.
	 *
	 * @return string
	 */
	protected function compile() {
		$output = '';
		foreach ($this->_stack as $key => $group) {
			$object = $this->{$group}[$key];
			if (method_exists($object, 'unique')) {
				if ($object->unique() === null && $this->_unique !== null) {
					$object->id($object->id().$this->_seg.$this->_unique);
				}
			}

			$output .= $object;
		}

		return $output;
	}

	/**
	 * Gets the ID for the current element.
	 *
	 * @return string
	 */
	protected function _id() {
		if (!empty($this->_id)) {
			return $this->_id;
		}
		else if (!empty($this->_name) && $this->_unique !== null) {
			return $this->_name.$this->_seg.$this->_unique;
		}
		else if (!empty($this->_attributes['id'])) {
			return $this->_attributes['id'];
		}
		else {
			return $this->_name;
		}
	}

	/**
	 * By default, this method will just set the data-unique key. You may extend
	 * this method in extending classes to add extra functionality.
	 */
	protected function set_shell_attributes() {
		if ($this->_unique !== null && !isset($this->_shell_attributes['data-unique'])) {
			$this->attributes(array(
				'data-unique' => $this->_unique
			), true);
		}

		$this->_shell_attributes_set = true;
	}

	/**
	 * By default, this method will just set the element's ID. You may extend
	 * this method in extending classes to add extra functionality.
	 */
	protected function set_attributes() {
		if (empty($this->_id) && !isset($this->_attributes['id'])) {
			$id = $this->id();
			$this->attributes(array(
				'id' => $id
			));
		}

		$this->_attributes_set = true;
	}

	/**
	 * Runs local object methods.
	 *
	 * @param  array  $options  array of method => params
	 *
	 * @return mixed
	 */
	protected function build(array $options) {
		foreach ($options as $function => $params) {
			if (method_exists($this, $function)) {
				call_user_func_array(array($this, $function), array($params));
			}
		}

		return $this;
	}

} // End Oxygen_HTML_Element
