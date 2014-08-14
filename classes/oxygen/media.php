<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Oxygen_Media {

	/**
	 * @var  string  default media directory
	 */
	public static $directory = 'uploads';

	/**
	 * @var  string  default media driver
	 */
	public static $driver = 'image';

	/**
	 * Creates a new Media object.
	 *
	 * @static
	 * @param  string  $file    path to temporary file
	 * @param  array   $config  configuration
	 * @param  string  $driver  media driver
	 * @return Media
	 */
	public static function factory($file, array $config, $driver = null) {

		if ($driver !== null) {
			Media::$driver = $driver;
		}

		$class = 'Oxygen_Media_'.Media::$driver;

		return new $class($file, $config);
	}

	/**
	 * @var  array  temporary $_FILE array
	 */
	protected $_temp = array();

	/**
	 * @var  string  file path
	 */
	protected $_path = '';

	/**
	 * @var  string  directory to save the image to.
	 */
	protected $_directory = '';

	/**
	 * @var  string  filename extension
	 */
	protected $_ext = '';

	/**
	 * @var  array  filename args
	 */
	protected $_args = array();

	/**
	 * Initializes the Media object.
	 *
	 * @param  string  $file    path to temporary file
	 * @param  array   $config  configuration settings
	 */
	public function __construct($file, array $config = array()) {
		$this->_temp = $file;

		// Set the config
		foreach ($config as $key => $val) {
			$this->$key = $val;
			if ($key == 'ext') {
				$this->_ext = $val;
			}
		}

		if (empty($this->_ext) && is_file($file)) {
			$info = pathinfo($file);
			$this->_ext = $info['extension'];
		}

		// Build the file path
		$this->_path = $this->path($this->_args, $this->_directory, $this->_ext);
	}

	/**
	 * Sets a local variable value.
	 *
	 * @throws Kohana_Exception
	 * @param  string  $name   driver variable key
	 * @param  string  $value  driver variable value
	 */
	public function __set($name, $value) {
		if (isset($this->$name)) {
			$this->$name = $value;
		}
		else if (isset($this->{'_'.$name})) {
			$this->{'_'.$name} = $value;
		}
		else {
			throw new Kohana_Exception('Invalid configuration key :key for Media driver :driver.', array(
				':key' => $name,
				':driver' => Media::driver()
			));
		}
	}

	/**
	 * Calls the driver specific save method.
	 *
	 * @return string
	 */
	public function save() {
		return $this->_save();
	}

	/**
	 * Builds the path for the uploaded file.
	 *
	 * @static
	 * @param  string  $directory  directory the file lives in
	 * @param  array   $args       args to be MD5ed to create the filename
	 * @param  string  $ext        file extension, default is jpg.
	 * @return string
	 */
	public static function path(array $args, $directory = null, $ext = 'jpg') {
		$filename = md5(implode('', $args).Oxygen::config('oxygen')->get('salt'));

		// Build the directories
		$dir = Media::directory().DIRECTORY_SEPARATOR;
		if ($directory !== null) {
			$dir .= $directory.DIRECTORY_SEPARATOR;
		}
		$dir .= substr($filename, 0, 3).DIRECTORY_SEPARATOR;
		$dir .= substr($filename, 3, 3).DIRECTORY_SEPARATOR;
		$dir .= substr($filename, 6, 3);

		// Add the filename to the path
		$path = $dir.'/'.$filename;
		if ($ext !== null) {
			$path .= '.'.$ext;
		}

		// Create the directory
		if (!is_dir(DOCROOT.$dir)) {
			Media::create_directory($path);
		}

		return $path;
	}

	/**
	 * Creates the upload directory for the media item.
	 *
	 * @static
	 * @param  string  $file  file name path
	 */
	public static function create_directory($file) {
		$directories = explode(DIRECTORY_SEPARATOR, $file);

		// Remove the filename itself
		unset($directories[(count($directories)-1)]);

		$directory = DOCROOT.Media::directory();
		foreach ($directories as $dir) {
			if ($dir != Media::directory()) {
				$directory .= DIRECTORY_SEPARATOR.$dir;
				if (!is_dir($directory)) {
					mkdir($directory, 0775);
				}
			}
		}
	}

	/**
	 * Returns the media directory.
	 *
	 * @static
	 * @return string
	 */
	public static function directory() {
		return self::$directory;
	}

	/**
	 * Returns the media driver.
	 *
	 * @static
	 * @return string
	 */
	public static function driver() {
		return self::$driver;
	}

	/**
	 * Saves the media.
	 *
	 * @abstract
	 */
	abstract protected function _save();

} // End Oxygen_Upload
