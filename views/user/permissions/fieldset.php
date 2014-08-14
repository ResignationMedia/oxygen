<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<fieldset<?php echo HTML::attributes($attributes); ?>>
<?php
if (!empty($legend)) {
?>
		<legend><?php echo $legend; ?></legend>
<?php 
}

foreach ($fields as $field) {
	echo $field;
}

echo $content;
?>
</fieldset>
