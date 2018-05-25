<?php

use Cake\Routing\Router;

Router::plugin(
    'Bookmarks',
    ['path' => '/api/v2'],
    function ($routes) {
        $routes->setExtensions(['json']);
        $routes->resources('Bookmarks');
    }
);
