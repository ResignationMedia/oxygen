<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<!DOCTYPE html>
<html>
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta content="en-us" http-equiv="Content-Language" />
	<title>404 Page Not Found</title>
	<?php echo HTML::style('themes/oxygen/css/errors.css', array('media' => 'screen, tv, projection')); ?>
</head>
<body>
<div class="msg-box">
	<div class="msg-error">
		<h1>404 Page Not Found</h1>
		The page you have requested could not be found. Please <a href="javascript:history.go(-1);">go back</a> and try again.
	</div>
</div>
</body>
</html>
