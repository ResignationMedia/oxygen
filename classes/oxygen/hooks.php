<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Hooks {

	/**
	 * @var  OHooks  instance
	 */
	public static $instance;

	/**
	 * Returns an instance of Hooks.
	 *
	 * @static
	 * @return OHooks
	 */
	public static function instance() {
		if (self::$instance === null) {
			// Create new instance
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * @var  array  stored hooks
	 */
	private $_hooks = array();

	/**
	 * Adds a hook listener to be called.
	 *
	 * @chainable
	 * @param  string  $key       key to access the hook
	 * @param  string  $function  callback
	 * @param  int	   $priority  priority
	 * @return OHooks
	 */
	public function add_listener($key = '', $function = '', $priority = 100) {
		if ($key && $function) {
			// Does this hook already exist?
			if (!isset($this->_hooks[$key])) {
				$this->_hooks[$key] = array();
			}

			for ($i = 0; $i < 10000; ++$i) {
				$unique = 'priority_'.$priority.'.'.substr((10000+$i), 1);
				if (!isset($this->_hooks[$key][$unique])) {
					break;
				}
			}

			$this->_hooks[$key][$unique] = $function;
			ksort($this->_hooks);
		}

		return $this;
	}

	public function add($key = '', $function = '', $priority = 100) {
		Oxygen::deprecated('Use add_listener() instead.');
		return $this->add_listener($key, $function, $priority);
	}

	/**
	 * Removes a hook listener.
	 *
	 * @chainable
	 * @param  string  $key       access key
	 * @param  string  $function  callback
	 * @param  int     $priority  priority
	 * @return OHooks
	 */
	public function remove_listener($key = '', $function = '', $priority = 100) {
		if ($key && $function) {
			if (isset($this->_hooks[$key]) && count($this->_hooks[$key])) {
				foreach ($this->_hooks[$key] as $unique => $_function) {
					$_priority = explode('.', $unique);
					$_priority = $_priority[0];
					$_priority = str_replace('priority_', '', $_priority);
					if ($priority == floor($_priority) && $function == $_function) {
						unset($this->_hooks[$key][$unique]);
					}
				}
			}
		}

		return $this;
	}

	public function remove($key = '', $function = '', $priority = 100) {
		Oxygen::deprecated('Use remove_listener() instead.');
		return $this->remove_listener($key, $function, $priority);
	}

	/**
	 * Executes an event.
	 *
	 * @param  string  $key   key to access the event
	 * @param  mixed   $data  data to pass to the event
	 * @return array
	 */
	public function event($key = '', $data = array()) {
		$token = Profiler::start('events', $key);
		if (isset($this->_hooks[$key])) {
			foreach ($this->_hooks[$key] as $function) {
				if (is_callable($function)) {
					call_user_func($function, $data);
				}
			}
		}
		Profiler::stop($token);

		return $data;
	}

	/**
	 * Applies a filter.
	 *
	 * @param  string  $key   key to access the filter
	 * @param  mixed   $data  data to pass to the filter
	 * @return array
	 */
	public function filter($key = '', $data = array()) {
		$token = Profiler::start('filters', $key);
		if (isset($this->_hooks[$key])) {
			foreach ($this->_hooks[$key] as $function) {
				if (is_callable($function)) {
					$data = call_user_func($function, $data);
				}
			}
		}
		Profiler::stop($token);

		return $data;
	}

	/**
	 * Alias for $this->event()
	 *
	 * @chainable
	 * @param  string  $key   access key
	 * @param  mixed   $data  data to pass to the event
	 * @return Hooks
	 */
	public function modify($key = '', $data = array()) {
		return $this->event($key, $data);
	}

} // End Oxygen_Hooks
