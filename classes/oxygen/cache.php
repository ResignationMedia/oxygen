<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Cache {

	/**
	 * @var  OCache  instance
	 */
	public static $instance;

	/**
	 * Creates a singleton of a Oxygen Cache.
	 *
	 *	 // Create an instance
	 *	 $cache = OCache::instance();
	 *
	 * @return  OCache
	 */
	public static function instance() {
		if (self::$instance === null) {
			// Return the current group if initiated already
			self::$instance = new self;
		}

		// Return the instance
		return self::$instance;
	}

	/**
	 * @var  array  cached objects
	 */
	public $_cache = array();

	/**
	 * @var  array  cached collections
	 */
	public $_collections = array();

	/**
	 * Delete all cache entries.
	 *
	 *	 // Delete all cache entries
	 *	 OCache::instance()->delete_all();
	 *
	 * @return boolean
	 */
	public function delete_all() {
		$this->_cache = array();
		$this->_collections = array();
	}

	/**
	 * Delete a cache entry based on key
	 *
	 *	 // Delete 'foo' entry
	 *	 OCache::instance()->delete('foo');
	 *
	 * @param  string  $key          key to remove from cache
	 * @param  mixed   $collection  collection?
	 * @return boolean
	 */
	public function delete($key, $collection = false) {
		if (!$collection && isset($this->_cache[$key])) {
			unset($this->_cache[$key]);
		}
		else if (isset($this->_collections[$collection][$key])) {
			unset($this->_collections[$collection][$key]);
		}
	}

	/**
	 * Set a value to cache with id and lifetime
	 *
	 *	 $data = 'bar';
	 *
	 *	 // Set 'bar' to 'foo'
	 *	 OCache::instance()->set('foo', $data);
	 *
	 * @param  string  $key         key of cache entry
	 * @param  string  $data        data to set to cache
	 * @param  mixed   $collection  collection?
	 * @return boolean
	 */

	public function set($key, $data, $collection = false, $lifetime = 3600) {
		// Database_MySQL_Result objects contain resources which cannot be serialized
		// Results should be cached, not the Database_MySQL_Result object
		if (is_object($data) && get_class($data) == 'Database_MySQL_Result') {
			throw new Kohana_Exception('Unable to serialize Database_MySQL_Result for caching');
		}

		// Serialize then Unserialize creates copies of objects
		// Otherwise objects are passed by reference and can have strange side effects
		$data = serialize($data);
		if (!$collection) {
			$this->_cache[$key] = $data;
		}
		else {
			if (!isset($this->_collections[$collection])) {
				$this->_collections[$collection] = array(
					$key => $data
				);
			}
			else {
				$this->_collections[$collection][$key] = $data;
			}
		}
	}

	/**
	 * Retrieve a cached value entry by id.
	 *
	 *	 // Retrieve cache entry
	 *	 $data = OCache::instance()->get('foo');
	 *
	 * @param   string  $key         key of cache to entry
	 * @param   mixed	$collection  collection?
	 * @param   string  $default     default value to return if cache miss
	 * @return  mixed
	 */
	public function get($key = null, $collection = false, $default = null) {

		$data = false;
		if (!$collection && isset($this->_cache[$key])) {
			$data = $this->_cache[$key];
		}
		else {
			if (isset($this->_collections[$collection]) && isset($this->_collections[$collection][$key])) {
				$data = $this->_collections[$collection][$key];
			}
		}

		// Create a copy of $data that was passed into $this->set
		// Otherwise objects are passed by reference
		$data = unserialize($data);

		return !$data ? $default : $data;
	}

} // End Oxygen_Cache
