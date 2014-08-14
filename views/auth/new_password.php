<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo OForm::factory('auth/shell')
	->name('new_password')
	->title('New Password')
	->content('new_password_messages', (!empty($messages) ? $messages : ''))
	->content('new_password_open', '<div id="login-content">')
	->field('password', OField::factory('password')->name(md5('password'.$new_password_token))->label('Password')->help('top', '<p>Enter your new password <strong>twice</strong>:</p>'))
	->field('confirm_password', OField::factory('password')->name(md5('confirm_password'.$new_password_token))->label('Confirm'))
	->content('new_password_actions', View::factory('auth/actions/new_password'))
	->content('new_password_close', '</div>')
	->attributes(array(
		'class' => 'edit',
		'data-token' => $new_password_token
	))
	->attributes(array(
		'class' => 'box box-login frm'
	), true);
