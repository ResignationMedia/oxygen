<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<?php if ($type != 'hidden'): ?>
<div id="elm-block-<?php echo $type == 'link' ? 'link' : $name; ?>" class="elm-block<?php echo ($type != 'link' ? ' has-'.$type : '').($has_flag ? ' has-flag' : ''); ?>">
<?php endif; ?>
	<?php if ($type == 'link' || $type == 'hidden'): ?>
		<?php echo $field; ?>
	<?php elseif ($type == 'checkbox' || $type == 'flag'): ?>
		<?php echo $field.$label; ?>
	<?php else: ?>
		<?php echo $label.$field; ?>
	<?php endif; ?>
<?php if ($type != 'hidden'): ?>
</div>
<?php endif; ?>
