<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Resource {

	/**
	 * @var  array  OResource instances
	 */
	protected static $instances = array();

	/**
	 * Get a singleton OResource instance. If configuration is not specified,
	 * it will be loaded from the resources configuration file using the same
	 * group as the key.
	 *
	 *	 // Create a custom configured instance
	 *	 $resources = OResource::instance('css', $config);
	 *
	 * @param   string  $key	 instance name
	 * @param   array   $config  configuration parameters
	 * @return  OResource
	 */
	public static function instance($key, array $config = null) {
		if (empty($key)) {
			$key = rand(0, 9999);
		}

		if (!isset(OResource::$instances[$key])) {
			if ($config === null) {
				// Load the configuration for this database
				$config = Oxygen::config('resources');
				if (isset($config->$key)) {
					$config = $config->$key;
				}
				else {
					$config = array(
						'type' => 'css',
						'resources' => array()
					);
				}
			}

			if (!isset($config['type'])) {
				throw new Kohana_Exception('Resource type not defined in :key configuration',
					array(':key' => $key));
			}

			// Create the database connection instance
			OResource::$instances[$key] = new OResource($key, $config);
		}

		return OResource::$instances[$key];
	}

	/**
	 * @var  string  accessor key
	 */
	protected $_key = '';

	/**
	 * @var  string  css|js
	 */
	protected $_type = '';

	/**
	 * @var  array  resources
	 */
	protected $_resources = array();

	/**
	 * Sets the local variables.
	 *
	 * @param  string  $key	 instance name
	 * @param  array   $config  configuration parameters
	 */
	public function __construct($key, array $config) {
		$this->_key = $key;
		$this->_type = $config['type'];
		$this->_resources = $config['resources'];
	}

	/**
	 * Sets, or returns, the instance key.
	 *
	 * @param  string  $key  instance key
	 * @return OResource|string
	 */
	public function key($key = null) {
		if ($key == null) {
			return $this->_key;
		}

		$this->_key = $key;
		return $this;
	}

	/**
	 * Sets, or returns, the instance type.
	 *
	 * @param  string  $type  instance type (css|js)
	 * @return OResource|string
	 */
	public function type($type = null) {
		if ($type === null) {
			return $this->_type;
		}

		$this->_type = $type;
		return $this;
	}

	/**
	 * Sets, or returns, the instance resources.
	 *
	 * @param  array  $resources  array of resources
	 * @return OResource|array
	 */
	public function resources(array $resources = null) {
		if ($resources === null) {
			return $this->_resources;
		}

		$this->_resources = $resources;
		return $this;
	}

	/**
	 * Add a resource to the instance.
	 *
	 * @param  string  $key         the resource key (must be unique)
	 * @param  array   $resource    resource configuration
	 * @param  string  $target      target to add the resource before|after
	 * @param  string  $position    after|default, default after
	 * @return OResource
	 */
	public function add($key, array $resource, $target = null, $position = 'after') {
		// TODO add the ability to pass an array as $key to add multiple resources at once
		if ($target === null) {
			$this->_resources[$key] = $resource;
		}
		else {
			if (isset($this->_resources[$$key])) {
				unset($this->_resources[$key]);
			}

			$added = false;
			$_resources = array();
			foreach ($this->_resources as $resource_key => $resource) {
				if ($resource_key == $target) {
					$added = true;
					if ($position == 'before') {
						$_resources[$key] = $resource;
					}

					$_resources[$resource_key] = $resource;

					if ($position == 'after') {
						$_resources[$key] = $resource;
					}
				}
				else {
					$_resources[$key] = $resource;
				}
			}

			if (!$added) {
				Oxygen::$log->add(Log::INFO, 'The :target resource does not exist for :key.',
					array(':target' => $target, ':key' => $this->_key));

				$_resources[$key] = $resource;
			}

			$this->_resources = $_resources;
		}

		return $this;
	}

	/**
	 * Removes a resource from the instance.
	 *
	 * @param  string  $key  key of the resource to remove
	 * @return OResource
	 */
	public function remove($key) {
		$_resources = array();
		foreach ($this->_resources as $resource_key => $resource) {
			if ($resource_key != $key) {
				$_resources[$resource_key] = $resource;
			}
		}

		$this->_resources = $_resources;
		return $this;
	}

	/**
	 * Runs the instance's resource through a filter and then returns the final set.
	 *
	 * @return array
	 */
	public function compile() {
		OHooks::instance()->event('compile_resources', $this->_key);
		return $this->_resources;
	}

	/**
	 * Builds the resource URL.
	 *
	 * @param  string  $key      accessor in the config stack
	 * @param  int     $version  resource version
	 * @return string
	 */
	public static function url($key, $version = 0) {
		$uri = array(
			'resources',
			$key,
		);

		if (Oxygen::$environment !== Oxygen::PRODUCTION && !$version) {
			$version = time();
		}

		if ($version) {
			$uri['version'] = $version;
		}
		return implode('/', $uri);
	}

} // End Oxygen_Resources
