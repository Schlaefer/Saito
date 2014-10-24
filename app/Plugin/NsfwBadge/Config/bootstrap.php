<?php

	App::uses('NsfwBadgeRenderer', 'NsfwBadge.Lib');
	SaitoEventManager::getInstance()->attach(new NsfwBadgeRenderer());
