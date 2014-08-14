<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta content="en-us" http-equiv="Content-Language" />
	<title><?php echo $title; ?></title>
	<link rel="shortcut icon" href="<?php echo URL::site(OTheme::find_file('img', 'favicon', 'ico')); ?>" />
	<?php echo HTML::style(OResource::url('oxygen-css'), array('media' => 'screen, tv, projection')); ?>
	<!--[if IE 7]>
		<?php echo HTML::style(OTheme::find_file('css', 'ie7', 'css'), array('media' => 'screen, tv, projection')); ?>
	<![endif]-->
</head>
<body id="upgrade">
<?php
echo OForm::factory('upgrade/shell')
	->title($title)
	->content('upgrade_content', $content)
	->button('next', OField::factory('submit')->name('continue')->default_value('Continue'))
	->attributes(array(
		'class' => 'edit',
	))
	->attributes(array(
		'class' => 'box box-upgrade frm',
	), true);
?>
</body>
</html>
