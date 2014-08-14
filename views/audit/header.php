<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<div id="cnt-header" class="clearfix<?php echo (empty($favorites) ? ' fav-disabled' : ''); ?>">
	<h1><?php echo $breadcrumbs; ?></h1>
	<?php echo $favorites; ?>

	<ul id="cnt-actions">
		<li class="cnt-actions-li"><a href="<?php echo $model->url('view'); ?>" class="btn">View</a></li>
	</ul>
</div>
