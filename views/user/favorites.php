<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<aside id="fav">
	<h1>Favorites</h1>
	<?php if (count($favorites)): ?>
		<?php foreach ($favorites as $group => $bookmarks): ?>

			<?php if ($group != '*'): ?>
			<h2><?php echo $group; ?></h2>
			<?php endif; ?>

			<ul class="lst fav">
				<?php foreach ($bookmarks as $bookmark): ?>
				<li>
					<a href="<?php echo URL::site($bookmark->url()); ?>"><?php echo $bookmark->title(); ?></a>
					<a class="fav-remove" data-url="<?php echo $bookmark->url(); ?>"><?php echo __('Remove'); ?></a>
				</li>
				<?php endforeach; ?>
			</ul>

		<?php endforeach; ?>
	<?php else: ?>
	<p class="none">(none)</p>
	<?php endif; ?>
</aside>
