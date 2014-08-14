<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 *
 * @property int $user_id
 * @property int $expires
 * @property int $created
 * @property string $user_agent
 * @property string $token
 *
 * @property Model_User $user
 */
abstract class Model_Oxygen_User_Token extends ORM {

	/**
	 * @var  bool  disable audits
	 */
	protected $_audit_status = OAudit::OFF;

	/**
	 * @var  array  updated column
	 */
	protected $_updated_column = false;

	/**
	 * @var  array  belongs to user
	 */
	protected $_belongs_to = array('user' => array());

	/**
	 * @var  int
	 */
	protected $_now;

	/**
	 * Handles garbage collection and deleting of expired objects.
	 *
	 * @return  void
	 */
	public function __construct($id = null) {

		parent::__construct($id);

		// Set the now, we use this a lot
		$this->_now = time();

		if (mt_rand(1, 100) === 1) {
			// Do garbage collection
			$this->delete_expired();
		}

		if ($this->expires !== null && $this->expires < $this->_now) {
			// This object has expired
			$this->delete();
		}
	}

	/**
	 * Overload saving to set the created time and to create a new token
	 * when the object is saved.
	 *
	 * @param  Validation  $validation  Validation object
	 * @return ORM
	 */
	public function save(Validation $validation = NULL) {

		if ($this->loaded() === false) {
			// Set the created time, token, and hash of the user agent
			$this->created = $this->_now;
			$this->user_agent = sha1(Request::$user_agent);
		}

		while (true) {
			$this->token = $this->create_token();
			$result = DB::select($this->_primary_key)
				->from($this->table_name())
				->where('token', '=', $this->token)
				->execute($this->_db);

			if (!$result->count()) {
				return parent::save();
			}
		}
	}

	/**
	 * Deletes all expired tokens.
	 *
	 * @return ORM
	 */
	public function delete_expired() {

		// Delete all expired tokens
		DB::delete($this->table_name())
			->where('expires', '<', $this->_now)
			->execute($this->_db);

		return $this;
	}

	/**
	 * Generate a new unique token.
	 *
	 * @return string
	 * @uses Text::random
	 */
	protected function create_token() {
		// Create a random token
		return Text::random('alnum', 32);
	}

} // End Model_Oxygen_User_Token
