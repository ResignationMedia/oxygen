<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
if ($actor['guid'] === $target['guid']) {
	echo HTML::anchor($actor['view_url'], '<strong>'.HTML::chars($actor['title']).'</strong>').' updated their photo.';
}
else {
	echo HTML::anchor($actor['view_url'], '<strong>'.HTML::chars($actor['title']).'</strong>').' updated '.HTML::anchor($target['view_url'], '<strong>'.HTML::chars($target['title']).'</strong>').'\'s photo.';
}
