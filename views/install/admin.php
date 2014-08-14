<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<?php echo $messages; ?>

<p>The system as been setup, now it's time to create the default administrator account.</p>

<h3>Contact Information</h3>
<fieldset>
	<?php echo OField::factory()->name('name')->default_value(Arr::get($_POST, 'name', '')); ?>
	<?php echo OField::factory()->name('email')->default_value(Arr::get($_POST, 'email', '')); ?>
</fieldset>

<h3>Account Information</h3>
<fieldset>
	<?php echo OField::factory()->name('username')->default_value(Arr::get($_POST, 'username', '')); ?>
	<?php echo OField::factory('password')->name('password')->default_value(Arr::get($_POST, 'password', '')); ?>
	<?php echo OField::factory('password')->name('password_confirm')->default_value(Arr::get($_POST, 'confirm_password', ''))->label('Confirm Password'); ?>
</fieldset>
