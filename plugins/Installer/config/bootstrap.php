<?php

use Cake\Event\EventManager;
use Installer\Middleware\InstallerMiddleware;

if (file_exists(CONFIG . '/installer') && php_sapi_name() !== 'cli') {
    EventManager::instance()->on(
        'Server.buildMiddleware',
        function ($event, $middlewareQueue) {
            $middlewareQueue->add(new InstallerMiddleware());
        }
    );
}
