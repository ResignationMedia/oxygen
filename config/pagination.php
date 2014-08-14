<?php defined('SYSPATH') || die('No direct script access.');

return array(

	// Application defaults
	'default' => array(
		'current_page'      => array(
			'source' => 'route', // source: "query_string" or "route"
			'key' => 'page'
		),
		'total_items'       => 0,
		'items_per_page'    => 20,
		'view'              => 'pagination/basic',
		'auto_hide'         => true,
		'first_page_in_url' => false,
		'count_out'         => 2,
		'count_in'          => 5
	),

);
