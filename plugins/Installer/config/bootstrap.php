<?php

use Cake\Core\Configure;
use Cake\Event\EventManager;
use Installer\Middleware\InstallerMiddleware;

if (Configure::read('Saito.installed')) {
    return;
}

EventManager::instance()->on(
    'Server.buildMiddleware',
    function ($event, $middlewareQueue) {
        $middlewareQueue->add(new InstallerMiddleware());
    }
);
