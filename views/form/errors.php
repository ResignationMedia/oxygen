<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
if (is_array($errors) && count($errors)):
?>
<ul id="msgs">
	<?php foreach($errors as $error): ?>
	<li class="error"><?php echo $error; ?></li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
