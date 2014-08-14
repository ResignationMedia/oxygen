<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

$value = (int) $field->value();
$default_value = (int) $field->default_value();

echo Form::checkbox($field->name(), $field->default_value(), Form::checked($field->name(), $value, $default_value), $field->attributes());
