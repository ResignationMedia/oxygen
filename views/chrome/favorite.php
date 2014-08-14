<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$attributes = array(
	'title' => __('Add to Favorites'),
	'class' => $favorite === true ? 'fav' : null,
	'data-url' => HTML::chars($url),
	'data-title' => HTML::chars($title),
	'data-group' => HTML::chars($group),
);
?>
<span class="fav-action">
	<a href="favorite.php#"<?php echo HTML::attributes($attributes); ?>>__('Add to Favorites')</a>
</span>
	
