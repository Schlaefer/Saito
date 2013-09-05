<?php

	// threads collection
	// -------------------------------------

	// Read
	Router::connect(
		'/api/v1/threads',
		[
			'plugin'     => 'Api',
			'controller' => 'ApiEntries',
			'action'     => 'threadsGet',
			'[method]'   => 'GET'
		]
	);

	// Read entries of thread
	Router::connect(
		'/api/v1/threads/*',
		[
			'plugin'     => 'Api',
			'controller' => 'ApiEntries',
			'action'     => 'threadsItemGet',
			'[method]' 	=> 	'GET'
		]
	);

	// entries
	// -------------------------------------

	// Create
	Router::connect(
		'/api/v1/entries',
		['plugin' => 'Api', 'controller' => 'ApiEntries', 'action' => 'entriesItemPost', '[method]' => 'POST']
	);

	// Update
	Router::connect(
		'/api/v1/entries/*',
		['plugin' => 'Api', 'controller' => 'ApiEntries', 'action' => 'entriesItemPut', '[method]' => 'PUT']
	);

	// User
	// -------------------------------------

	// Login
	Router::connect(
		'/api/v1/login',
		[
			'plugin'     => 'Api',
			'controller' => 'ApiUsers',
			'action'		 => 'login',
			'[method]' => 'POST'
		]
	);

	// Logout
	Router::connect(
		'/api/v1/logout',
		[
			'plugin'     => 'Api',
			'controller' => 'ApiUsers',
			'action'		 => 'logout',
			'[method]' => 'POST'
		]
	);

	// Misc
	// -------------------------------------

	// Bootstrap - Read
	Router::connect(
		'/api/v1/bootstrap',
		[
			'plugin'     => 'Api',
			'controller' => 'ApiCore',
			'action'     => 'bootstrap',
			'[method]'   => 'GET'
		]
	);

	// Mark as Read - Update
	Router::connect(
		'/api/v1/markasread',
		[
			'plugin'     => 'Api',
			'controller' => 'ApiUsers',
			'action'     => 'markasread',
			'[method]'   => 'POST'
		]
	);

	// catchall for unknown route
	Router::connect(
		'/api/v1/*',
		['plugin' => 'Api', 'controller' => 'ApiCore', 'action' => 'unknownRoute']
	);
