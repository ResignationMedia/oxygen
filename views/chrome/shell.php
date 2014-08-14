<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta content="en-us" http-equiv="Content-Language" />
	<title><?php echo strip_tags($title); ?></title>
	<link rel="shortcut icon" href="<?php echo URL::site(OTheme::find_file('img', 'favicon', 'ico')); ?>" />
	<?php echo HTML::style(OResource::url('oxygen-css'), array('media' => 'screen, tv, projection, print')); ?>
	<!--[if IE 7]>
	<?php echo HTML::style(OTheme::find_file('css', 'ie7', 'css'), array('media' => 'screen, tv, projection')); ?>
	<![endif]-->
<?php
foreach ($extra_styles as $style) {
	echo HTML::style($style, array('media' => 'screen, tv, projection'));
}

echo HTML::script(OResource::url('oxygen-js'));
?>
	<!--[if IE]>
	<?php echo HTML::script(OTheme::find_file('js', 'html5', 'js')); ?>
	<![endif]-->
</head>
<body<?php echo ($login) ? ' class="login-screen"' : ''; ?>>
<?php
if (!$login) {
?>
<div id="str-body">
	<header id="str-header">
		<?php echo $header; ?>
	</header>

	<div id="str-page" class="clearfix">
		<div id="str-content">
			<?php echo $messages; ?>
			<div class="pad-common">
<?php
}

echo $content;

if (!$login) {
?>
			</div>
		</div>

		<div id="str-sidebar">
			<?php echo $sidebar; ?>
		</div>
	</div>
</div>

<footer id="str-footer" class="pad-common">
	<?php echo $footer; ?>
</footer>
<?php
}

echo $favorites->form();

foreach ($extra_scripts as $script) {
	echo HTML::script($script);
}

?>
</body>
</html>
