<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<span<?php echo HTML::attributes($field->attributes()); ?>><?php echo $field->chars($field->value()); ?></span>
