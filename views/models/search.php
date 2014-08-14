<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$label = 'item';
if (!empty($pagination) && $pagination->total_items() > 1) {
	$label .= 's';
}
?>
<div id="cnt-header" class="clearfix<?php echo (empty($favorites) ? ' fav-disabled' : ''); ?>">
	<?php if (!empty($pagination)): ?>
	<span class="flt-right" style="line-height:20px"><?php echo $pagination->total_items().' '.$label; ?></span>
	<?php endif; ?>
	<h1><?php echo $breadcrumbs; ?></h1>
	<?php echo $favorites; ?>
</div>

<div id="crud-search" class="box oxygen-grid">
	<?php echo Form::open(Request::current()); ?>
	<div id="crud-search-form">
		<div id="crud-search-left">
			<?php $i = 0; ?>
			<?php foreach ($model->fieldgroup('search') as $field): ?>
				<?php if (!in_array($field->name(), array('created', 'updated', 'enabled'))): ?>
				<div class="crud-search-row<?php echo $i == 0 ? ' no-border' : ''; ?>">
					<?php echo $field; ?>
					<div class="clearfix"></div>
				</div>
				<?php endif; ?>
			<?php ++$i; endforeach; ?>
		</div>

		<div id="crud-search-right">
			<div id="crud-search-ipp" class="crud-search-row no-border">
				<?php echo $form->field('items_per_page'); ?>
				<span><?php echo Oxygen::config('oxygen')->preference('search_items_per_page'); ?> Max</span>
				<div class="clearfix"></div>
			</div>

			<div id="crud-search-sort" class="crud-search-row">
				<?php echo $form->field('sort'); ?>
				<?php echo $form->field('sort_order'); ?>
				<div class="clearfix"></div>
			</div>

			<?php if ($form->field('enabled') !== false): ?>
			<div id="crud-search-enabled" class="crud-search-row">
				<?php echo $form->field('enabled'); ?>
				<div class="clearfix"></div>
			</div>
			<?php endif; ?>

			<?php if ($form->field('created') !== false): ?>
			<div class="crud-search-row">
				<?php echo $form->field('created'); ?>
				<div class="clearfix"></div>
			</div>
			<?php endif; ?>

			<?php if ($form->field('updated') !== false): ?>
			<div class="crud-search-row">
				<?php echo $form->field('updated'); ?>
				<div class="clearfix"></div>
			</div>
			<?php endif; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php echo $form->footer(); ?>
	<?php echo Form::close(); ?>

	<?php echo $list; ?>
</div>

<?php if (Arr::get($_POST, 'show') === null): ?>
<script type="text/javascript">
	$(function(){
		$('#crud-search-show input[type=checkbox]').prop('checked', true);
	});
</script>
<?php endif; ?>
