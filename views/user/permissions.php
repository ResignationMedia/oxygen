<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

$fields = OPermissions::instance()->roles_fields($roles, $model);
$permissions_html = '';
foreach ($fields as $field) {
	$output = OFieldset::factory()
		->model($model)
		->view('shell', 'user/permissions/fieldset')
		->field($field->name(), $field)
		->add_css_class('lbl-pos-left', true);

	$key = explode('_role_id', $field->name());
	$key = $key[0];
	if ($key == 'role_id') {
		$key = 'system';
	}

	$fieldsets = OPermissions::instance()->checkboxes(
		$permissions,
		$key,
		($field->value() == '0' ? true : false),
		true
	);
	if (count($fieldsets['fieldsets'])) {
		$content = '';
		foreach ($fieldsets['fieldsets'] as $fieldset) {
			$content .= $fieldset;
		}
		$output->content($content);
	}

	$permissions_html .= $output;
}

if (isset($header)) {
?>
</div> <!-- /box-content -->
<header class="box-header frm-header box-fuse-top bdr-top">
	<h2><?php echo __('Permissions'); ?></h2>
</header>
<div class="box-content" id="edit_permissions">
<?php
}
else {
?>
<div id="edit_permissions">
<?php
}
echo $permissions_html;
?>
<script type="text/javascript">
<?php
foreach ($roles as $role) {
?>
o.roles.id_<?php echo $role->id; ?> = '<?php echo implode(',', OPermissions::instance()->flatten($role->permissions)); ?>';
<?php
}
?>
</script>
<?php
if (!isset($header)) {
?>
</div> <!-- /box-content -->
<?php 
}
