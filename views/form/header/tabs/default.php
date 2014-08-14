<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
if (!isset($header_tabs)) {
	if (!empty($form) && !empty($form->tabs)) {
		$header_tabs = $form->tabs;
	}
	else if (!empty($lists) && !empty($lists->tabs)) {
		$header_tabs = $lists->tabs;
	}
}

if (isset($header_tabs) && count($header_tabs)) {
?>
<header class="box-header frm-header">
	<ul>
<?php
	foreach ($header_tabs as $url => $label) {
		$extra = array();
		$uri = Request::current()->uri();
		if ($uri == $url || $uri == 'profile/profile' && $url == 'profile') {
			$extra['class'] = 'current';
		}
?>
		<li><?php echo HTML::anchor($url, $label, $extra); ?></li>
<?php
	}
?>
	</ul>
</header>
<?php
}
