<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

if (!empty($actions)) {
?>
<ul class="box-actions">
<?php
foreach ($actions as $action) {
	echo View::factory('list/actions/'.$action, array('model' => $model));
}
?>
</ul>
<?php
}
