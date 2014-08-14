<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<?php if (Auth::instance()->has_permission('delete', $model)): ?>
	<li class="box-actions-li"><a href="<?php echo $model->url('delete'); ?>" class="btn-b lnk-delete"><?php echo __('Delete'); ?></a></li>
<?php endif; ?>
