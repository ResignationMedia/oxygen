<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<footer class="box-footer frm-footer">
<?php
if (count($buttons)) {
	foreach ($buttons as $button) {
		echo $button;
	}
}
?>
</footer>
