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
 * Routes for xml files
 */
Router::scope(
    '/sitemap',
    ['plugin' => 'Sitemap'],
    function ($routes) {
        $routes->setExtensions(['xml']);
        $routes->connect('/', ['controller' => 'Sitemaps']);
        $routes->connect('/:action/*', ['controller' => 'Sitemaps']);
    }
);

/**
 * Routes for admin interface
 */
Router::prefix(
    'admin',
    function ($routes) {
        $routes->connect(
            '/plugins/sitemap',
            ['plugin' => 'Sitemap', 'controller' => 'Sitemaps', 'action' => 'index']
        );
    }
);
