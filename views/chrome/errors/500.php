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
	<title>500 - Access Denied</title>
	<?php echo HTML::style('themes/oxygen/css/errors.css', array('media' => 'screen, tv, projection')); ?>
</head>
<body>
<div class="msg-box">
	<div class="msg-error">
		<h1>500 - Access Denied</h1>
		<?php echo $message; ?> Please <a href="javascript:history.go(-1);">go back</a>.
	</div>
</div>
</body>
</html>
