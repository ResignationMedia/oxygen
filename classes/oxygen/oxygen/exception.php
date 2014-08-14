<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

class Oxygen_Oxygen_Exception extends Kohana_Kohana_Exception {

	/**
	 * Handles system exceptions.
	 *
	 * @static
	 * @param  Exception  $e
	 * @return bool
	 */
	public static function handler(Exception $e) {
		if (Oxygen::$environment === Oxygen::PRODUCTION) {
			switch (get_class($e)) {
				case 'Oxygen_Install_Exception':
					echo View::factory('install/error', array(
						'message' => $e->getMessage()
					));
				break;
				case 'Oxygen_Access_Exception':
					$response = new Response;
					$response->status(403);
					$view = View::factory('errors/403', array(
						'message' => $e->getMessage()
					));
					echo $response->body($view)->send_headers()->body();
					return true;
				break;
				case 'HTTP_Exception_404':
					$response = new Response;
					$response->status(404);
					$view = View::factory('errors/404', array(
						'message' => $e->getMessage()
					));
					echo $response->body($view)->send_headers()->body();
					return true;
				break;
				case 'Database_Exception':
					echo View::factory('errors/database');
				break;
				default:
					echo View::factory('errors/general', array(
						'message' => $e->getMessage()
					));
					return true;
				break;
			}
		}
		else {
			return Kohana_Exception::handler($e);
		}
	}

} // End Oxygen_Exception_Handler
