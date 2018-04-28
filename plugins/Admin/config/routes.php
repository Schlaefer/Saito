<?php

use Cake\Routing\Router;

/**
 * Routes for Admin-Area
 */
Router::scope(
    '/admin',
    ['plugin' => 'Admin'],
    function ($routes) {
        $routes->connect(
            '/',
            ['controller' => 'Admins', 'action' => 'index']
        );
        $routes->connect(
            '/plugins',
            ['controller' => 'Admins', 'action' => 'plugins']
        );
        $routes->fallbacks('InflectedRoute');
    }
);
