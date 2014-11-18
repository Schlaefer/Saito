<?php

	use Cake\Routing\Router;

	/**
	 * Routes for xml files
	 */
	Router::scope(
		'/sitemap', ['plugin' => 'Sitemap'],
		function ($routes) {
			$routes->extensions(['xml']);
			$routes->connect('/', ['controller' => 'Sitemaps']);
			$routes->connect('/:action/*', ['controller' => 'Sitemaps']);
		}
	);

	/**
	 * Routes for admin interface
	 */
	Router::prefix('admin', function ($routes) {
		$routes->connect(
			'/plugins/sitemap', ['plugin' => 'Sitemap', 'controller' => 'Sitemaps', 'action' => 'index']
		);
	});
