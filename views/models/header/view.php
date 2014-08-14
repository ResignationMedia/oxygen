<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<div id="cnt-header" class="clearfix<?php echo (empty($favorites) ? ' fav-disabled' : ''); ?>">
	<h1><?php echo $breadcrumbs; ?></h1>
<?php
echo $favorites;

if (Auth::instance()->has_permission('edit', $model)) {
?>
	<ul id="cnt-actions">
		<li class="cnt-actions-li"><a href="<?php echo $model->url('edit'); ?>" class="btn"><?php echo __('Edit'); ?></a></li>
	</ul>
<?php
}
?>
</div>
