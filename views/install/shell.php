<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<section <?php echo HTML::attributes($shell_attributes); ?>>
<?php
echo Form::open($action, $attributes);
echo $header;
?>
	<div class="box-content">
		<?php echo $content; ?>
	</div>
<?php
echo $footer;
echo Form::close();
?>
</section>
