<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

// TODO implement history
?>
<aside id="usr" class="usr-profile vcard">
	<h1 class="fn usr-fn"><?php echo Utility::html($user->name()); ?></h1>
	<?php echo $user->profile_photo('thumbnail'); ?>
	<ul class="usr-nav">
		<li><?php echo HTML::anchor('profile', 'Profile'); ?></li>
		<li><?php echo HTML::anchor('logout', 'Logout'); ?></li>
		<li><?php echo HTML::anchor('help', 'Help'); ?></li>
	</ul>
</aside>

<?php
echo View::factory('user/favorites', array(
	'favorites' => Favorites::load_all($user)
));
