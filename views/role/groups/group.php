<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<div id="permissions_group_<?php echo $key; ?>" class="permissions-group">
<?php
foreach ($fieldsets as $fieldset) {
	echo $fieldset;
}
?>
</div>
