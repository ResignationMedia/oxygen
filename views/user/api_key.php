<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$attributes = $field->attributes();
?>
<div class="pad-common" id="api_key">
	<span<?php echo HTML::attributes($attributes); ?>><?php echo $field->value(); ?></span>
</div>
<a class="btn api_key_reset" data-target="<?php echo $attributes['id']; ?>" data-id="<?php echo $model->id; ?>" data-url="<?php echo URL::site(Request::current()->controller().'/api'); ?>">Change API Key</a>
