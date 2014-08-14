<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
echo Form::checkbox($field->name(), $field->value(), Form::checked($field->name(), $field->value(), $field->default_value()), $field->attributes());
