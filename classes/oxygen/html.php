<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_HTML extends Kohana_HTML {

	/**
	 * Create HTML link anchors. Note that the title is not escaped, to allow
	 * HTML elements within links (images, etc).
	 *
	 *	 echo HTML::anchor('profile', 'My Profile');
	 *
	 * @param   string   $uri         URL or URI string
	 * @param   string   $title       link text
	 * @param   array	 $attributes  HTML anchor attributes
	 * @param   mixed    $protocol    protocol to pass to URL::base()
	 * @param   boolean  $index       include the index page
	 * @return  string
	 * @uses URL::base
	 * @uses URL::site
	 * @uses HTML::attributes
	 */
	public static function anchor($uri, $title = null, array $attributes = null, $protocol = null, $index = true) {
		if ($protocol === null) {
			$protocol = Request::current();
		}

		if (substr($uri, 0, 1) != '#' && strpos($uri, '://') === false) {
			$uri = URL::site($uri);
		}

		return parent::anchor($uri, $title, $attributes, $protocol, $index);
	}

	/**
	 * Creates a style sheet link element.
	 *
	 *     echo HTML::style('media/css/screen.css');
	 *
	 * @param   string   file name
	 * @param   array    default attributes
	 * @param   mixed    protocol to pass to URL::base()
	 * @param   boolean  include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function style($file, array $attributes = null, $protocol = null, $index = false) {
		if (strpos($file, '://') === false) {
			$file = URL::site($file);
		}

		return parent::style($file, $attributes, $protocol, $index);
	}

	/**
	 * Creates a script link.
	 *
	 *     echo HTML::script('media/js/jquery.min.js');
	 *
	 * @param   string   file name
	 * @param   array    default attributes
	 * @param   mixed    protocol to pass to URL::base()
	 * @param   boolean  include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function script($file, array $attributes = null, $protocol = null, $index = false) {
		if (strpos($file, '://') === false) {
			$file = URL::site($file);
		}

		return parent::script($file, $attributes, $protocol, $index);
	}


	/**
	 * Compile the HTML title
	 *
	 * @static
	 * @param  string  $title  content to append before the default title
	 * @return string
	 * @uses HTML::global_title
	 */
	public static function title($title = '') {
		return $title.HTML::global_title();
	}

	/**
	 * Gets the global title, which is appended at the end of
	 * every page title.
	 *
	 * @static
	 * @return string
	 */
	public static function global_title() {

		return ' | '.Oxygen::config('oxygen')->get('app_name');
	}

	/**
	 * Adds a class to an existing string of classes.
	 *
	 * @static
	 * @param  string  $class    CSS className to add
	 * @param  string  $classes  existing collection of classNames
	 * @return string
	 */
	public static function add_class($class, $classes) {

		$class_array = explode(' ', $classes);
		$queue = array();
		foreach ($class_array as $existing) {
			if (!empty($existing)) {
				$queue[] = $existing;
			}
		}
		$queue[] = $class;
		return implode(' ', array_unique($queue));
	}

	/**
	 * Removes a class from an existing string of classes.
	 *
	 * @static
	 * @param  string  $class    CSS className to remove
	 * @param  string  $classes  existing collection of classNames
	 * @return string
	 */
	public static function remove_class($class, $classes) {

		$class_array = explode(' ', $classes);
		$queue = array();
		foreach ($class_array as $existing) {
			if (!empty($existing) && $existing != $class) {
				$queue[] = $existing;
			}
		}

		return implode(' ', $queue);
	}

	/**
	 * Toggles a class.
	 *
	 * @static
	 * @param  string  $class    CSS className to toggle
	 * @param  string  $classes  existing collection of classNames
	 */
	public static function toggle_class($class, $classes) {

		$class_array = explode(' ', $classes);
		if (in_array($class, $class_array)) {
			HTML::remove_class($class, $classes);
		}
		else {
			HTML::add_class($class, $classes);
		}
	}

} // End Oxygen_HTML
