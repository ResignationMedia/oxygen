<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Core extends Kohana {

	// Common environment type constants for consistency and convenience
	const PRODUCTION = 1;
	const STAGING = 2;
	const TESTING = 3;
	const DEVELOPMENT = 4;

	/**
	 * @var  string  Current environment name
	 */
	public static $environment = Oxygen::PRODUCTION;

	/**
	 * @var  bool  does the application have a config.php?
	 */
	public static $has_config = false;

	/**
	 * @var  boolean  Whether to enable [profiling](kohana/profiling). Set by [Kohana::init]
	 */
	public static $profiling = false;

	/**
	 * @var  int  used for view profiler information
	 */
	public static $view_counter = 0;

	/**
	 * @var  array  configuration cache
	 */
	protected static $_config_cache = array();

	/**
	 * Initializes Oxygen
	 *
	 * @static
	 * @param  array  $settings  settings to pass to Kohana::init
	 * @param  array  $modules   modules to load
	 */
	public static function init(array $settings = null, array $modules = array()) {

		// Load the logger if one doesn't already exist
		if (!Oxygen::$log instanceof Log) {
			Oxygen::$log = Log::instance();
		}
		/**
		 * Attach the file write to logging. Multiple writers are supported.
		 */
		Oxygen::$log->attach(new Log_File(DOCROOT . 'logs'));

		// Load the config if one doesn't already exist
		if (!Oxygen::$config instanceof Config) {
			Oxygen::$config = new Config;
		}
		/**
		 * Attach a file reader to config. Multiple readers are supported.
		 */
		Oxygen::$config->attach(new Config_File);

		// Media directory writable?
		if (!is_writable(DOCROOT . 'uploads')) {
			throw new Kohana_Exception('Directory :dir must be writable',
				array(':dir' => Debug::path(DOCROOT . 'uploads'))
			);
		}

		/**
		 * Load the new Kohana::$paths stack.
		 */
		Oxygen::modules($modules);

		/**
		 * Load the default configuration values.
		 */
		$config = array();
		include OXYPATH . 'config.php';
		include APPPATH . 'config.php';

		// Only include the root config if it exists.
		if (file_exists(DOCROOT . 'config.php')) {
			Oxygen::$has_config = true;

			include DOCROOT . 'config.php';

			/**
			 * Set database configuration
			 */
			if (isset($db)) {
				$db_config['table_prefix'] = $db['table_prefix'];
				unset($db['table_prefix']);
				$db_config['connection'] = $db;

				$default = Oxygen::config('database.default');
				Oxygen::config('database')->set('default', Arr::merge($default, $db_config));
			}
		}

		/**
		 * Loop through the configuration keys and store their values
		 * on the Oxygen object.
		 */
		if (isset($config)) {
			foreach ($config as $key => $value) {
				Oxygen::config('oxygen')->set($key, $value);
			}
		}

		$merged_config = Arr::merge($settings, $config);

		// enable profiling if set in config
		if (isset($merged_config['profile'])) {
			Oxygen::$profiling = (bool) $merged_config['profile'];
		}

		// Initialize Kohana
		parent::init($merged_config);

		// Cache the routes only in production
		if (!Route::cache() && Oxygen::$environment === Oxygen::PRODUCTION) {
			Route::cache(true);
		}

	}

	/**
	 * Wrapper for naming conventions.
	 *
	 * @static
	 * @param  string  $group
	 * @return Config
	 */
	public static function config($group) {
		return Oxygen::$config->load($group);
	}

	/**
	 * Initializes Oxygen's default routes.
	 *
	 * @static
	 */
	public static function init_routes() {
		if (!Route::cache()) {
			// Install
			Route::set('install', 'install(/<key>)')
			->defaults(array(
				'controller' => 'install',
				'action' => 'index',
			));

			// Auth
			Route::set('auth', '<action>(/<id>)', array(
				'action' => 'login|logout|forgot_password|new_password'
			))
			->defaults(array(
				'controller' => 'auth'
			));

			// Activity
			Route::set('activity/find', 'activity/find(/<audit_id>(/<target>))', array(
				'audit_id' => '\d+',
			))
			->defaults(array(
				'controller' => 'activity',
				'action' => 'find',
			));
			Route::set('activity/recent', 'activity/recent(.<format>)', array(
				'format' => 'html|json'
			))
			->defaults(array(
				'controller' => 'activity',
				'action' => 'recent',
			));

			// Resources
			Route::set('resources', 'resources(/<key>(/<version>))', array(
				'version' => '\d+'
			))
			->defaults(array(
				'controller' => 'resources'
			));

			// Audits
			Route::set('history', 'history(/<table>(/<id>(/<model>)))', array(
				'table' => '(?!view|compare)[a-zA-Z_]+',
				'id' => '\d+',
				'model' => '[a-zA-Z_]+',
			))
			->defaults(array(
				'controller' => 'history',
				'action' => 'index',
			));

			Route::set('history/compare', 'history/compare(/<a>(/<b>))', array(
				'a' => '\d+',
			))
			->defaults(array(
				'controller' => 'history',
				'action' => 'compare',
			));

			// Profile
			Route::set('profile', 'profile(/<action>)')
			->defaults(array(
				'controller' => 'profile',
				'action' => 'profile'
			));

			// Heartbeat
			Route::set('heartbeat', 'heartbeat(/<key>)')
			->defaults(array(
				'controller' => 'heartbeat',
				'action' => 'key',
			));

			Route::set('search/form', 'search/form(/<model>)', array(
				'model' => '[a-zA-Z_]+',
			))
			->defaults(array(
				'controller' => 'search',
				'action' => 'form',
			));

			// Search
			Route::set('search', 'search(/<key>(/<page>(/<sort>(/<order>))))', array(
				'page' => '\d+',
				'sort' => '[a-zA-Z_-]+',
				'order' => '(asc|desc)',
			))
			->defaults(array(
				'controller' => 'search',
				'action' => 'index',
			));

			Route::set('crud-search', '<controller>/search(/<key>(/<page>(/<sort>(/<order>))))', array(
				'page' => '\d+',
				'sort' => '[a-zA-Z_-]+',
				'order' => '(asc|desc)',
			))
			->defaults(arraY(
				'action' => 'search',
			));

			// Pages
			Route::set('grid', '<controller>/grid(/<page>(/<sort>(/<order>)))', array(
				'page' => '\d+',
				'sort' => '[a-zA-Z_-]+',
				'order' => '(asc|desc)',
			))
			->defaults(array(
				'controller' => 'dashboard',
				'action' => 'index',
			));

			Route::set('grid_list_view', '<controller>/<action>/<id>(/<page>(/<sort>(/<order>)))', array(
				'id' => '\d+',
				'sort' => '[a-zA-Z_-]+',
				'order' => '(asc|desc)',
			));

			// Lists
			Route::set('list', '<controller>(/<page>(/<sort>(/<order>)))', array(
				'page' => '\d+',
				'sort' => '[a-zA-Z_-]+',
				'order' => '(asc|desc)',
			))
			->defaults(array(
				'controller' => 'dashboard',
				'action' => 'index',
			));
		}
	}

	/**
	 * Changes the currently enabled modules. Module paths may be relative
	 * or absolute, but must point to a directory:
	 *
	 *     Oxygen::modules(array('modules/foo', MODPATH.'bar'));
	 *
	 * @param   array  $modules  list of module paths
	 * @return  void
	 */
	public static function modules(array $modules = array()) {
		$core_modules = array(
			'kohana-archive' => MODPATH . 'kohana-archive', // Kohana Archive (For Unit Testing)
			'unittest' => MODPATH . 'unittest', // Unit Testing
			'userguide' => MODPATH . 'userguide', // User Guide
			'cache' => MODPATH . 'cache', // Caching with multiple backends
			'database' => MODPATH . 'database', // Database access
			'image' => MODPATH . 'image', // Image manipulation
			'pagination' => MODPATH . 'pagination', // Pagination
			'minify' => MODPATH . 'minify', // Minify
			'orm' => MODPATH . 'orm', // Object Relationship Mapping
			'email' => MODPATH . 'email', // Email
		);

		// Start a new list of include paths, default is,
		if (defined('THEMEPATH')) {
			$paths = array(THEMEPATH);
		}

		// Oxygen plugins
		foreach ($modules as $name => $path) {
			if (is_dir($path)) {
				// Add the module to include paths
				$paths[] = $modules[$name] = realpath($path) . DIRECTORY_SEPARATOR;
			}
			else {
				// This module is invalid, remove it
				unset($modules[$name]);
			}
		}

		// Add the APPPATH and OXYPATH
		$paths[] = APPPATH;
		$paths[] = OXYPATH;

		// Core modules
		foreach ($core_modules as $name => $path) {
			if (is_dir($path)) {
				// Add the module to include paths
				$paths[] = $core_modules[$name] = realpath($path) . DIRECTORY_SEPARATOR;
			}
			else {
				// This module is invalid, remove it
				unset($core_modules[$name]);
			}
		}

		// Finish the include paths by adding SYSPATH
		$paths[] = SYSPATH;

		// Set the new include paths
		Oxygen::$_paths = $paths;
		Kohana::$_paths = $paths;

		// Set the current module list
		Oxygen::$_modules = array_merge($core_modules, $modules);

		foreach (Oxygen::$_modules as $path) {
			$init = $path . 'init' . EXT;

			if (is_file($init)) {
				// Include the module initialization file once
				require_once $init;
			}
		}

		return Oxygen::$_modules;
	}

	/**
	 * Builds a GUID. Calls the same filter as the guid() method on OModel.
	 *
	 * @static
	 * @param  mixed  $class  class object
	 * @return string
	 */
	public static function guid(&$class) {

		$class_name = get_class($class);

		$data = OHooks::instance()->filter(
			$class_name.'.guid',
			array(
				'guid' => $class_name.'_'.$class->pk(),
				'id' => $class->pk(),
				'class' => $class_name,
			)
		);

		return $data['guid'];
	}

	/**
	 * Strips the GUID and returns the ID.
	 *
	 * @static
	 * @param  mixed   $class  class object
	 * @param  string  $guid   guid to strip down to id
	 * @return mixed
	 */
	public static function strip_guid(&$class, $guid) {
		return str_replace(get_class($class).'_', '', $guid);
	}

	/**
	 * Builds the navigation menus for the headers
	 *
	 * @static
	 * @throws Kohana_Exception
	 * @param  string  $nav_key  navigation key
	 * @return string
	 */
	public static function build_header_nav($nav_key) {
		$output = '';
		$nav = Oxygen::config('oxygen')->get($nav_key);
		foreach ($nav['order'] as $tab) {
			// Skip tabs that have been defined in the config array
			if (!isset($nav[$tab])) {
				$model = 'Model_' . $tab;

				$class = new ReflectionClass($model);
				if (!$class->isAbstract()) {
					$model = new $model;

					try {
						if ($class->hasMethod('nav_menu')) {
							$nav_menu = $class->getMethod('nav_menu')->invoke($model, array());
							if (!empty($nav)) {
								$nav[$tab] = $nav_menu;
							}
						}
					}
					catch (Exception $e) {
						throw new Kohana_Exception('You have not defined nav_menu() for the model: ' . $tab);
					}

					try {
						if (!isset($nav[$tab]['permissions']) && $class->hasMethod('permissions')) {
							$nav[$tab]['permissions'] = $class->getMethod('permissions')->invoke($model, array());
						}
					}
					catch (Exception $e) {
						throw new Kohana_Exception('You have not defined permissions() for the model: ' . $tab);
					}

					try {
						if ($class->hasMethod('meta') && $class->hasMethod('url') ) {
							if (!isset($nav[$tab]['url'])) {
								$nav[$tab]['url'] = $model->url('list');
							}

							if (!isset($nav[$tab]['text'])) {
								$nav[$tab]['text'] = $class->getMethod('meta')->invoke($model, 'mult_text');
							}
						}
					}
					catch (Exception $e) {
						throw new Kohana_Exception('You have not defined meta() for the model: ' . $tab);
					}
				}


				unset($class);
			}

			if (isset($nav[$tab])) {
				$access = false;
				if (!count($nav[$tab]['permissions'])) {
					$access = true;
				}
				else {
					foreach ($nav[$tab]['permissions'] as $permission) {
						if (is_array($permission)) {
							$config = Oxygen::config('roles');
							foreach ($config as $key => $display) {
								if (Auth::instance()->has_permission($permission[0], $permission[1], $key)) {
									$access = true;
									break;
								}
							}
						}
					}
				}

				// User has access to view this tab
				if ($access) {
					$attributes = array();
					if (Request::current()->controller() == $nav[$tab]['url']) {
						$attributes = array('class' => 'current');
					}

					$output .= '<li>' . HTML::anchor($nav[$tab]['url'], $nav[$tab]['text'], $attributes);
				}
			}
		}

		return $output;
	}

	/**
	 * Loads the list of models.
	 *
	 * @static
	 * @return array
	 */
	public static function find_models() {
		$paths = Kohana::include_paths();

		$models = array();
		foreach ($paths as $path) {
			$dir = $path . 'classes/model/';
			$models = array_merge($models, self::_find_models($dir, $dir));
		}
		$models = array_unique($models);

		return $models;
	}

	/**
	 * Recursive function to find models.
	 *
	 * @static
	 * @param  string  $path  path to recurse
	 * @param  string  $base  base path we started from
	 * @return array
	 */
	protected static function _find_models($path, $base = '') {
		$models = array();
		if (is_dir($path)) {
			if (($dh = opendir($path)) !== false) {
				while (($item = readdir($dh)) !== false) {
					$check = $path.$item;
					if (is_file($check)) {
						$model_name = str_replace($base, '', $path).substr($item, 0, strrpos($item, '.'));
						$models[] = str_replace('/', '_', $model_name);
					}
					else if (is_dir($check) && !in_array($item, array('.', '..', 'oxygen'))) {
						$models = array_merge($models, self::_find_models($check.'/', $base));
					}
				}
			}
		}
		return $models;
	}

	/**
	 * Sets the permissions to the config.
	 *
	 * @static
	 * @throws Oxygen_Config_Exception
	 * @return void
	 */
	public static function set_permissions() {

		$permissions = array();

		$roles_config = Oxygen::config('roles');
		foreach (Oxygen::find_models() as $model) {
			if (empty($model)) {
				continue;
			}
			$path = Kohana::find_file('classes/model', str_replace('_', '/', $model));
			if (!empty($path) && !isset($permissions[$model])) {
				try {
					$model = 'Model_' . $model;
					$class = new ReflectionClass($model);
					if (!$class->isAbstract() && $class->hasMethod('permissions')) {
						$perms = $class->getMethod('permissions')->invoke(new $model, array());
						if (!empty($perms)) {
							foreach ($perms as $key => $_perms) {
								if (!isset($roles_config[$key])) {
									throw new Oxygen_Config_Exception('Invalid permissions key :key in :model.', array(
										':key' => $key,
										':model' => $model,
									));
								}
							}
							$permissions[$model] = $perms;
						}
					}
				}
				catch (Exception $e) {
					self::log('Exception: $e');
				}

				// Clean up
				unset($class);
			}
		}

		ksort($permissions);
		Oxygen::config('oxygen')->set('permissions', $permissions);
	}

	public static function deprecated($msg = '') {
		if (Oxygen::$environment === Oxygen::PRODUCTION) {
			return;
		}
		$trace = debug_backtrace();

		$log = '## DEPRECATED OXYGEN CALL: ';
		if (!empty($trace[1]['class'])) {
			$log .= $trace[1]['class'].'::';
		}
		$log .= $trace[1]['function'].'()';
		error_log($log);
		error_log(' | REASON: '.$msg);
	}

	public static function log($msg = '') {
		if (Oxygen::$environment === Oxygen::PRODUCTION) {
			return;
		}
		error_log('## OXYGEN LOG: '.$msg);

		$trace = debug_backtrace();
		if (!empty($trace[1]['file'])) {
			error_log(' | file:  '.$trace[1]['file']);
		}
		if (!empty($trace[1]['line'])) {
			error_log(' | line:  '.$trace[1]['line']);
		}
	}

} // End Oxygen_Core
