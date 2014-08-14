<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

?>
<ul>
<?php
$options = $field->options();
$values = $field->value();
if (count($values)) {
	foreach ($values as $key) {
		$attributes['id'] = $id = $field->name().':'.$key;
		if ($field->unique() !== null) {
			$attributes['id'] .= ':'.$field->unique();
		}
		$attributes['class'] = 'elm-checkbox';
?>
	<li id="elm-li:<?php echo $id; ?>">
		<span <?php echo HTML::attributes($field->attributes()); ?>><?php echo $field->chars($options[$key]); ?></span>
	</li>
<?php
	}
}
else {
?>
	<li>
		<span class="none"><?php echo __('(None)'); ?></span>
	</li>
<?php
}
?>
</ul>
