<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo HTML::anchor($actor['view_url'], '<strong>'.HTML::chars($actor['title']).'</strong>').' added '.HTML::anchor($target['view_url'], '<strong>'.HTML::chars($target['title']).'</strong>');
if (!empty($destination)) {
	echo ' to '.HTML::anchor($destination['view_url'], '<strong>'.HTML::chars($destination['title']).'</strong>');
}
echo '.';
