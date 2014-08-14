<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
// Header
if (!Request::current()->is_ajax()) {
	echo View::factory('content/header', array(
		'breadcrumbs' => $breadcrumbs,
		'favorites' => $favorites,
	));
	echo $form->header();
}
?>
<div id="glb-filter-form">
	<div id="glb-filter-left">
<?php
echo $form->field('name')->display('edit')->label('Name:');
echo $form->field('username')->display('edit')->label('Username:');
echo $form->field('email')->display('edit')->label('Email:');
echo $form->field('company')->display('edit')->label('Company:');
echo $form->field('website')->display('edit')->label('Web Site:');
?>
	</div>
	<div id="glb-filter-right">
		<div class="glb-filter-row no-border">
			<div style="float:left;height:30px;">
				<label>Enabled:</label><br />
				<span class="select-all">
					<a href="#" rel="obsolete-no,obsolete-ues" class="all">All</a> - <a href="#" rel="obsolete-no,obsolete-yes" class="none">None</a>
				</span>
			</div>

			<input type="checkbox" name="enabled" id="enabled-yes" value="yes" /> <label for="enabled-yes" style="display:inline;font-weight:normal;float:none;">Yes</label><br />
			<input type="checkbox" name="enabled" id="enabled-no" value="no" /> <label for="enabled-no" style="display:inline;font-weight:normal;float:none;">No</label>
		</div>
	</div>
	<div class="clearfix"></div>
</div>
<?php
if (!Request::current()->is_ajax()) {
	echo $form->footer();
}
