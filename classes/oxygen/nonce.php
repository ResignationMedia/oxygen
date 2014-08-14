<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Nonce {

	/**
	 * @var  string  nonce action
	 */
	protected $_action = 'unknown';

	/**
	 * @var  string  nonce item id
	 */
	protected $_item_id = 'unknown';

	/**
	 * @var  int  nonce's timeout
	 */
	protected $_timeout = 0;

	/**
	 * @var  string|int  user id
	 */
	protected $_user_id = 'anonymous';

	/**
	 * @var  string  nonce secret
	 */
	protected $_secret = '';

	/**
	 * Creates a new Nonce object.
	 *
	 * @static
	 * @param  string  $action   form action
	 * @param  string  $item_id  OModel object ID
	 * @param  int     $timeout  Nonce lifetime
	 * @return Nonce
	 */
	public static function factory($action, $item_id = '', $timeout = 0) {
		return new Nonce($action, $item_id, $timeout);
	}

	/**
	 * Initializes the Nonce object.
	 *
	 * @throws Kohana_Exception
	 * @param  string  $action   form action
	 * @param  string  $item_id  OModel object ID
	 * @param  int     $timeout  Nonce lifetime
	 */
	public function __construct($action, $item_id = '', $timeout = 0) {
		// Set action
		$this->_action = $action;

		// Set item id
		if (!empty($item_id)) {
			$this->_item_id = $item_id;
		}

		// Set timeout
		if (!$timeout) {
			$this->_timeout = strtotime('+'.intval(Oxygen::config('oxygen')->get('nonce_timeout')).' hours', time());
		}

		// User ID
		if (($user = Auth::instance()->get_user()) !== false) {
			$this->_user_id = $user->id;
		}

		// Set the secret
		$this->_secret = Oxygen::config('oxygen')->get('nonce_salt');
		if (empty($this->_secret)) {
			throw new Kohana_Exception('Configuration error (missing nonce salt).');
		}
	}

	/**
	 * Generates the nonce string.
	 *
	 * @return string
	 */
	public function generate() {
		return $this->_hash().'_'.$this->_timeout;
	}

	/**
	 * Validates the provided nonce.
	 *
	 * @param  string  $nonce  nonce string
	 * @return bool
	 */
	public function validate($nonce) {
		if (strpos($nonce, '_') === false) {
			return false;
		}

		$parts = explode('_', $nonce);
		$this->_timeout = $parts[1];
		if (time() > $this->_timeout) {
			return false;
		}

		$hash = $this->_hash();
		return ($parts[0] === $hash);
	}

	/**
	 * Utility method to verify the provided nonce is valid.
	 *
	 * @static
	 * @param  string  $action   form action
	 * @param  string  $item_id  OModel object ID
	 * @param  string  $nonce    Nonce
	 * @return bool
	 */
	public static function check($action, $item_id = '', $nonce = '') {
		if (empty($nonce)) {
			$nonce = Arr::get($_POST, 'nonce');
		}

		$test = Nonce::factory($action, $item_id);
		return $test->validate($nonce);
	}

	/**
	 * Utility method to verify the provided nonce is valid, if it is not then this
	 * will throw an exception.
	 *
	 * @static
	 * @throws Kohana_Exception
	 * @param  string  $action   form action
	 * @param  string  $item_id  OModel object ID
	 * @param  string  $nonce    Nonce
	 * @return bool
	 */
	public static function check_fatal($action, $item_id = '', $nonce = '') {
		if (!Nonce::check($action, $item_id, $nonce)) {
			throw new Kohana_Http_Exception_403('Sorry, this form appears to be invalid.');
		}

		return true;
	}

	/**
	 * Creates a hidden field object that is populated with a nonce string.
	 *
	 * @static
	 * @param  string  $action   form action
	 * @param  string  $item_id  OModel object ID
	 * @param  int     $timeout  Nonce lifetime
	 * @return OField
	 */
	public static function field($action, $item_id = '', $timeout = 0) {
		$value = Nonce::factory($action, $item_id, $timeout)->generate();
		return OField::factory('hidden')
			->name('nonce')
			->value($value);
	}

	/**
	 * Generates an sha1 hash for the nonce.
	 *
	 * @return string
	 */
	private function _hash() {
		return sha1($this->_secret.$this->_action.$this->_item_id.$this->_user_id.$this->_timeout);
	}

} // End Oxygen_Nonce
