<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
// Show elements
$show = Arr::get($_POST, 'show');
?>
<li>
	<p class="itm-actions action"><?php echo HTML::anchor($item->global->edit_url, __('Edit')); ?></p>
	<?php if ($show === null || in_array('title', $show)): ?>
	<h3><?php echo HTML::anchor($item->global->view_url, $item->global->title); ?></h3>
	<?php endif; ?>

	<?php if ($show === null || in_array('description', $show)): ?>
	<p><?php echo $item->global->search->summary; ?></p>
	<?php endif; ?>
	<dl class="itm-meta lst-inline">
		<?php if ($show === null || in_array('created', $show)): ?>
		<dt><?php echo __('Created:'); ?></dt>
		<dd>
			<?php
				if ($item->created) {
					echo Date::local($item->created);
				}
				else {
					echo __('Never');
				}
			?>
		</dd>
		<?php endif; ?>

		<?php if ($show === null || in_array('updated', $show)): ?>
		<dt><?php echo __('Last Modified:'); ?></dt>
		<dd>
			<?php
				if ($item->updated) {
					echo Date::local($item->updated);
				}
				else {
					echo __('Never');
				}
			?>
		</dd>
		<?php endif; ?>
	</dl>
</li>
