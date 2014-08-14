<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo Form::select($field->name(), $field->options(), $field->default_value(), $field->attributes());
