<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright  (c) Crowd Favorite. All Rights Reserved.
 * @package    Oxygen
 * @subpackage Controllers
 *
 * @property Request $request
 * @property Response $response
 * @property Session $session
 */
abstract class Controller_Oxygen_Protected extends Controller_Oxygen {

	/**
	 * Verifies user authentication, if they are not verified a login form will
	 * be displayed.
	 *
	 * @return void
	 */
	public function before() {
		try {
			if (Oxygen::$has_config && !$this->is_public_uri()) {
				Oxygen::set_permissions();

				// Set active models and load the user's preferences.
				if (Auth::instance()->logged_in()) {
					$models = array();
					foreach (Oxygen::config('oxygen')->get('permissions') as $model => $data) {
						$key = str_replace('Model_', '', $model);
						if (Auth::instance()->has_permission('view', $model)) {
							$models[$key] = Inflector::humanize($key, true);
						}
					}
					Oxygen::config('oxygen')->set('active_models', $models);
				}
			}
		}
		catch (Database_Exception $e) {
			// Don't do anything, chances are the system hasn't been installed yet.
		}

		parent::before();

		if (!$this->is_public_uri() && Auth::instance()->logged_in() === false) {
			// Store the post data in the session
			if (!Arr::get($_POST, 'login')) {
				if (count($_POST)) {
					$this->session->set('post', json_encode($_POST));
				}

				if (count($_FILES)) {
					$this->session->set('files', json_encode($_FILES));
				}
			}

			// Skip the controller action
			$this->auto_render = false;
			$this->response->body(Request::factory('auth/login')->query(array(
				'redirect_uri' => urlencode($this->request->uri())
			))->execute()->body());
			$this->request->action('protected');
		}
		else {
			// Check for previously $_POST data
			$post = $this->session->get('post');
			$files = $this->session->get('files');
			if (!empty($post) || !empty($files)) {
				if (!empty($post)) {
					$_POST = Arr::merge($_POST, json_decode($post));
					$this->session->delete('post');
				}

				if (!empty($files)) {
					$_FILES = Arr::merge($_FILES, json_decode($files));
					$this->session->delete('files');
				}
			}
			// Check for password_change setting
			$user = Auth::instance()->get_user();
			if (!empty($user->password_change) && $this->request->uri() != 'profile/password') {
				Msg::add('info', 'You must change your password.');
				$this->request->redirect('profile/password');
			}
		}
	}

	/**
	 * Method that helps us skip over controller actions if the user is logged out.
	 *
	 * @return void
	 */
	public function action_protected() {
		// Do nothing...
		return;
	}

	/**
	 * URIs to skip authentication.
	 *
	 * @return array
	 */
	protected function public_uris() {
		return array();
	}

	/**
	 * Check URI to skip authentication.
	 *
	 * @param  string  $uri
	 * @return bool
	 */
	public function is_public_uri($uri = null) {
		if (is_null($uri)) {
			$uri = $this->request->uri();
		}

		foreach ($this->public_uris() as $key) {
			$route = Route::get($key);
			if ($route->matches($uri)) {
				return true;
			}
		}
		return false;
	}

} // End Controller_Oxygen_Protected
