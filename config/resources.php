<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 *
 * Format:
 *
 *   'key' => array( // 'key' must be unique
 *       'type' => 'css' // css|js
 *       'resources' => array(
 *           'key' => array( // 'key' must be unique
 *               'path' => '/path/to/file'
 *           ),
 *           'key2' => array( // 'key2' must be unique
 *               'path' => '/path/to/file'
 *           ),
 *       )
 *   )
 */
$http_path = URL::site('core/oxygen/assets/img', null, false);
return array(
	/**
	 * JavaScript Resources
	 */
	'oxygen-js' => array(
		'type' => 'js',
		'resources' => array(
			'jquery' => array(
				'path' => OXYPATH.'assets/js/jquery.min.js'
			),
			'domwindow' => array(
				'path' => OXYPATH.'assets/js/jquery.DOMwindow.js'
			),
			'jquery-ui' => array(
				'path' => OXYPATH.'assets/js/jquery.ui.core.min.js'
			),
			'jquery-ui-widget' => array(
				'path' => OXYPATH.'assets/js/jquery.ui.widget.min.js'
			),
			'jquery-ui-mouse' => array(
				'path' => OXYPATH.'assets/js/jquery.ui.mouse.min.js'
			),
			'jquery-ui-sortable' => array(
				'path' => OXYPATH.'assets/js/jquery.ui.sortable.min.js'
			),
			'jquery-ui-slider' => array(
				'path' => OXYPATH.'assets/js/jquery.ui.slider.min.js'
			),
			'jquery-ui-datepicker' => array(
				'path' => OXYPATH.'assets/js/jquery.ui.datepicker.min.js'
			),
			'jquery-ui-timepicker' => array(
				'path' => OXYPATH.'assets/js/jquery.ui.timepicker.js'
			),
			'jquery-ui-position' => array(
				'path' => OXYPATH.'assets/js/jquery.ui.position.min.js'
			),
			'jquery-effects' => array(
				'path' => OXYPATH.'assets/js/jquery.effect.min.js'
			),
			'jquery-highlight' => array(
				'path' => OXYPATH.'assets/js/jquery.effect-highlight.min.js'
			),
			'jquery-cf-popover' => array(
				'path' => OXYPATH.'assets/js/crowdfavorite/jquery.cf.popover.js'
			),
			'utility' => array(
				'path' => OXYPATH.'assets/js/utility.js'
			),
			'oxygen' => array(
				'path' => OXYPATH.'assets/js/oxygen.js'
			),
			'msgs' => array(
				'path' => OXYPATH.'assets/js/msgs.js'
			),
			'typeahead' => array(
				'path' => OXYPATH.'assets/js/typeahead.js'
			),
			'search' => array(
				'path' => OXYPATH.'assets/js/search.js'
			),
			'audit' => array(
				'path' => OXYPATH.'assets/js/audit.js'
			),
			'favorite' => array(
				'path' => OXYPATH.'assets/js/favorite.js'
			),
			'list' => array(
				'path' => OXYPATH.'assets/js/list.js'
			),
			'user' => array(
				'path' => OXYPATH.'assets/js/user.js'
			),
			'role' => array(
				'path' => OXYPATH.'assets/js/role.js'
			),
			'oxygen-ready' => array(
				'type' => 'source',
				'content' => 'jQuery(function($){ $("body").trigger("oxygenReady"); });'
			),
		),
	),
	/**
	 * CSS Resources
	 */
	'oxygen-css' => array(
		'type' => 'css',
		'resources' => array(
			'base' => array(
				'path' => OXYPATH.'assets/css/base.css'
			),
			'structure' => array(
				'path' => OXYPATH.'assets/css/structure.css'
			),
			'utility' => array(
				'type' => 'source',
				'content' => str_replace('../img', $http_path, file_get_contents(OXYPATH.'assets/css/utility.css')),
				'modified' => filemtime(OXYPATH.'assets/css/utility.css')
			),
			'slider' => array(
				'type' => 'source',
				'content' => str_replace('../img', $http_path, file_get_contents(OXYPATH.'assets/css/jquery.ui.slider.css')),
				'modified' => filemtime(OXYPATH.'assets/css/jquery.ui.slider.css')
			),
			'datepicker' => array(
				'type' => 'source',
				'content' => str_replace('../img', $http_path, file_get_contents(OXYPATH.'assets/css/jquery.ui.datepicker.css')),
				'modified' => filemtime(OXYPATH.'assets/css/jquery.ui.datepicker.css')
			),
			'timepicker' => array(
				'type' => 'source',
				'content' => str_replace('../img', $http_path, file_get_contents(OXYPATH.'assets/css/jquery.ui.timepicker.css')),
				'modified' => filemtime(OXYPATH.'assets/css/jquery.ui.timepicker.css')
			),
			'default-theme' => array(
				'type' => 'source',
				'content' => str_replace('../img', $http_path, file_get_contents(OXYPATH.'assets/css/theme.css')),
				'modified' => filemtime(OXYPATH.'assets/css/theme.css')
			)
		),
	)
);
