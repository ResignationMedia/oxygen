<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<tr class="itm<?php echo ($is_last_item ? ' last' : ($is_first_item ? ' first' : '')); ?>">
	<td><a href="<?php echo URL::site($item['actor']['view_url']); ?>"><img src="<?php echo $item['actor']['avatar_url']; ?>"  class="photo usr-photo" width="20" height="20" /></a></td>
	<td width="100%"><?php echo View::factory($item['view'], $item); ?></td>
	<td nowrap="nowrap"><?php echo Inflector::humanize($item['type'], true); ?></td>
	<td nowrap="nowrap"><?php echo Date::fuzzy_span($item['created']); ?></td>
</tr>
