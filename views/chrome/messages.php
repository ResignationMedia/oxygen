<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

// Does not strictly follow MVC, but allows for better reusability and maitenence

// If no messages passed in, grab them all

if (empty($messages)) {
	$messages = Msg::get();
	$messages = Msg::sort($messages);
	Msg::clear();
}

if (is_array($messages) && !empty($messages)) {
?>
<ul class="msgs">
<?php
	foreach ($messages as $message) {
		$message->show();
	}
?>
</ul>
<?php 
}
