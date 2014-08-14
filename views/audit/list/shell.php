<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<section class="box hst">
<?php
echo Form::open('history/compare', array('class' => 'hst'));
?>
	<header class="box-header grid-header">
		<h2><?php echo __('History'); ?></h2>
	</header>
	<div class="box-content">
		<table id="history" class="hst oxygen-grid">
			<thead>
				<tr>
					<th colspan="2" class="txt-center"><?php echo __('Compare'); ?></th>
					<th width="50%"><?php echo __('Revision'); ?></th>
					<th><?php echo __('Date'); ?></th>
					<th width="25%">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
<?php

if (!empty($items)) {
	$i = 1;
	foreach ($items as $item) {
		// defaults
		if ($i == 1 && is_null($a)) {
			$a = $item->id;
		}
		if ($i == 2 && is_null($b)) {
			$b = $item->id;
		}
		$first = ($i == 1 ? true : false);
		$last = ($i == count($items) ? true : false);
		echo View::factory('audit/list/row', compact('item', 'a', 'b', 'first', 'last'));
		$i++;
	}
}
else {
	echo View::factory('audit/list/row/empty');
}

?>
			</tbody>
		</table>
	</div>
	<footer class="box-footer">
		<?php echo Form::submit('compare', __('Compare Selected Versions'), array('class' => 'btn btn-submit')); ?>
	</footer>
<?php
echo Form::close();
?>
</section>
