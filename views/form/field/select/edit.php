<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<select name="<?php echo $field->name(); ?>"<?php echo HTML::attributes($field->attributes()); ?>>
	<?php foreach ($field->options() as $k => $v): ?>
	<?php $selected = (($k == $field->value()) || ($k == '' && $field->value() === false) || Arr::get($_POST, $field->name()) == $k) ? ' selected="selected"' : ''; ?>
	<option id="<?php echo $field->name().':'.$k; ?>" value="<?php echo $k; ?>"<?php echo $selected; ?>><?php echo $field->chars($v); ?></option>
	<?php endforeach; ?>
</select>
