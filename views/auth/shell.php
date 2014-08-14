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
	<footer class="box-footer frm-footer">
		<p class="product-by-cf">
		<a href="http://crowdfavorite.com"><?php echo HTML::image(OTheme::find_file('img', 'cf-logo', 'png'), array('title' => 'Development by Crowd Favorite')); ?></a></p>
		<p>Copyright &copy; <?php echo date('Y'); ?> <a href="http://crowdfavorite.com">Crowd Favorite, Ltd.</a> All Rights Reserved.</p>
	</footer>
<?php
echo Form::close();
?>
</section>
