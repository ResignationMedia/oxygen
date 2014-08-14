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

if (!$errors) {
?>
<table class="oxygen-grid audit diff mar-bottom-single">
	<thead>
		<tr>
			<th class="fieldname"></th>
			<th class="txt-center"><?php echo Date::local($a->created); ?></th>
			<th class="txt-center"><?php echo Date::local($b->created); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$model_a = $a->get_model($model->pk());
	$model_b = $b->get_model($model->pk());

	$b_data = $b->data;
	foreach ($a->data as $key => $value) {
		$no_change = false;
		if (is_array($value)) {
			if (serialize($value) == serialize($b_data[$key])) {
				$no_change = true;
			}
		}
		else if ($value == $b->data[$key]) {
			$no_change = true;
		}

		$field_key = str_replace('_id', '', $key);
		if (($field = $model_a->field($field_key)) !== false) {
			if ($field->type() == 'flag') {
				$value = Text::bool_display($value);
				$b_data[$key] = Text::bool_display($b_data[$key]);
			}
			else {
				$value = $field->value();
			}
		}

		switch ($key) {
			case 'user_id':
			case 'created_by':
			case 'updated_by':
// TODO - use name service
				$value = OModel::factory('user', $value)->name();
				$b_data[$key] = OModel::factory('user', $value)->name();
			break;
			case 'created':
			case 'updated':
				$format = Oxygen::config('oxygen')->get('date_format').' '.Oxygen::config('oxygen')->get('time_format');
				$value = ($value ? Date::local($value, $format) : __('Never'));
				$b_data[$key] = ($b_data[$key] ? Date::local($b_data[$key], $format) : __('Never'));
			case 'last_login':
				if ($value == '0') {
					$value = __('Never');
				}
				if ($b_data[$key] == '0') {
					$value = __('Never');
				}
		}
?>
		<tr>
			<td class="txt-right label"><?php echo ($key == 'id' ? 'ID' : ucwords(Inflector::humanize($key))); ?></td>
<?php
		if ($no_change) {
?>
			<td colspan="2" class="txt-center same">
				<div><?php echo OAudit::complex_display($value, $model_a->ignore_complex_display($key)); ?>&nbsp;</div>
			</td>
<?php
		}
		else {
?>
			<td class="txt-center diff"><?php echo OAudit::complex_display($value, $model_a->ignore_complex_display($key)); ?></td>
			<td class="txt-center diff"><?php echo OAudit::complex_display($b_data[$key], $model_a->ignore_complex_display($key)); ?></td>
<?php
		}
?>
		</tr>
<?php
	}
?>
	</tbody>
</table>
<?php
}

echo $history;
