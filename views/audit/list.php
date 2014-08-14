<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$id = $model->meta('one').'-'.$model->id;
?>
<p class="hst-toggle" data-item="<?php echo $id; ?>">
	<?php echo HTML::anchor('history/'.$model->table_name().'/'.$model->id.'/'.get_class($model), 'History'); ?>
</p>
<div id="hst-<?php echo $id; ?>" class="hidden"></div>
