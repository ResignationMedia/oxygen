<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

class Oxygen_Msg {
	var $text;
	var $type;

	function __construct($type = 'info', $text = '', $css_class = '') {
		$this->text = $text;
		$this->type = $type;
		$this->css_class = $css_class;
	}

	function show($extra = '') {
		echo '<li class="'.$this->type.' '.$this->css_class.'"'.$extra.'><span>'.$this->text.'</span></li>'.PHP_EOL;
	}

	/**
	 * Add message to the session
	 *
	 * @param mixed $type type of message, or a message object
	 * @param mixed $text, message string or array of strings
	 * @param mixed $css_class, message string or array of strings
	 * @return void
	 */
	static function add($type, $text = '', $css_class = '') {
		if (is_object($type)) {
			if (is_a($type, 'Msg')) {
				$new_msgs = array($type);
			}
			// Object passed in, but not a message
			else {
				return;
			}
		}
		else {
			$new_msgs = array();
			foreach ((array) $text as $str) {
				$new_msgs[] = new Msg($type, $str, $css_class);
			}
		}

		// filter out duplicate messages
		$session_msgs = self::get();
		$new_msgs = array_udiff($new_msgs, $session_msgs, array('Msg', 'message_diff'));

		$session = Session::instance();
		$session->set('msgs', array_merge($session_msgs, $new_msgs));
	}

	/**
	 * Messages array diff function used in array_udiff() within Msg::add()
	 *
	 * @param Msg $msg_a
	 * @param Msg $msg_b
	 * @return int
	 */
	static function message_diff($msg_a, $msg_b) {
		return (int)($msg_a->text != $msg_b->text);
	}


	/**
	 * Grab messages, if no type is passed in, grab all messages
	 *
	 * @param string $type Type of messages to get
	 * @return array Array of messages
	 */
	static function get($type = null) {
		$session = Session::instance();
		$msgs = (array) $session->get('msgs');
		if ($type) {
			$match = array();
			foreach ($msgs as $msg) {
				if ($type == $msg->type) {
					$match[] = $msg;
				}
			}
			$msgs = $match;
		}
		return $msgs;
	}

	/**
	 * Clear all messages from the session, clears only messages
	 * of a certain type if type passed
	 *
	 * @param string $type
	 * @return void
	 */
	static function clear($type = null) {
		$msgs = array();
		if (!empty($type)) {
			$msgs = self::get();
			foreach ($msgs as $msg_key => $msg) {
				if ($msg->type == $type) {
					unset($msgs[$msg_key]);
				}
			}
		}

		$session = Session::instance();
		$session->set('msgs', $msgs);
	}

	/**
	 * Sort messages
	 *
	 * @param array $msgs
	 * @return array Array of sorted messages
	 */
	static function sort($msgs = null) {
		if (is_array($msgs) && count($msgs)) {
			// Use usort for consistant sorting
			usort($msgs, array('Msg', 'sort_compare'));
		}
		else {
			$msgs = array();
		}

		return $msgs;
	}

	/**
	 * Used for usort string comparison
	 *
	 * @param string $a
	 * @param string $b
	 * @return int
	 */
	static function sort_compare($a, $b) {
		return strcasecmp($a->type, $b->type);
	}
}
