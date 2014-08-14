<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta content="en-us" http-equiv="Content-Language" />
	<title>Uh oh...</title>
	<?php echo HTML::style(OResource::url('oxygen-css'), array('media' => 'screen, tv, projection')); ?>
	<!--[if IE 7]>
		<?php echo HTML::style(OTheme::find_file('css', 'ie7', 'css'), array('media' => 'screen, tv, projection')); ?>
	<![endif]-->
	<style type="text/css">
		body {
			background: #f5f5f5;
			font-size: 11px;
			line-height: 1.4em;
			color: #333;
		}
		#message {
			margin: 0 auto;
			padding: 50px 0;
			width: 600px;
		}
		#message div {
			background: #fff;
			padding: 15px 20px;
			border-radius: 10px;
			-moz-border-radius: 10px;
			-webkit-border-radius: 10px;
			text-align: center;
		}
	</style>
</head>
<body>
<div id="message"><div><?php echo $message; ?></div></div>
</body>
</html>
