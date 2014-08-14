<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<header class="box-header frm-header">
<?php
if (!empty($title)) {
?>
	<h2><?php echo $title; ?></h2>
<?php
}
if (count($actions)) {
?>
	<ul class="box-actions">
<?php
foreach ($actions as $action) {
	echo Form::action($action, $model);
}
?>
	</ul>
<?php 
}
?>
</header>
