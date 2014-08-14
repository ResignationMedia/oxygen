<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<fieldset<?php echo HTML::attributes($attributes); ?>>
	<?php if (!empty($legend)): ?>
		<legend><?php echo $legend; ?></legend>
	<?php endif; ?>

	<?php echo $content; ?>

	<?php foreach ($fields as $field): ?>
		<?php echo $field; ?>
	<?php endforeach; ?>
</fieldset>
