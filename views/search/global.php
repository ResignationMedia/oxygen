<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$label = 'item';
if (!empty($pagination) && $pagination->total_items() > 1) {
	$label .= 's';
}

$items = '';
if (!empty($list)) {
	$items = $list->items();
}
?>
<div id="cnt-header" class="clearfix<?php echo (empty($favorites) ? ' fav-disabled' : ''); ?>">
	<?php if (!empty($pagination)): ?>
	<span class="flt-right" style="line-height:20px"><?php echo $pagination->total_items().' '.$label; ?></span>
	<?php endif; ?>
	<h1><?php echo $breadcrumbs; ?></h1>
	<?php echo $favorites; ?>
</div>

<div class="box oxygen-grid">
	<?php echo Form::open('search'); ?>
	<div id="glb-search-form">
		<div id="glb-search-box">
			<a href="#glb-search-options"><?php echo (empty($items) ? 'Hide' : 'Show'); ?> Options</a>
			<?php echo $form->field('terms')->label(''); ?>
			<?php echo $form->button('search'); ?>
			<div class="clearfix"></div>
		</div>

		<div id="glb-search-options"<?php echo (empty($items) ? '' : ' style="display:none"'); ?>>
			<div id="glb-search-left">
				<div id="glb-search-ipp" class="glb-search-row no-border">
					<?php echo $form->field('items_per_page'); ?>
					<span><?php echo Oxygen::config('oxygen')->preference('search_items_per_page'); ?> Max</span>
					<div class="clearfix"></div>
				</div>

				<div id="glb-search-sort" class="glb-search-row">
					<?php echo $form->field('sort'); ?>
					<?php echo $form->field('sort_order'); ?>
					<div class="clearfix"></div>
				</div>

				<div id="glb-search-show" class="glb-search-row">
					<?php echo $form->field('show'); ?>
				</div>
			</div>

			<div id="glb-search-right">
				<div id="glb-search-filter" class="glb-search-row no-border">
					<?php echo $form->field('filter'); ?>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php
		echo Form::close();
		echo $list;
	?>
</div>
<?php
	$ids = array();
	$show_checked = $form->field('show')->value();
	if (!empty($show_checked)) {
		foreach ($show_checked as $id) {
			$ids[] = 'show:'.$id;
		}
	}

	if (!empty($ids)) {
?>
<script type="text/javascript">
	$(function(){
		$('<?php echo implode(',', $ids); ?>').prop('checked', true);
	});
</script>
<?php
	}
