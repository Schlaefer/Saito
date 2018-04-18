<?php

use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'Feeds',
    function ($routes) {
        $routes->setExtensions(['rss']);
        $routes->fallbacks(DashedRoute::class);
    }
);
