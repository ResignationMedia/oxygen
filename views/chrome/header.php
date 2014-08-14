<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$admin_nav = Oxygen::build_header_nav('admin_nav');
?>
<div class="pad-common clearfix">
	<h1 id="logo"><strong><?php echo HTML::anchor('', Oxygen::config('oxygen')->get('app_name')); ?></strong></h1>
	<nav id="hdr-nav">
		<ul class="nav-main">
			<?php echo Oxygen::build_header_nav('main_nav'); ?>

			<?php if (!empty($admin_nav)) { ?>
			<li class="has-nav-sub">
				<a href="#">Admin</a>
				<ul class="nav-sub">
					<?php echo $admin_nav; ?>
				</ul>
			</li>
			<?php } ?>
		</ul>
	</nav>
	<form id="hdr-search" action="<?php echo URL::site('search'); ?>" method="post">
		<input type="text" name="terms" id="s-terms" placeholder="Search" value="<?php echo Arr::get($_POST, 'terms'); ?>" />
		<input type="submit" name="search" id="s-button" value="&rarr;" />
		<div class="s-tips"></div>
	</form>
</div>
