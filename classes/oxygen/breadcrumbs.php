<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Breadcrumbs {

	/**
	 * Creates the Breadcrumbs object.
	 *
	 * @param  array  $config  configuration
	 * @return Breadcrumbs
	 */
	public static function factory(array $config = array()) {
		return new Breadcrumbs($config);
	}

	/**
	 * @var  string  page title
	 */
	protected $_title = null;

	/**
	 * @var  bool  is the page title already HTML encoded?
	 */
	public $title_encoded = false;

	/**
	 * @var  array  breadcrumbs
	 */
	protected $_crumbs = array();

	/**
	 * Magic method, returns the output of [Breadcrumbs::render].
	 *
	 * @return  string
	 * @uses    Breadcrumbs::render
	 */
	function __toString() {
		try {
			return $this->render();
		}
		catch (Exception $e) {
			// Display the exception message
			Kohana_Exception::handler($e);

			return '';
		}
	}

	/**
	 * Sets the page title.
	 *
	 * @chainable
	 * @param  string  $title  title
	 * @return Breadcrumbs
	 */
	public function title($title = null) {
		if ($title === null) {
			return $this->_title;
		}

		$this->_title = str_replace(HTML::global_title(), '', $title);
		return $this;
	}

	/**
	 * Removes all bookmarks.
	 *
	 * @chainable
	 * @return void
	 */
	public function clear() {
		$this->_crumbs = array();
		return $this;
	}

	/**
	 * Adds a bookmark to the crumbs.
	 *
	 * @chainable
	 * @param  Bookmark  $bookmark  Bookmark object to add
	 * @return Breadcrumbs
	 */
	public function add(Bookmark $bookmark) {
		$this->_crumbs[] = $bookmark;
		return $this;
	}

	/**
	 * Deletes a breadcrumb from the stack.
	 *
	 * @chainable
	 * @param  string  $key  Bookmark key to delete
	 * @return Breadcrumbs
	 */
	public function delete($key) {
		$crumbs = array();
		foreach ($this->_crumbs as $crumb) {
			if ($crumb->title() != $key) {
				$crumbs[] = $crumb;
			}
		}

		$this->_crumbs = $crumbs;
		return $this;
	}

	/**
	 * Renders the breadcrumbs output.
	 *
	 * @return string
	 */
	public function render() {
		$crumbs = array();
		if (count($this->_crumbs)) {
			foreach ($this->_crumbs as $bookmark) {
				$crumbs[] = $bookmark->render();
			}
		}

		$crumbs[] = ($this->title_encoded ? $this->title() : HTML::chars($this->title()));
		return implode(' <span>&rsaquo;</span> ', $crumbs);
	}

	/**
	 * Adds/gets the current crumbs.
	 *
	 * @param  array  $crumbs
	 * @return array|Oxygen_Breadcrumbs
	 */
	public function crumbs(array $crumbs = null) {
		if ($crumbs === null) {
			return $this->_crumbs;
		}

		$this->_crumbs = $crumbs;
		return $this;
	}

} // End Oxygen_Breadcrumbs
