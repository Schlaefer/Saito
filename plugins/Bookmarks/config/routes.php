<?php

	use Cake\Routing\Router;

	Router::plugin(
		'Bookmarks',
		function ($routes) {
			$routes->connect('/', ['controller' => 'Bookmarks', 'action' => 'index']);
			$routes->connect('/:action/*', ['controller' => 'Bookmarks']);
		}
	);
