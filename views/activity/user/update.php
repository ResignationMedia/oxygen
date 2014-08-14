<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
if ($actor['guid'] === $target['guid']) {
	$a = HTML::anchor($actor['view_url'], HTML::chars($actor['title']));
	echo __('<strong>:a</strong> updated their profile.', array(':a' => $a));
}
else {
	$a = HTML::anchor($actor['view_url'], HTML::chars($actor['title']));
	$b = HTML::anchor($target['view_url'], HTML::chars($target['title']));
	echo __('<strong>:a</strong> updated <strong>:b\'s</strong> profile.', array(':a' => $a, ':b' => $b));
}
