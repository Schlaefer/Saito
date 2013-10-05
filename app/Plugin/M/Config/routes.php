<?php
	Router::connect(
		'/mobile',
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
