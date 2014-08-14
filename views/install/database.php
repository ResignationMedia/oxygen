<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<?php echo $messages; ?>

<p>Use the form below to configure your application's initial environment.</p>

<h3>Domain</h3>
<p>This is the domain your application is hosted on.</p>
<fieldset>
	<?php echo OField::factory()->name('domain')->default_value(Arr::get($_POST, 'domain', $_SERVER['SERVER_NAME']))->label(''); ?>
</fieldset>

<h3>Database</h3>
<p>Enter your database credentials.</p>
<fieldset>
	<?php echo OField::factory()->name('hostname')->default_value(Arr::get($_POST, 'hostname', 'localhost'))->label('Host'); ?>
	<?php echo OField::factory()->name('username')->default_value(Arr::get($_POST, 'username', 'root')); ?>
	<?php echo OField::factory()->name('password')->default_value(Arr::get($_POST, 'password', 'password')); ?>
	<?php echo OField::factory()->name('database')->default_value(Arr::get($_POST, 'database', 'oxygen'))->label('Name'); ?>
	<?php echo OField::factory()->name('table_prefix')->default_value(Arr::get($_POST, 'table_prefix', 'o_')); ?>
</fieldset>
