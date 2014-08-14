<?php defined('SYSPATH')  || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Request extends Kohana_Request {

	/**
	 * Redirects as the request response. If the URL does not include a
	 * protocol, it will be converted into a complete URL.
	 *
	 *     $request->redirect($url);
	 *
	 * [!!] No further processing can be done after this method is called!
	 *
	 * @param   string   $url   Redirect location
	 * @param   integer  $code  Status code: 301, 302, etc
	 * @return  void
	 * @uses    URL::site
	 * @uses    Request::send_headers
	 */
	public function redirect($url = '', $code = 302) {
		if (Kohana::$index_file) {
			$url = URL::site_reverse($url);
		}

		parent::redirect($url, $code);
	}

	/**
	 * Checks to see if the current route is a public route.
	 *
	 * @param  string  $uri  URI to check
	 * @return bool
	 */
	public function public_route($uri = null) {
		if (is_null($uri)) {
			$uri = $this->uri();
		}

		foreach (Oxygen::config('oxygen')->get('public_routes') as $route) {
			if ($uri == $route) {
				// Valid route
				return true;
			}

			// Check wildcard URLs
			$wild = strpos($route, '*');
			if ($wild !== false) {
				$check = substr($route, 0, $wild);
				if (strpos($uri, $check) !== false) {
					// Valid route
					return true;
				}
			}
		}

		return false;
	}

} // End Oxygen_Request
