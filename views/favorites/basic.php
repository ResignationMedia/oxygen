<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<span class="fav-action">
	<a href="#" title="Add to Favorites"<?php echo ($favorites->favorite() ? ' class="fav"' : ''); ?> data-url="<?php echo $favorites->url(); ?>" data-title="<?php echo $favorites->title(); ?>" data-group="<?php echo $favorites->group(); ?>">Add to Favorites</a>
</span>
