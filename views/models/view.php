<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo View::factory('models/header/view', array(
	'model' => $model,
	'breadcrumbs' => $breadcrumbs,
	'favorites' => $favorites,
));
echo $form;

// Audits
if (isset($model) && $model->audit_status() !== OAudit::OFF) {
	echo View::factory('audit/list', array(
		'model' => $model
	));
}
