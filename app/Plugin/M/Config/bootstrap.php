<?php

	CakeLog::config(
		'mobile-client',
		[
			'engine' => 'FileLog',
			'file' => 'mobile-client.log',
			'size' => 5242880,
			'rotate' => 2,
			'types' => ['error'],
			'scopes' => ['mobile-client']
		]
	);
