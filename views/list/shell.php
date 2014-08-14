<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<section class="box oxygen-grid" <?php echo HTML::attributes($attributes); ?>>
	<?php echo $header; ?>
	<div class="box-content">
		<table class="oxygen-grid">
			<?php echo $row_header; ?>
			<?php echo $content; ?>
		</table>
	</div>
	<footer class="box-footer grid-footer">
		<?php echo $footer; ?>
	</footer>
</section>
