<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo View::factory('models/header/list', array(
	'breadcrumbs' => $breadcrumbs,
	'favorites' => $favorites,
));

echo $list;
