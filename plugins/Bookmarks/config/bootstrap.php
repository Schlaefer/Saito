<?php

use Bookmarks\Lib\MenuRenderer;
use Saito\Event\SaitoEventManager;

SaitoEventManager::getInstance()->attach(new MenuRenderer());
