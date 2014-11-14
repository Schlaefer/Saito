<?php
	Router::connect(
			'/help/:id',
			[
					'plugin' => 'SaitoHelp',
					'controller' => 'SaitoHelps',
					'action' => 'languageRedirect'
			],
			[
					'pass' => ['id']
			]
	);
	Router::connect(
			'/help/:lang/:id',
			[
					'plugin' => 'SaitoHelp',
					'controller' => 'SaitoHelps',
					'action' => 'view'
			],
			[
					'pass' => ['lang', 'id']
			]
	);