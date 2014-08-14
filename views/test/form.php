<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<h1>Editable Fields</h1>
<?php
echo OForm::factory()
	->name('edit')
	->field('hidden', OField::factory('hidden')->name('hidden')->default_value('test'))
	->field('text', OField::factory()->name('text'))
	->field('password', OField::factory('password')->name('password'))
	->field('email', OField::factory('email')->name('email'))
	->field('phone', OField::factory('phone')->name('phone'))
	->field('file', OField::factory('file')->name('file'))
	->field('readonly', OField::factory('readonly')->name('readonly')->default_value('test'))
	->field('textarea', OField::factory('textarea')->name('textarea'))
	->field('select', OField::factory('select')->name('select')->options(array('foo' => 'bar', 'cat' => 'dog')))
	->field('checkbox', OField::factory('checkbox')->name('checkbox'))
	->field('checboxes', OField::factory('checkbox')->name('checkboxes')->label('')->options(array('foo' => 'bar', 'cat' => 'dog')))
	->field('radio', OField::factory('radio')->name('radio'))
	->field('date', OField::factory('date')->name('date')->default_value(time()))
	->button('button', OField::factory('button')->name('button')->default_value('Button'))
	->button('submit', OField::factory('submit')->name('submit')->default_value('Submit'))
	->button('reset', OField::factory('reset')->name('reset')->default_value('reset'));
?>

<h2>View Fields</h2>
<?php
echo OForm::factory()
	->name('view')
	->field('hidden', OField::factory('hidden')->name('hidden')->default_value('test')->display('view'))
	->field('text', OField::factory()->name('text')->default_value('test')->display('view'))
	->field('password', OField::factory('password')->name('password')->default_value('test')->display('view'))
	->field('email', OField::factory('email')->name('email')->default_value('user@example.com')->display('view'))
	->field('phone', OField::factory('phone')->name('phone')->default_value('5551234567')->display('view'))
	->field('file', OField::factory('file')->name('file')->display('view'))
	->field('readonly', OField::factory('readonly')->name('readonly')->default_value('test')->display('view'))
	->field('textarea', OField::factory('textarea')->name('textarea')->default_value('test')->display('view'))
	->field('select', OField::factory('select')->name('select')->options(array('foo' => 'bar', 'cat' => 'dog'))->default_value('cat')->display('view'))
	->field('checkbox', OField::factory('checkbox')->name('checkbox')->display('view'))
	->field('checboxes', OField::factory('checkbox')->name('checkboxes')->label('')->options(array('foo' => 'bar', 'cat' => 'dog'))->default_value('cat')->display('view'))
	->field('radio', OField::factory('radio')->name('radio')->display('view'))
	->field('date', OField::factory('date')->name('date')->default_value(time())->display('view'))
	->button('button', OField::factory('button')->name('button')->default_value('Button'))
	->button('submit', OField::factory('submit')->name('submit')->default_value('Submit'))
	->button('reset', OField::factory('reset')->name('reset')->default_value('reset'));
