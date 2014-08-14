<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$value = $field->value();
if (empty($value)) {
	$value[] = '';
	$value[] = '';
}
?>
<div class="elm-dsp-date-range">
	<?php echo Form::label($field->id().'_start', 'Start:'); ?>
	<?php echo Form::input($field->name().'[]', $value[0], Arr::merge($field->attributes(), array('id' => $field->id().'_start'))); ?>
</div>
<div class="elm-dsp-date-range">
	<?php echo Form::label($field->id().'_end', 'End:'); ?>
	<?php echo Form::input($field->name().'[]', $value[1], Arr::merge($field->attributes(), array('id' => $field->id().'_end'))); ?>
</div>
