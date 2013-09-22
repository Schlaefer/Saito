<?php
	Router::connect(
		'/m/ms/',
		[
			'plugin'     => 'M',
			'controller' => 'ms',
			'action'     => 'index'
		]
	);

	Router::connect(
		'/m/ms/cache.manifest',
		[
			'plugin'     => 'M',
			'controller' => 'ms',
			'action'     => 'manifest'
		]
	);
