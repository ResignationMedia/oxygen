<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<tr id="<?php echo $item->meta('one').'_'.$item->id; ?>" class="itm<?php echo ($is_last_item ? ' last' : ($is_first_item ? ' first' : '')); ?>">
<?php
foreach ($item->fieldgroup('list') as $field) {
	$field->display('view');
	$value = $field->value();
	if ($item->view_column() == $field->name()) {
		$value = $item->link('view', $field->chars($value));
	}

	$classes = 'elm-'.$field->type();
	if ($field->name() == Request::current()->param('sort')) {
		$classes = HTML::add_class('sorted', $classes);
	}

?>
	<td class="<?php echo $classes; ?>"><?php echo $value; ?></td>
<?php
}

foreach ($actions as $action) {
	if (Auth::instance()->has_permission($action, $model)) {
		$nonce = Nonce::factory($item->meta('one').'_'.$action, $item->id)->generate();
		$class = ($item->crud_request() == 'ajax' ? ' ajax-'.$action : '');

		$attributes = array(
			'class' => 'lnk-'.$action.$class,
			'data-nonce' => $nonce
		);
?>
	<td class="action <?php echo $action.$class; ?>">
		<?php echo HTML::anchor($item->url($action), Inflector::humanize($action, true), $attributes); ?>
	</td>
<?php
	}
}
?>
</tr>
