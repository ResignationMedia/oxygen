<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

$options = $field->options();
$values = $field->value();
$output = array();
if (is_array($values) && !empty($values)) {
	foreach ($values as $key) {
		if (isset($options[$key])) {
			$output[] = $field->chars($options[$key]);
		}
	}
?>
<span class="values"><?php echo implode(', ', $output); ?></span>
<?php
}
else {
?>
<span class="none"><?php echo __('(None)'); ?></span>
<?php
}
