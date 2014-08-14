<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Gravatar {

	/**
	 * Builds the Gravatar URL
	 *
	 * @static
	 * @param  string  $email
	 * @param  int     $size
	 * @param  bool    $encode_html
	 * @return string
	 */
	public static function url($email, $size = 32, $encode_html = true) {
		$default = OHooks::instance()->filter('gravatar_default', 'http://gravatar.api.crowdfavorite.com/oxygen/profile-large.jpg');

		if (Request::current()->secure()) {
			$host = 'https://secure.gravatar.com';
		}
		else {
			$host = 'http://www.gravatar.com';
		}

		$sep = ($encode_html ? '&amp;' : '&');

		return $host.'/avatar/'.md5(strtolower(trim($email))).'?s='.$size.$sep.'d='.urlencode($default);
	}

} // End Oxygen_Gravatar
