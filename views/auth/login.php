<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo OForm::factory('auth/shell')
	->name('login')
	->title(Oxygen::config('oxygen')->get('app_name'))
	->content('login_messages', (!empty($messages) ? $messages : ''))
	->content('login_open', '<div id="login-content">')
	->field('username', OField::factory()
		->name(md5('username'.$login_token))
		->label(__('Username'))
		->add_css_class('username')
	)
	->field('password', OField::factory('password')
		->name(md5('password'.$login_token))
		->label(__('Password'))
	)
	->field('remember_me', OField::factory('flag')
		->name(md5('remember_me'.$login_token))
		->label(__('Remember Me'))
	)
	->content('login_actions', View::factory('auth/actions/login'))
	->content('login_close', '</div>')
	->attributes(array(
		'class' => 'edit',
		'data-token' => $login_token
	))
	->attributes(array(
		'class' => 'box box-login frm'
	), true);
