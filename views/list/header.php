<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<header class="box-header grid-header">
	<h2><?php echo Utility::html($title); ?></h2>
	<?php echo $actions; ?>
</header>
<div class="grid-filter">
	<?php echo $filter; ?>
</div>
