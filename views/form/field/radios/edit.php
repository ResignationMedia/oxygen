<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<ul>
<?php
foreach ($field->options() as $value => $label) {
	$id = $field->name().':'.$value;
	if ($field->unique() !== null) {
		$id .= ':'.$field->unique();
	}

	$checked = false;
	if ($value.'' === $field->value().'') {
		$checked = true;
	}
?>
	<li id="elm-li:<?php echo $id; ?>">
<?php
	echo Form::radio($field->name(), $value, $checked, array('id' => $id));
	echo Form::label($id, $field->chars($label));
?>
	</li>
<?php
}
?>
</ul>
