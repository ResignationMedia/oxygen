<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_URL extends Kohana_URL {

	/**
	 * Builds the URL based on the action passed in.
	 *
	 * @static
	 * @param  OModel  $model   OModel object
	 * @param  string  $action  action
	 * @param  int     $id      ID
	 * @return string
	 */
	public static function action($model, $action = null, $id = null) {
		$url = $model->meta('mult').'/'.$action;
		if ($action !== null) {
			switch ($action) {
				case 'edit':
				case 'view':
				case 'delete':
				case 'clone':
				case 'reinstate':
					return $url.'/'.$id;
				case 'add':
					return $url;
					break;
			}
		}
		return $url;
	}

	/**
	 * Fetches an absolute site URL based on a URI segment.
	 *
	 *	 echo URL::site('foo/bar');
	 *
	 * @param   string   $uri       Site URI to convert
	 * @param   mixed    $protocol  Protocol string or [Request] class to use protocol from
	 * @param   boolean  $index     Include the index_page in the URL
	 * @return  string
	 * @uses	URL::base
	 */
	public static function site($uri = '', $protocol = null, $index = true) {
		if ($protocol === null) {
			$request = Request::current();
			if ($request->secure()) {
                $protocol = 'https';
            }
            else {
				$protocol = Oxygen::config('oxygen')->get('protocol', 'http');
			}
		}

		return parent::site($uri, $protocol, $index);
	}

	/**
	 * Removes the content generated by URL::site() from the URL.
	 *
	 * @static
	 * @param  string  $url  URL
	 * @return mixed
	 */
	public static function site_reverse($url) {
		return str_replace(URL::site(), '', $url);
	}

	/**
	 * Removes the content generated by URL::site() from the URL.
	 *
	 * @static
	 * @param  string  $url  URL
	 * @param  array  $params  key / value pairs to add as query params
	 * @return string
	 */
	public static function add_params($url, $params = array(), $sep = '&') {
		$params = http_build_query($params, '', $sep);
		$url .= (strpos($url, '?') === false ? '?' : $sep);
		return $url.$params;
	}

} // End Oxygen_URL
