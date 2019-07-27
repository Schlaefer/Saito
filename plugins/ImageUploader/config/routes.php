<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin(
    'ImageUploader',
    ['path' => '/api/v2'],
    function (RouteBuilder $routes) {
        $routes->get(
            '/uploads/thumb/:id',
            ['controller' => 'Thumbnail', 'action' => 'thumb'],
            'imageUploader-thumbnail'
        )
            ->setPatterns(['id' => '[0-9]+']);

        $routes->setExtensions(['json']);
        $routes->resources('Uploads');
    }
);
