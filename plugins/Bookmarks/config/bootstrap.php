<?php

	use Saito\Event\SaitoEventManager;
	use Bookmarks\Lib\MenuRenderer;

	SaitoEventManager::getInstance()->attach(new MenuRenderer());