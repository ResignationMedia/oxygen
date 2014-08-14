<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<ul>
	<li><?php echo HTML::anchor('about', 'About '.Oxygen::config('oxygen.app_name').' v'.Oxygen::config('version.app') . ' (Oxygen v'.Oxygen::config('version.oxygen').')'); ?></li>
</ul>
<p>Copyright &copy; <?php echo date('Y'); ?> <a href="http://crowdfavorite.com/">Crowd Favorite</a>. All Rights Reserved.</p>
<p class="cf-branding"><a href="http://crowdfavorite.com"><?php echo HTML::image(OTheme::find_file('img', 'cf-logo', 'png')); ?></a></p>
