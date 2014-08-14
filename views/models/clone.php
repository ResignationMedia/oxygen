<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo View::factory('content/header', array(
	'breadcrumbs' => $breadcrumbs,
	'favorites' => $favorites,
));
echo $form;

// Audits
if (isset($model)) {
	echo View::factory('audit/list', array(
		'model' => $model
	));
}
