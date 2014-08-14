<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
if (isset($model)):
?>
<li class="box-actions-li">
	<?php echo $model->link('filter', 'Filter', array('class' => 'btn-b filter')); ?>
</li>
<?php endif; ?>
