<?php

	//= don't activate on CLI-tests
	if (php_sapi_name() === 'cli') {
		return;
	}

	App::uses('NsfwBadgeRenderer', 'NsfwBadge.Lib');
	SaitoEventManager::getInstance()->attach(new NsfwBadgeRenderer());
