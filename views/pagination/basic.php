<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
if ($page->total_pages() > 1):
	$count_out = $config['count_out'];
	$count_in = $config['count_in'];
	$n1 = 1;
	$n2 = min($count_out, $page->total_pages());
	$n7 = max(1, $page->total_pages() - $count_out + 1);
	$n8 = $page->total_pages();
	$n4 = max($n2 + 1, $page->current_page() - $count_in);
	$n5 = min($n7 - 1, $page->current_page() + $count_in);
	$use_middle = ($n5 >= $n4);
	$n3 = (int) (($n2 + $n4) / 2);
	$use_n3 = ($use_middle && (($n4 - $n2) > 1));
	$n6 = (int) (($n5 + $n7) / 2);
	$use_n6 = ($use_middle && (($n7 - $n5) > 1));

	$links = array();
	for ($i = $n1; $i <= $n2; ++$i) {
		$links[$i] = $i;
	}
	if ($use_n3) {
		$links[$n3] = '&hellip;';
	}
	for ($i = $n4; $i <= $n5; ++$i) {
		$links[$i] = $i;
	}
	if ($use_n6) {
		$links[$n6] = '&hellip;';
	}
	for ($i = $n7; $i <= $n8; ++$i) {
		$links[$i] = $i;
	}
?>
<nav class="pgn">
	<ol class="pgn-list">
		<?php foreach ($links as $number => $content): ?>
			<li><a href="<?php echo $page->url($number); ?>" data-page="<?php echo $number; ?>"<?php echo ($number == $page->current_page() ? ' class="current"' : ''); ?>><?php echo $content; ?></a></li>
		<?php endforeach; ?>
	</ol>

	<?php if ($page->previous_page()): ?>
	<p class="pgn-prev"><a href="<?php echo $page->url($page->previous_page()); ?>" data-page="<?php echo $page->previous_page(); ?>"><?php echo __('Prev'); ?></a></p>
	<?php endif; ?>

	<?php if ($page->next_page()): ?>
	<p class="pgn-next"><a href="<?php echo $page->url($page->next_page()); ?>" data-page="<?php echo $page->next_page(); ?>"><?php echo __('Next'); ?></a></p>
	<?php endif; else: ?>
<nav class="pgn empty">
	<?php endif; ?>
</nav>
