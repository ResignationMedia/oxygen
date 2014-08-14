<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

echo View::factory('audit/header', array(
	'model' => $model,
	'breadcrumbs' => $breadcrumbs,
	'favorites' => $favorites,
));
?>
<table class="oxygen-grid audit" style="margin-bottom:15px">
	<thead>
		<tr>
			<th class="fieldname"></th>
			<th><?php echo Date::local($item->created); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="txt-right label">Summary</td>
			<td class="summary"><?php echo $item->activity; ?></td>
		</tr>
		<?php foreach ($model->fieldgroup('edit') as $key => $field): ?>
		<tr>
			<td class="txt-right label"><?php echo ucwords(Inflector::humanize($key)); ?></td>
			<td><?php echo $model->field($key)->value(); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php echo $history; ?>
