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
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'Feeds',
    function ($routes) {
        $routes->setExtensions(['rss']);
        $routes->fallbacks(DashedRoute::class);
    }
);
