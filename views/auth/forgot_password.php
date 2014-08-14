<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo OForm::factory('auth/shell')
	->name('forgot_password')
	->title('Forgot Password')
	->content('forgot_password_messages', (!empty($messages) ? $messages : ''))
	->content('forgot_password_open', '<div id="login-content">')
	->field('email_username', OField::factory()->name('identifier')->label('Email/Username')->help('top', 'Enter your email address and we\'ll send you a link to set a new password.'))
	->content('forgot_password_actions', View::factory('auth/actions/forgot_password'))
	->content('forgot_password_close', '</div>')
	->attributes(array(
		'class' => 'edit',
		'data-token' => $forgot_password_token
	))
	->attributes(array(
		'class' => 'box box-login frm'
	), true);
