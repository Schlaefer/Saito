<?php

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
