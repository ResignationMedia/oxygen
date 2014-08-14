<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<div <?php echo HTML::attributes($field->attributes(null, true)); ?>>
	<div class="flt-left">
		<?php echo $label; ?>
		<?php if (!empty($select_all)): ?>
		<div class="select-all">
			<a href="#" rel="<?php echo $select_all; ?>" class="all">All</a> - <a href="#" rel="<?php echo $select_all; ?>" class="none">None</a>
		</div>
		<?php endif; ?>
	</div>

	<div class="flt-left">
		<?php echo $element; ?>
	</div>

	<div class="clearfix"></div>
</div>
