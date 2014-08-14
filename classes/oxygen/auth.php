<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Auth {

	/**
	 * @var  Auth
	 */
	public static $instance;

	/**
	 * Singleton pattern
	 *
	 * @return Auth
	 */
	public static function instance() {
		if (self::$instance === null) {
			// Create a new session instance
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Create an instance of Auth.
	 *
	 * @return Auth
	 */
	public static function factory() {
		return new Auth;
	}

	/**
	 * @var  Session
	 */
	protected $_session;

	/**
	 * @var Phpass instance
	 */
	protected $_phpass;

	/**
	 * @var  string  session key
	 */
	protected $_session_key = 'auth_user';

	/**
	 * @var  string  the generated password
	 */
	protected $_new_password = '';

	/**
	 * Loads Session and configuration options.
	 */
	public function __construct() {
		require Kohana::find_file('vendor/phpass', 'PasswordHash');
		$this->_session = Session::instance();
		$this->_phpass = new PasswordHash(8, true);
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param  boolean  $destroy     completely destroy the session
	 * @param  boolean  $logout_all  remove all tokens for user
	 * @return boolean
	 */
	public function logout($destroy = false, $logout_all = false) {
		// Cookie set?
		if (($token = Cookie::get('oxygen_login')) !== null) {
			// Delete the oxygen_login cookie to prevent re-login
			Cookie::delete('oxygen_login');

			// Clear the oxygen_login token from the database
			$token = OModel::factory('user_token', array('token' => $token))->find();

			if ($token->loaded()) {
				if ($logout_all) {
					OModel::factory('user_token')->where('user_id', '=', $token->user_id)->delete_all();
				}
				else {
					$token->delete();
				}
			}
		}

		// Destroy the session completely?
		if ($destroy === true) {
			$this->_session->destroy();
		}
		else {
			// Remove the user form the session
			$this->_session->delete($this->_session_key);

			// Regenerate the session id
			$this->_session->regenerate();
		}

		// Double check
		return !$this->logged_in();
	}

	/**
	 * Checks if a session is active.
	 *
	 * @return bool
	 */
	public function logged_in() {
		$status = false;
		$user = $this->get_user();

		if (is_object($user) && $user instanceof Model_User && $user->loaded()) {
			// Everything is okay so far
			$status = true;
		}

		return $status;
	}

	/**
	 * Logs a user in.
	 *
	 * @param  Model_User $user      user object
	 * @param  boolean    $remember  enable autologin
	 * @return boolean
	 */
	public function login($identifier, $password, $remember = true) {
		$user = OModel::factory("User");
		$user->where($user->unique_key($identifier), '=', $identifier)->find();
		if ($user->loaded() && $user->enabled() && $this->check_password($password, $user->password)) {
			if ($remember === true) {
				// Create the autlogin token
				$token = OModel::factory('User_Token');

				// Set token data
				$remember_me_days = Oxygen::config('oxygen')->get('remember_me_days');
				$lifetime = ($remember_me_days < 1 ? 14 : $remember_me_days) * 86400;
				$token->user_id = $user->id;
				$token->expires = time()+$lifetime;
				$token->save();

				// Set the autologin cookie
				Cookie::set('oxygen_login', $token->token, $lifetime);
			}

			// Complete login
			$this->complete_login($user);

			return true;
		}

		// Login failed
		return false;
	}

	/**
	 * Set info to enable password reset, and email link to reset form
	 *
	 * @param  string  $identifier  email or username of user
	 * @return bool
	 */
	public function forgot_password($identifier) {
		$user = OModel::factory('User');
		$user->where($user->unique_key($identifier), '=', $identifier)->find();

		if ($user->loaded() && $user->enabled()) {
			$user->password_key = md5(microtime().Oxygen::config('oxygen')->get('salt'));
			$user->password_change = 1;
			$user->update();

			$email = Email::factory(Oxygen::config('oxygen')->get('app_name').': Password Reset')
				->to($user->email)
				->from(Oxygen::config('oxygen')->get('email_from'), Oxygen::config('oxygen')->get('app_name'))
				->message(View::factory('auth/email/forgot_password', array(
					'user' => $user->as_array()
				)));

			return $email->send();
		}

		return false;
	}

	/**
	 * Gets the currently logged in user from the session.
	 * Returns false if no user is currently logged in.
	 *
	 * @param  boolean  $auto_login  check for auto login?
	 * @return Model_User|bool
	 */
	public function get_user($auto_login = true) {
		// Check for "remembered" login
		$user = $this->auto_login();
		if ($user === false) {
			$user = OModel::factory('User', $this->_session->get($this->_session_key, 0));
			if (!$user->loaded()) {
				$user = false;
			}
		}

		return $user;
	}

	/**
	 * Complete the login for a user by incrementing the logins and setting
	 * session data: user_id, username, roles.
	 *
	 * @param  Model_User  $user  user object
	 * @return Session
	 */
	public function complete_login(Model_User $user) {
		if (!$user->loaded()) {
			// nothing to do
			return;
		}

		// Set the last login date
		$user->last_login = date('Y-m-d H:i:s');
		$user->password_key = '';

		// Save the user
		$user->audit_status(OAudit::OFF)
			->update();

		// Regenerate the session
		$this->_session->regenerate();
		$success = $this->_session->set($this->_session_key, $user->id);

		if ($success) {
			OHooks::instance()->event('auth_post_login');
		}

		return $success;
	}

	/**
	 * Logs a user in, based on the oxygen_login cookie.
	 *
	 * @return mixed
	 */
	private function auto_login() {
		if (($token = Cookie::get('oxygen_login')) !== false) {
			// Load the token and user
			$token = new Model_User_Token(array('token' => $token));

			if ($token->loaded() && $token->user->loaded()) {
				if ($token->user_agent === sha1(Request::$user_agent)) {
					// Save the token to create a new unique token
					$token->save();

					// Set the new token
					Cookie::set('oxygen_login', $token->token, $token->expires-time());

					// Automatic login was successful
					$this->_session->set($this->_session_key, $token->user->id);
					return $token->user;
				}

				// Token is invalid
				$token->delete();
			}
		}

		return false;
	}

	/**
	 * Returns the classes the current user has access to.
	 *
	 * @return array
	 */
	public function accessible_classes() {
		// Super admin?
		if ($this->super_admin()) {
			$permissions = Oxygen::config('oxygen')->get('permissions');
			return array_keys($permissions);
		}

		$permissions = $this->permissions();
		if (empty($permissions) || !is_array($permissions) || !count($permissions)) {
			return array();
		}

		return array_keys($permissions);
	}

	/**
	 * Gets the permissions for the current user.
	 *
	 * @return mixed
	 */
	public function permissions() {
		$permissions = array();
		$user = $this->get_user();
		if ($user) {
			$permissions = $user->get_permissions();
		}
		return $permissions;
	}

	/**
	 * Sets the permissions for the current user.
	 *
	 * @param  int     $user_id      user ID
	 * @param  array   $permissions  array of permissions
	 * @param  string  $type         permissions type
	 */
	public function set_permissions($user_id, array $permissions, $type = 'system') {
		// Delete the current user's permissions.
		OModel::factory('permission')
			->where('user_id', '=', $user_id)
			->where('type', '=', $type)
			->delete_all();

		// Custom permissions?
		if (count($permissions)) {
			foreach ($permissions as $group => $keys) {
				foreach ($keys as $key) {
					$perms = OModel::factory('permission');
					$perms->id = 0;
					$perms->user_id = $user_id;
					$perms->group = $group;
					$perms->key = $key;
					$perms->type = $type;
					$perms->save();
				}
			}
		}
	}

	/**
	 * Does the user have permission for this key/group combination?
	 *
	 * @param  string  $perm_key    permission name
	 * @param  string  $group  permission group
	 * @param  string  $type  permission type
	 * @return bool
	 */
	public function has_permission($perm_key, $group = 'application', $type = 'system') {
		if (($user = $this->get_user()) !== false) {
			if ($this->super_admin()) {
				return true;
			}
			
			$permissions = $user->get_permissions();

			// allow group or model to be passed as group
			if (is_object($group)) {
				$group = get_class($group);
			}
			$group = strtolower(str_replace('Model_', '', $group));

			// check for requested permission
			if (isset($permissions[$type]) &&
				isset($permissions[$type][$group]) &&
				in_array($perm_key, $permissions[$type][$group])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Is the current user a super admin?
	 *
	 * @return bool
	 */
	public function super_admin() {
		$user = $this->get_user();
		if ($user->role_id == 1 || in_array($user->username, Oxygen::config('oxygen')->get('superadmins'))) {
			return true;
		}

		return false;
	}

	/**
	 * Generates a hashed password.
	 *
	 * @return string
	 */
	public function generate_password() {
		$this->_new_password = str_replace(array('0', 'o', '1', 'l'), array('x'), substr(strtolower(md5(microtime())), 0, 8));
		return $this->hash_password($this->_new_password);
	}

	/**
	 * Retrieves Generated Password
	 *
	 * @return string
	 */
	public function get_generated_password() {
		return $this->_new_password;
	}

	/**
	 * Hashes a string in accordance with password hashing standards (using phpass vendor library).
	 *
	 * @param string plaintext string to be hashed
	 * @return string hashed version of the plaintext string
	 */
	public function hash_password($string) {
		return $this->_phpass->HashPassword($string);
	}

	/**
	 * Check if a plaintext password matches a hashed version
	 *
	 * @param string plaintext string
	 * @param string proposed hash version
	 * @return bool Whether or not the plaintext matches the hash
	 */
	public function check_password($plaintext, $hash) {
		return $this->_phpass->CheckPassword($plaintext, $hash);
	}

} // End Oxygen_Auth
