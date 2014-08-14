<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package application
 * @subpackage views
 */
?>
<div class="confirm-setup">
	<h2 class="confirm-msg"><?php echo HTML::image(OTheme::find_file('img', 'confirm-check', 'png')); ?>Upgrade Complete</h2>
	<p><?php echo $app_name; ?> has been successfully upgraded.</p>
	<p><?php echo HTML::anchor(URL::site(), 'Reload '.$app_name); ?></p>
</div>
