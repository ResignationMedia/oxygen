<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<ul>
<?php
foreach ($field->options() as $key => $label) {
	$attributes['id'] = $id = $field->name().':'.$key;
	if ($field->unique() !== null) {
		$attributes['id'] .= ':'.$field->unique();
	}
	$attributes['class'] = 'elm-checkbox';
?>
	<li id="elm-li:<?php echo $id; ?>">
<?php
	echo Form::checkbox(
		$field->name().'[]',
		$key,
		(@in_array($key, $field->value())),
		$attributes
	);
?>
		<label for="<?php echo $field->name().':'.$key; ?>" class="lbl-checkbox"><?php echo $field->chars($label); ?></label>
	</li>
<?php
} // end foreach
?>
</ul>
