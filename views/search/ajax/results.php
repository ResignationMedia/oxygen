<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<ul>
	<?php if ($result == 'error'): ?>
	<li class="none"><?php echo $items; ?></li>
	<?php else: ?>
		<?php foreach ($items as $item): ?>
		<li data-url-edit="<?php echo URL::site($item->global->edit_url); ?>">
			<h4><?php echo HTML::anchor($item->global->view_url, $item->global->title); ?></h4>
			<p><?php echo $item->summary; ?></p>
		</li>
		<?php endforeach; ?>
		<li class="s-tips-view-all"><?php echo HTML::anchor($view_all, 'view all'); ?></li>
	<?php endif; ?>
</ul>
