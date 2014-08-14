<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<div id="fav-form">
	<form action="<?php echo URL::site('favorite/add'); ?>" method="post" data-url-delete="<?php echo URL::site('favorite/delete'); ?>">
		<input type="text" name="title" value="" class="fav-form-title" />
		<input type="submit" value="Save" class="btn btn-submit" />
		<input type="hidden" name="url" />
		<input type="hidden" name="key" />
	</form>
	<div class="fav-form-btm"></div>
</div>
