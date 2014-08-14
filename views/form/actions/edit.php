<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<?php if (Auth::instance()->has_permission('edit', $model)): ?>
	<li class="box-actions-li"><a href="<?php echo $model->url('edit'); ?>" class="btn-b lnk-edit"><?php echo __('Edit'); ?></a></li>
<?php endif; ?>
