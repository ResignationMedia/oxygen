<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<div id="elm-block--login">
	<?php echo OField::factory('submit')->name('login')->value('Login'); ?>
</div>

<div id="elm-block--forgot_password">
	<?php echo HTML::anchor('forgot_password', 'Forgot your password?'); ?>
</div>
