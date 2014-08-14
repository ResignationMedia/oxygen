<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<p>Your data was <strong>not</strong> saved. Someone else edited this record while you were making your changes. Please
review the differences below and choose the data you would like to save.</p>

<table class="oxygen-grid collection collision">
	<thead>
		<tr>
			<th class="fieldname"></th>
			<th class="current_value">Current Value</th>
			<th class="your_value">Your Value</th>
			<th class="edit_value">Chosen Value</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ($diff as $key) {
				$prev = $previous->field($key)->label('')->display('view');
				$field = $model->field($key);
				$label = $field->label();
				$current = $field->label('')->display('view');
		?>
		<tr>
			<td class="right"><?php echo $label; ?></td>
			<td><?php echo $prev; ?></td>
			<td><?php echo $current; ?></td>
			<td><?php echo $field->display('edit'); ?></td>
		</tr>
		<?php
			}
		?>
	</tbody>
</table>
<div class="hidden">
	<?php
		foreach ($model->fieldgroup('edit') as $key => $field) {
			if (!in_array($key, $diff)) {
				echo $field;
			}
		}

		if ($nonce !== null) {
			echo Nonce::field($nonce, $model->id);
		}
	?>
</div>
