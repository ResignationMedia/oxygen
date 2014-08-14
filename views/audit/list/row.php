<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

$actions = array(
	HTML::anchor('history/view/'.$item->id(), __('View'))
);
$actions[] = ($first ? __('vs. Current') : HTML::anchor('history/compare/'.$item->id.'/current', __('vs. Current')));
$actions[] = ($last ? __('vs. Previous') : HTML::anchor('history/compare/'.$item->id.'/previous', __('vs. Previous')));

?>
<tr>
	<td class="elm-radio"><?php echo Form::radio('item_a', $item->id, ($item->id == $a)); ?></td>
	<td class="elm-radio"><?php echo Form::radio('item_b', $item->id, ($item->id == $b)); ?></td>
	<td class="description"><?php echo $item->description; ?></td>
	<td><?php echo Date::local($item->created); ?></td>
	<td class="actions txt-right"><?php echo implode(' | ', $actions); ?></td>
</tr>
