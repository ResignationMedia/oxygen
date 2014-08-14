<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo __('Oops!'); ?></title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" media="screen, tv, projection" href="<?php echo URL::site(OTheme::find_file('css', 'errors', 'css')); ?>" />
</head>
<body>
<div class="msg-box">
	<div class="msg-error">
		<h1><?php echo __('Oops!'); ?></h1>
		<div>
<?php

echo $message;
echo View::factory('chrome/messages');

?>
		</div>
	</div>
</div>
</body>
