<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
if (isset($model)):
?>
<li class="box-actions-li">
	<?php echo $model->link('search', 'Search', array('class' => 'btn-b')); ?>
</li>
<?php endif; ?>
