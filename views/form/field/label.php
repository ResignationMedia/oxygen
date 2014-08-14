<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$label = $field->label();
?>
<?php if (!empty($label)): ?>
	<label for="<?php echo $field->id(); ?>" class="lbl-<?php echo $field->type(); ?>"><?php echo $label; ?></label>
<?php endif; ?>
