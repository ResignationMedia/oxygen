<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Theme {

	/**
	 * @var  OTheme  instance
	 */
	public static $instance;

	/**
	 * Creates an instance of OTheme
	 *
	 * @return OTheme
	 */
	public static function instance() {
		if (self::$instance === null) {
			$theme = Oxygen::config('oxygen')->preference('theme');
			if ($theme == null) {
				$theme = 'default';
			}
			self::$instance = new self($theme);
		}

		return self::$instance;
	}

	/**
	 * @var  string  current theme
	 */
	public $theme = '';

	/**
	 * @var  array  available themes
	 */
	protected $themes = array();

	/**
	 * @var  array  resources
	 */
	protected $resources = array();

	/**
	 * Loads the themes directory.
	 */
	public function __construct($theme = 'default') {
		$this->theme = $theme;
		define('THEMEPATH', DOCROOT.'themes'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR);

		// Init the theme
		if (is_file(THEMEPATH.'init'.EXT)) {
			Oxygen::load(THEMEPATH.'init'.EXT);
		}
	}

	/**
	 * Builds an array of the themes for select menus.
	 *
	 * @return array
	 */
	public function options() {

		$options = array();
		$this->themes = $this->find_all();
		foreach ($this->themes as $key => $theme) {
			$options[$key] = $theme['name'];
		}

		return $options;
	}

	/**
	 * Finds all installed themes.
	 *
	 * @return array
	 */
	public function find_all() {
		if (!count($this->themes)) {
			// Load the themes directory
			$path = DOCROOT.'themes';
			$dirs = scandir(DOCROOT.'themes');
			foreach ($dirs as $dir) {
				if ($dir === '.' || $dir === '..' || substr($dir, 0, 1) === '.') continue;

				$_dir = $path.'/'.$dir.'/';
				if (is_dir($_dir)) {
					// Find the config files
					$config = $_dir.'config/';
					$theme = Kohana::load($config.'theme.php');

					// Set the theme
					if (!empty($theme)) {
						$this->themes[$dir] = $theme;
					}
				}
			}
		}

		return $this->themes;
	}

	/**
	 * Builds the path to the specified file
	 *
	 * @static
	 * @param  string  $dir   directory
	 * @param  string  $file  file name
	 * @param  string  $ext   file extensions
	 * @return string
	 */
	public static function find_file($dir, $file, $ext = 'php') {
		$dir = 'assets/'.$dir;
		if (($path = Kohana::find_file($dir, $file, $ext)) !== false) {
			return str_replace(DOCROOT, '', $path);
		}

		return false;
	}

} // End Oxygen_Theme
