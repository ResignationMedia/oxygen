<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo View::factory('audit/header', array(
	'model' => $model,
	'breadcrumbs' => $breadcrumbs,
	'favorites' => $favorites,
));
echo $history;
