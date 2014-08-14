<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright  (c) Crowd Favorite. All Rights Reserved.
 * @package    Oxygen
 * @subpackage Controllers
 *
 * @property Request $request
 * @property Response $response
 * @property Session $session
 * @property View $template
 */
class Controller_Oxygen_Auth extends Controller_Oxygen {

	/**
	 * @var bool Favorites enabled
	 */
	public $favorites_enabled = false;

	/**
	 * Forgot Password
	 *
	 * @return void
	 */
	public function action_forgot_password() {
		if (Auth::instance()->logged_in()) {
			// Logged in, redirect to the profile.
			$this->request->redirect('profile');
		}

		// Process the forgot password form
		if ($this->request->post('forgot')) {
			if (Auth::instance()->forgot_password($this->request->post('identifier'))) {
				Msg::add('confirm', 'An email has been sent to you with a link to reset your password.');
			}
			else {
				Msg::add('error', 'Invalid email/username.');
			}
		}

		// Generate the forgot password token
		$forgot_password_token = Security::token(true);

		// Show the forgot password form
		$this->views['header'] = '';
		$this->views['footer'] = '';
		$this->views['sidebar'] = '';
		$this->template->set(array(
			'title' => 'Forgot Password',
			'login' => true,
			'content' => View::factory('auth/forgot_password', array(
				'messages' => View::factory('chrome/messages'),
				'forgot_password_token' => $forgot_password_token
			))
		));
	}

	/**
	 * Log In
	 *
	 * @return void
	 */
	public function action_login() {
		$redirect = urldecode($this->request->query('redirect_uri') ?: Oxygen::config('oxygen')->get('login_target'));
		if (Auth::instance()->logged_in()) {
			// Logged in, redirect to the profile.
			$this->request->redirect($redirect);
		}

		// Login attempted?
		if ($this->request->post(md5("username".Security::token()))) {
			$username = $this->request->post(md5("username".Security::token()));
			$password = $this->request->post(md5("password".Security::token()));
			$remember = $this->request->post(md5("remember_me".Security::token()));

			if (Auth::instance()->login($username, $password, $remember)) {
				// Logged in!
				Msg::add('confirm', 'You have successfully been logged in.');
				if (!$this->request->is_ajax()) {
					$this->request->redirect($redirect);
				}
			}
			else {
				Msg::add('error', 'Invalid username and/or password.');
			}
		}

		if ($this->request->is_ajax()) {
			$this->template->set(array(
				'response' => Msg::get()
			));
			Msg::clear();
		}
		else {

			// Generate the login token
			$login_token = Security::token(true);

			// Show the login form
			$this->views['header'] = '';
			$this->views['footer'] = '';
			$this->views['sidebar'] = '';
			$this->template->set(array(
				'title' => 'Log In',
				'login' => true,
				'content' => View::factory('auth/login', array(
					'messages' => View::factory('chrome/messages'),
					'login_token' => $login_token
				))
			));
		}
	}

	/**
	 * Sets a new password.
	 *
	 * @return void
	 */
	public function action_new_password() {
		$key = $this->request->param('id');
		if (!$key) {
			$this->request->redirect('login');
		}

		$user = OModel::factory('User', array('password_key' => $key));
		if (!$user->loaded()) {
			Msg::add('error', 'Invalid token, please request a new one.');
			$this->request->redirect('forgot_password');
		}

		if ($this->request->post('save')) {
			$new_password = $this->request->post(md5('password'.Security::token()));
			$confirm_password = $this->request->post(md5('confirm_password'.Security::token()));

			if ($user->set_password($new_password, $confirm_password)) {
				Auth::instance()->complete_login($user);
				Msg::add('confirm', 'Your password has been changed - you are now logged in.');
				$this->request->redirect(Oxygen::config('oxygen')->get('login_target'));
			}
		}

		// Generate the login token
		$new_password_token = Security::token(true);

		// Show the forgot password form
		$this->views['header'] = '';
		$this->views['footer'] = '';
		$this->views['sidebar'] = '';
		$this->template->set(array(
			'title' => 'New Password',
			'login' => true,
			'content' => View::factory('auth/new_password', array(
				'messages' => View::factory('chrome/messages'),
				'new_password_token' => $new_password_token
			))
		));
	}

	/**
	 * Logout
	 *
	 * @return void
	 */
	public function action_logout() {
		Auth::instance()->logout(true);
		$redirect = urldecode($this->request->query('redirect_uri') ?: 'login');
		$this->request->redirect($redirect);
	}

} // End Controller_Oxygen_Auth
