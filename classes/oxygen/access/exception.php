<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Access_Exception extends Exception {

	/**
	 * Creates a new translated exception.
	 *
	 *	 throw new Oxygen_Access_Exception('Access denied. You do not have permissions to view :url.',
	 *		 array(':url' => '/some/protected/path'));
	 *
	 * @param  string	$message    error message
	 * @param  array	$variables  translation variables
	 * @param  integer  $code       the exception code
	 */
	public function __construct($message = null, array $variables = null, $code = 0) {
		// Set the message
		if ($message === null) {
			$message = 'You do not have permission to access this page.';
		}
		$message = __($message, $variables);

		// Pass the message to the parent
		parent::__construct($message, $code);
	}

	/**
	 * Magic object-to-string method.
	 *
	 *	 echo $exception;
	 *
	 * @return string
	 * @uses Kohana_Exception::text
	 */
	public function __toString() {
		return Kohana_Exception::text($this);
	}

} // End Kohana_Exception
