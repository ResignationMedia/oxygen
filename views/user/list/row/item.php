<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<tr id="<?php echo $item->meta('one').'_'.$item->id; ?>" class="itm">
	<td><?php echo $item->profile_photo('thumbnail'); ?></td>
	<?php
		foreach ($item->fieldgroup('list') as $field) {
			if ($field->name() == 'email') {
				$field->link(true);
			}

			$value = $field->value();
			if ($item->view_column() == $field->name()) {
				$value = HTML::anchor($item->url('view'), $field->chars($value));
			}

			$classes = 'elm-'.$field->type();
			if ($field->name() == Request::current()->param('sort')) {
				$classes = HTML::add_class('sorted', $classes);
			}
	?>
	<td class="<?php echo $classes; ?>">
		<?php echo $value; ?>
		<?php if ($field->name() == 'name'): ?>
			<br /><span><?php echo $item->field('username')->value(); ?></span>
		<?php endif; ?>
	</td>
	<?php
		}

		foreach ($actions as $action) {
			$auth = Auth::instance();
			if ($auth->has_permission($action, $model)) {
				if ($action == 'delete' && $auth->get_user()->id == $item->id) {
	?>
	<td></td>
	<?php
				}
				else {
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
		}
	?>
</tr>
