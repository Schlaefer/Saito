<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
