<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$type = $field->type();
echo Form::$type($field->name(), $field->value(), $field->attributes());
