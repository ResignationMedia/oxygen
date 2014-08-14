<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Bookmark {

	/**
	 * @var  string  default title
	 */
	protected $_title = '';

	/**
	 * @var  string  default URL
	 */
	protected $_url = '';

	/**
	 * Creates an Bookmark object.
	 *
	 * @static
	 * @param  string  $url    bookmark URL
	 * @param  string  $title  link title
	 * @return Bookmark
	 */
	public static function factory($url, $title) {
		return new Bookmark($url, $title);
	}

	/**
	 * Builds the Bookmark object. This should always be called
	 * by [Bookmark::factory].
	 *
	 * @param  string  $url    bookmark URL
	 * @param  string  $title  link title
	 */
	public function __construct($url, $title) {
		$this->_url = $url;
		$this->_title = $title;
	}

	/**
	 * Magic method to force $this->render() when calling echo.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * Converts the object to a stdClass.
	 *
	 * @return object
	 */
	public function as_object() {
		return (object) array(
			'title' => $this->_title,
			'url' => $this->_url,
		);
	}

	/**
	 * Sets/gets the title for this bookmark.
	 *
	 * @param  string  $title
	 * @return Oxygen_Bookmark|string
	 */
	public function title($title = null) {
		if ($title === null) {
			return $this->_title;
		}

		$this->_title = $title;
		return $this;
	}

	/**
	 * Sets/gets the URL for this bookmark.
	 *
	 * @param  string  $url
	 * @return Oxygen_Bookmark|string
	 */
	public function url($url = null) {
		if ($url === null) {
			return $this->_url;
		}

		$this->_url = $url;
		return $this;
	}

	/**
	 * Builds the Bookmark URL
	 *
	 * @param  array  $attributes  link attributes
	 * @return string
	 */
	public function render(array $attributes = array()) {
		return HTML::anchor($this->_url, HTML::chars($this->_title), $attributes);
	}

} // End Oxygen_Bookmark
