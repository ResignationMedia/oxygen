<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 */
?>
<div id="cnt-header" class="clearfix<?php echo (empty($favorites) ? ' fav-disabled' : ''); ?>">
	<h1><?php echo $breadcrumbs; ?></h1>
<?php
echo $favorites;

if ($model->show_add()) {
?>
	<ul id="cnt-actions">
		<li class="cnt-actions-li has-submenu">
			<?php echo HTML::anchor($model->url('add'), __('New'), array('data-target' => $model->meta('mult').'-grid', 'class' => 'btn add')); ?>
		</li>
	</ul>
<?php
}
?>
</div>
