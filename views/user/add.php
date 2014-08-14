<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo View::factory('content/header', array(
	'breadcrumbs' => $breadcrumbs,
	'favorites' => $favorites,
));
echo $form->content('permissions_content', View::factory('user/permissions', array(
	'model' => $model,
	'roles' => $roles,
	'default_role_id' => Oxygen::config('oxygen')->get('default_role'),
	'permissions' => OModel::factory('Role', Oxygen::config('oxygen')->get('default_role'))->permissions,
	'header' => true
)));
