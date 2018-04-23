<?php

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin(
    'SaitoSearch',
    ['path' => '/searches'],
    function (RouteBuilder $routes) {
        $routes->connect('/:action', ['controller' => 'searches']);
    }
);
