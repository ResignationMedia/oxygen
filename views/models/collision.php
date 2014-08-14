<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo View::factory('content/header', array(
	'breadcrumbs' => $breadcrumbs,
	'favorites' => $favorites,
));
echo OForm::factory()
	->model($model)
	->title('Edit '.$model->meta('one_text'))
	->name($model->meta('one').'_edit')
	->content('collision_content', View::factory('models/collision/content', array(
		'model' => $model,
		'diff' => $diff,
		'previous' => $previous,
		'nonce' => $nonce
	)))
	->field('updated',
		OField::factory('hidden')
			->model($model)
			->name('updated')
			->default_value($previous->updated)
	)
	->button('save',
		OField::factory('submit')
			->model($model)
			->name('save')
			->default_value('Save')
	)
	->attributes(array(
		'class' => 'edit'
	));
