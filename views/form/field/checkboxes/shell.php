<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<div <?php echo HTML::attributes($field->attributes(null, true)); ?>>
	<?php if (!empty($select_all)): ?>
	<div class="select-all">
		<a href="#" rel="<?php echo $select_all; ?>" class="all">Select All</a> - <a href="#" rel="<?php echo $select_all; ?>" class="none">Deselect All</a>
	</div>
	<?php endif; ?>
	<?php echo $label; ?>
	<?php echo $element; ?>
</div>
