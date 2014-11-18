<?php

	use Saito\Event\SaitoEventManager;

	/**
	 * register plugin's admin-UI in admin-backend
	 */
	SaitoEventManager::getInstance()->attach(
		'Request.Saito.View.Admin.plugins',
		function () {
			$url = '/admin/plugins/sitemap';
			$title = 'Sitemap';
			return compact('url', 'title');
		}
	);