<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo $field->help('top');
?>
<div <?php echo HTML::attributes($field->attributes(null, true)); ?>>
<?php
echo $field->help('left');
echo $element;
echo $label;
echo $field->help('right');
?>
</div>
<?php
echo $field->help('bottom');
