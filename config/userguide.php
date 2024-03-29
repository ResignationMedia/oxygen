<?php defined('SYSPATH') || die('No direct script access.');

return array(
	// Leave this alone
	'modules' => array(

		// This should be the path to this modules userguide pages, without the 'guide/'. Ex: '/guide/modulename/' would be 'modulename'
		'pagination' => array(

			// Whether this modules userguide pages should be shown
			'enabled' => true,
			
			// The name that should show up on the userguide index page
			'name' => 'Pagination',

			// A short description of this module, shown on the index page
			'description' => 'Tool for creating paginated links and viewing pages of results.',
			
			// Copyright message, shown in the footer for this module
			'copyright' => '&copy; 2008–2010 Kohana Team',
		)	
	)
);
