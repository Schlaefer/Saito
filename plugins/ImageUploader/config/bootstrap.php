<?php

use Cake\Cache\Cache;
use ImageUploader\Lib\MenuRenderer;
use Saito\Event\SaitoEventManager;

Cache::setConfig(
    'uploadsThumbnails',
    [
        'className' => 'File',
        'prefix' => 'saito_thumbnails-',
        'path' => CACHE,
        'groups' => ['uploads'],
        'duration' => '+1 year'
    ]
);

SaitoEventManager::getInstance()->attach(new MenuRenderer());
