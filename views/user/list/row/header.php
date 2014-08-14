<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<thead class="list-sort">
	<tr>
		<th width="<?php echo Oxygen::config('oxygen.user_photo.sizes.thumbnail.width'); ?>"></th>
<?php
$sortable = $model->sortable_columns();
foreach ($columns as $key => $label) {
?>
		<th>
<?php
	if (isset($sortable[$key])) {
		echo $model->sort_link($key, $label);
	}
	else {
		echo $label;
	}
?> 
		</th>
<?php
}

if (is_array($actions) && !empty($actions)) {
	foreach ($actions as $action) {
		if (Auth::instance()->has_permission($action, $model)) {
?>
		<th class="action"></th> 
<?php
		}
	}
}
?>
	</tr>
</thead>
